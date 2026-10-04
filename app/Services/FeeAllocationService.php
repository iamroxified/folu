<?php

namespace App\Services;

use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\AdditionalCharge;
use App\Models\FinancialAuditLog;
use Illuminate\Support\Facades\DB;

class FeeAllocationService
{
    /**
     * Determine and allocate appropriate fee structure for a student automatically.
     */
    public static function allocateFeeForStudent(Student $student, ?int $sessionId = null, ?int $termId = null): ?StudentFee
    {
        $sessionId = $sessionId ?? $student->current_session_id;
        $termId = $termId ?? $student->current_term_id;

        if (!$sessionId) {
            return null;
        }

        // Standardize category identifier: 'NI' for New Intake, 'OS' for Returning Student
        $category = in_array(strtoupper((string) $student->category), ['NI', 'NEW_INTAKE', 'NEW']) ? 'NI' : 'OS';

        // Gender mapping
        $gender = strtoupper(substr((string) $student->gender, 0, 1));
        if (!in_array($gender, ['M', 'F'])) {
            $gender = 'M'; // default fallback
        }

        // Query FeeStructure
        $query = FeeStructure::where('session_id', $sessionId)
            ->where('is_active', true)
            ->where('category', $category)
            ->where(function($q) use ($student) {
                $q->where('class_id', $student->current_class_id)
                  ->orWhereNull('class_id');
            })
            ->where(function($q) use ($gender) {
                $q->where('gender', $gender)
                  ->orWhere('gender', 'All');
            });

        // CRITICAL BUSINESS RULE: New Intake fees ignore term; Returning student fees filter by term.
        if ($category === 'OS' && $termId) {
            $query->where('term_id', $termId);
        }

        // Prefer specific class matching over null class_id
        $feeStructures = $query->get()->sortByDesc(function($structure) use ($student, $gender) {
            $score = 0;
            if ($structure->class_id === $student->current_class_id) $score += 10;
            if ($structure->gender === $gender) $score += 5;
            return $score;
        });

        $feeStructure = $feeStructures->first();

        if (!$feeStructure) {
            return null;
        }

        // Check if student fee allocation already exists for this session & term
        $existingQuery = StudentFee::where('student_id', $student->id)
            ->where('session_id', $sessionId);

        if ($category === 'OS' && $termId) {
            $existingQuery->where('term_id', $termId);
        }

        $studentFee = $existingQuery->first();

        if (!$studentFee) {
            $baseAmount = $feeStructure->amount;
            $studentFee = StudentFee::create([
                'student_id' => $student->id,
                'fee_structure_id' => $feeStructure->id,
                'session_id' => $sessionId,
                'term_id' => $category === 'OS' ? $termId : null,
                'base_amount' => $baseAmount,
                'additional_amount' => 0,
                'total_payable' => $baseAmount,
                'amount_due' => $baseAmount,
                'amount_paid' => 0,
                'amount_owed' => $baseAmount,
                'balance' => $baseAmount,
                'due_date' => now()->addDays(30),
                'status' => 'unpaid',
                'academic_year' => $feeStructure->session ? $feeStructure->session->session_name : date('Y'),
                'semester' => $feeStructure->term ? $feeStructure->term->term_name : null,
                'notes' => 'Auto-allocated ' . ($category === 'NI' ? 'New Intake' : 'Returning') . ' Fee Structure',
                'itemized_snapshot' => $feeStructure->itemized_components,
            ]);

            FinancialAuditLog::logAction('auto_allocated_fee', $studentFee, [], [
                'student_id' => $student->id,
                'fee_structure_id' => $feeStructure->id,
                'amount' => $baseAmount,
            ]);
        } else {
            // Keep existing historical record intact, but recalculate totals
            $studentFee->recalculateTotals();
        }

        return $studentFee;
    }

    /**
     * Add an additional charge to a student's profile.
     */
    public static function addAdditionalCharge(Student $student, string $feeName, float $amount, ?string $reason = null, ?int $sessionId = null, ?int $termId = null): AdditionalCharge
    {
        $sessionId = $sessionId ?? $student->current_session_id;
        $termId = $termId ?? $student->current_term_id;

        // Find or create current StudentFee allocation to attach charge
        $studentFee = self::allocateFeeForStudent($student, $sessionId, $termId);

        $charge = AdditionalCharge::create([
            'student_id' => $student->id,
            'student_fee_id' => $studentFee ? $studentFee->id : null,
            'session_id' => $sessionId,
            'term_id' => $termId,
            'fee_name' => $feeName,
            'amount' => $amount,
            'reason' => $reason,
            'added_by' => auth()->id() ?? ($_SESSION['adid'] ?? null),
            'status' => 'unpaid',
        ]);

        if ($studentFee) {
            $studentFee->recalculateTotals();
        }

        FinancialAuditLog::logAction('added_additional_charge', $charge, [], [
            'student_id' => $student->id,
            'fee_name' => $feeName,
            'amount' => $amount,
            'reason' => $reason,
        ]);

        return $charge;
    }
}
