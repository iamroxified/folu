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
        $activeSession = \App\Models\AcademicSession::where('is_active', true)->first();
        $activeSessionId = $activeSession ? (int) $activeSession->id : 2;

        $sessionId = $sessionId ?? ($student->current_session_id ?: $activeSessionId);
        $termId = $termId ?? ($student->current_term_id ?: 1);

        if (!$sessionId) {
            $sessionId = $activeSessionId;
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

        // 1. If not found, try matching by class in this session regardless of gender or category
        if (!$feeStructure && $student->current_class_id) {
            $feeStructure = FeeStructure::where('session_id', $sessionId)
                ->where('is_active', true)
                ->where('class_id', $student->current_class_id)
                ->first();
        }

        // 2. If not found, try general fee structure in this session (class_id IS NULL)
        if (!$feeStructure) {
            $feeStructure = FeeStructure::where('session_id', $sessionId)
                ->where('is_active', true)
                ->whereNull('class_id')
                ->first();
        }

        // 3. If no matching fee structure found in this session, try falling back to active session
        if (!$feeStructure && $sessionId !== $activeSessionId) {
            return self::allocateFeeForStudent($student, $activeSessionId, $termId);
        }

        // 4. If still not found, try general fee structure across active sessions
        if (!$feeStructure) {
            $feeStructure = FeeStructure::where('is_active', true)
                ->whereNull('class_id')
                ->first();
        }

        // 5. Ultimate fallback to any active fee structure in system
        if (!$feeStructure) {
            $feeStructure = FeeStructure::where('is_active', true)->first();
        }

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
            $baseAmount = (float) $feeStructure->amount;
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
            if ((float) $studentFee->base_amount <= 0.001) {
                $studentFee->base_amount = (float) $feeStructure->amount;
            }
            $studentFee->recalculateTotals();
        }

        return $studentFee;
    }

    /**
     * Add an additional charge to a student's profile.
     */
    public static function addAdditionalCharge(Student $student, string $feeName, float $amount, ?string $reason = null, ?int $sessionId = null, ?int $termId = null): AdditionalCharge
    {
        $activeSession = \App\Models\AcademicSession::where('is_active', true)->first();
        $activeSessionId = $activeSession ? (int) $activeSession->id : 2;

        $sessionId = $sessionId ?? ($student->current_session_id ?: $activeSessionId);
        $termId = $termId ?? ($student->current_term_id ?: 1);

        // 1. Find or auto-allocate StudentFee for this student
        $studentFee = self::allocateFeeForStudent($student, $sessionId, $termId);

        // 2. If null and student's session was different from the active session, try active session
        if (!$studentFee && $sessionId !== $activeSessionId) {
            $studentFee = self::allocateFeeForStudent($student, $activeSessionId, $termId);
            if ($studentFee) {
                $sessionId = $activeSessionId;
            }
        }

        // 3. If still null, check if any StudentFee already exists for this student
        if (!$studentFee) {
            $studentFee = StudentFee::where('student_id', $student->id)->latest('id')->first();
        }

        // 4. If student STILL has no StudentFee at all, create an explicit StudentFee container
        if (!$studentFee) {
            $templateFeeStructure = FeeStructure::where('is_active', true)->first() ?: FeeStructure::first();
            $feeStructureId = $templateFeeStructure ? $templateFeeStructure->id : 2;
            $academicYear = $activeSession ? $activeSession->session_name : date('Y');

            $studentFee = StudentFee::create([
                'student_id' => $student->id,
                'fee_structure_id' => $feeStructureId,
                'session_id' => $sessionId ?: $activeSessionId,
                'term_id' => $termId,
                'base_amount' => 0.00,
                'additional_amount' => 0.00,
                'total_payable' => 0.00,
                'amount_due' => 0.00,
                'amount_paid' => 0.00,
                'amount_owed' => 0.00,
                'balance' => 0.00,
                'due_date' => now()->addDays(30),
                'status' => 'unpaid',
                'academic_year' => $academicYear,
                'notes' => 'Allocated Student Fee Profile with Additional Charges',
            ]);
        }

        $charge = AdditionalCharge::create([
            'student_id' => $student->id,
            'student_fee_id' => $studentFee ? $studentFee->id : null,
            'session_id' => $sessionId,
            'term_id' => $termId,
            'fee_name' => $feeName,
            'amount' => $amount,
            'amount_paid' => 0.00,
            'balance' => $amount,
            'reason' => $reason,
            'added_by' => auth()->id() ?? ($_SESSION['adid'] ?? null),
            'status' => 'unpaid',
        ]);

        if ($studentFee) {
            // Also link any previously unlinked charges for this student
            AdditionalCharge::where('student_id', $student->id)
                ->whereNull('student_fee_id')
                ->update(['student_fee_id' => $studentFee->id]);

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

    /**
     * Remove an additional charge from a student's profile and recalculate totals.
     */
    public static function removeAdditionalCharge(int $chargeId): bool
    {
        $charge = AdditionalCharge::find($chargeId);
        if (!$charge) {
            return false;
        }

        $studentFee = $charge->studentFee;
        $charge->delete();

        if ($studentFee) {
            $studentFee->recalculateTotals();
        }

        return true;
    }

    /**
     * Allocate a specific fee structure to a student explicitly.
     */
    public static function allocateSpecificFeeForStudent(
        Student $student,
        int $feeStructureId,
        ?int $sessionId = null,
        ?int $termId = null,
        bool $skipIfAlreadyAllocated = true
    ): ?StudentFee {
        $feeStructure = FeeStructure::find($feeStructureId);
        if (!$feeStructure) {
            return null;
        }

        $activeSession = \App\Models\AcademicSession::where('is_active', true)->first();
        $activeSessionId = $activeSession ? (int) $activeSession->id : 2;

        $sessionId = $sessionId ?? ($feeStructure->session_id ?: ($student->current_session_id ?: $activeSessionId));
        $termId = $termId ?? ($feeStructure->term_id ?: ($student->current_term_id ?: null));

        // Check if this student already has this fee allocated
        $existing = StudentFee::where('student_id', $student->id)
            ->where('fee_structure_id', $feeStructureId)
            ->where(function ($q) use ($sessionId) {
                if ($sessionId) {
                    $q->where('session_id', $sessionId)->orWhereNull('session_id');
                }
            })
            ->first();

        if ($existing) {
            if ($skipIfAlreadyAllocated) {
                return $existing;
            }
        }

        $baseAmount = (float) $feeStructure->amount;
        $sessionName = $feeStructure->session ? $feeStructure->session->session_name : ($activeSession ? $activeSession->session_name : date('Y'));
        $termName = $feeStructure->term ? $feeStructure->term->term_name : null;

        $studentFee = StudentFee::create([
            'student_id' => $student->id,
            'fee_structure_id' => $feeStructure->id,
            'session_id' => $sessionId,
            'term_id' => $termId,
            'base_amount' => $baseAmount,
            'additional_amount' => 0,
            'total_payable' => $baseAmount,
            'amount_due' => $baseAmount,
            'amount_paid' => 0,
            'amount_owed' => $baseAmount,
            'balance' => $baseAmount,
            'due_date' => !empty($feeStructure->effective_to) ? $feeStructure->effective_to : now()->addDays(30),
            'status' => 'unpaid',
            'academic_year' => $sessionName,
            'semester' => $termName,
            'notes' => 'Allocated Fee Structure: ' . $feeStructure->name,
            'itemized_snapshot' => $feeStructure->itemized_components,
        ]);

        FinancialAuditLog::logAction('manual_allocated_fee', $studentFee, [], [
            'student_id' => $student->id,
            'fee_structure_id' => $feeStructure->id,
            'amount' => $baseAmount,
        ]);

        return $studentFee;
    }

    /**
     * Safely deallocate a student fee record if no payments have been recorded.
     */
    public static function deallocateStudentFee(int $studentFeeId): array
    {
        $studentFee = StudentFee::find($studentFeeId);
        if (!$studentFee) {
            return ['success' => false, 'message' => 'Fee allocation record not found.'];
        }

        // Check if payments exist
        $hasPayments = ((float) $studentFee->amount_paid > 0.001);
        if (!$hasPayments) {
            $paymentCount = \App\Models\Payment::where(function ($q) use ($studentFeeId) {
                $q->where('payable_type', 'student_fee')
                  ->orWhere('payable_type', 'App\\Models\\StudentFee');
            })->where('payable_id', $studentFeeId)->where('status', 'completed')->count();
            if ($paymentCount > 0) {
                $hasPayments = true;
            }
        }

        if ($hasPayments) {
            $feeTitle = $studentFee->feeStructure->name ?? 'Fee';
            return [
                'success' => false,
                'message' => "Cannot de-allocate fee '{$feeTitle}'. A payment of ₦" . number_format((float)$studentFee->amount_paid, 2) . " has already been recorded for this student."
            ];
        }

        $oldData = $studentFee->toArray();

        // Unlink any additional charges linked to this student fee
        AdditionalCharge::where('student_fee_id', $studentFeeId)->update(['student_fee_id' => null]);

        $feeName = $studentFee->feeStructure->name ?? 'Fee';
        $studentFee->delete();

        FinancialAuditLog::logAction('deallocated_student_fee', null, $oldData, [
            'student_id' => $oldData['student_id'],
            'fee_structure_id' => $oldData['fee_structure_id'],
        ]);

        return [
            'success' => true,
            'message' => "Fee '{$feeName}' successfully de-allocated from student."
        ];
    }

    /**
     * Bulk allocate fees to multiple students.
     */
    public static function bulkAllocateFees(
        array $studentIds,
        ?int $feeStructureId = null,
        ?int $sessionId = null,
        ?int $termId = null,
        bool $skipExisting = true
    ): array {
        $allocated = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($studentIds as $id) {
            $student = Student::find((int) $id);
            if (!$student) {
                $failed++;
                continue;
            }

            try {
                if ($feeStructureId && $feeStructureId > 0) {
                    $result = self::allocateSpecificFeeForStudent($student, $feeStructureId, $sessionId, $termId, $skipExisting);
                    if ($result && $result->wasRecentlyCreated) {
                        $allocated++;
                    } else {
                        $skipped++;
                    }
                } else {
                    $result = self::allocateFeeForStudent($student, $sessionId, $termId);
                    if ($result && $result->wasRecentlyCreated) {
                        $allocated++;
                    } else {
                        $skipped++;
                    }
                }
            } catch (\Throwable $e) {
                $failed++;
            }
        }

        return [
            'allocated' => $allocated,
            'skipped' => $skipped,
            'failed' => $failed,
            'total' => count($studentIds),
        ];
    }

    /**
     * Bulk deallocate fees from multiple students.
     */
    public static function bulkDeallocateFees(
        array $studentIds,
        ?int $feeStructureId = null,
        ?int $sessionId = null,
        ?int $termId = null
    ): array {
        $deallocated = 0;
        $protected = 0;
        $skipped = 0;

        foreach ($studentIds as $id) {
            $query = StudentFee::where('student_id', (int) $id);
            if ($feeStructureId && $feeStructureId > 0) {
                $query->where('fee_structure_id', $feeStructureId);
            }
            if ($sessionId && $sessionId > 0) {
                $query->where('session_id', $sessionId);
            }
            if ($termId && $termId > 0) {
                $query->where('term_id', $termId);
            }

            $fees = $query->get();
            if ($fees->isEmpty()) {
                $skipped++;
                continue;
            }

            foreach ($fees as $fee) {
                $res = self::deallocateStudentFee($fee->id);
                if ($res['success']) {
                    $deallocated++;
                } else {
                    $protected++;
                }
            }
        }

        return [
            'deallocated' => $deallocated,
            'protected' => $protected,
            'skipped' => $skipped,
            'total' => count($studentIds),
        ];
    }
}
