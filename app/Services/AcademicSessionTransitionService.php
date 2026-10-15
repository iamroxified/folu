<?php

namespace App\Services;

use App\Models\AcademicSession;
use App\Models\Term;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\FeeStructure;
use App\Models\FinancialAuditLog;
use Illuminate\Support\Facades\DB;
use Throwable;

class AcademicSessionTransitionService
{
    /**
     * Transition the system to an active session and/or term.
     * Automatically assigns all active students to the new session and term,
     * promotes intake category ('NI' -> 'OS') across new sessions,
     * and automatically allocates all scheduled fee structures for the new term.
     *
     * All historical payments, student fees, results, and attendance records
     * are strictly preserved without truncation or modification.
     *
     * @param int $sessionId Target academic session ID
     * @param int|null $termId Target term ID (1 = First Term, 2 = Second Term, 3 = Third Term, or null for default)
     * @param bool $autoAllocateFees Whether to apply scheduled fee structures (default: true)
     * @return array
     */
    public static function transitionToSessionAndTerm(int $sessionId, ?int $termId = null, bool $autoAllocateFees = true): array
    {
        $session = AcademicSession::find($sessionId);
        if (!$session) {
            throw new \InvalidArgumentException("Academic session with ID {$sessionId} does not exist.");
        }

        // Determine target term (defaults to active term or Term 1)
        $targetTermId = self::resolveTargetTermId($sessionId, $termId);
        $term = Term::find($targetTermId);
        $termName = $term ? $term->term_name : "Term {$targetTermId}";

        // 1. Update active flags on academic_sessions
        AcademicSession::where('is_active', true)->where('id', '!=', $sessionId)->update(['is_active' => false]);
        AcademicSession::where('id', $sessionId)->update(['is_active' => true]);

        // 2. Update active flags on terms table
        Term::where('is_active', true)->where('id', '!=', $targetTermId)->update(['is_active' => false]);
        Term::where('id', $targetTermId)->update(['is_active' => true]);

        // 3. Synchronize academic_terms table if present
        if (DB::getSchemaBuilder()->hasTable('academic_terms')) {
            DB::table('academic_terms')->where('is_active', 1)->update(['is_active' => 0]);

            $existingAcTerm = DB::table('academic_terms')
                ->where('academic_session_link', $sessionId)
                ->where('term_code', (string) $targetTermId)
                ->first();

            if ($existingAcTerm) {
                DB::table('academic_terms')
                    ->where('id', $existingAcTerm->id)
                    ->update(['is_active' => 1]);
            } else {
                DB::table('academic_terms')->insert([
                    'academic_session_link' => $sessionId,
                    'term_code' => (string) $targetTermId,
                    'term_name' => $termName,
                    'start_date' => $session->start_date ?: date('Y-m-d'),
                    'end_date' => $session->end_date ?: date('Y-m-d', strtotime('+3 months')),
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 4. Assign all active admitted students to the new session & term
        $studentsQuery = Student::where(function($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->where(function($q) {
            $q->where('admission_status', 'admitted')->orWhereNull('admission_status');
        });

        $students = $studentsQuery->get();
        $studentsAssigned = 0;
        $categoryPromotedCount = 0;

        foreach ($students as $student) {
            $isNewSession = (int) ($student->current_session_id ?? 0) !== (int) $sessionId;

            // Across new sessions, New Intake (NI) students automatically become Returning (OS) students
            if ($isNewSession && $student->category === 'NI') {
                $student->category = 'OS';
                $categoryPromotedCount++;
            }

            $student->current_session_id = $sessionId;
            $student->current_term_id = $targetTermId;
            $student->save();
            $studentsAssigned++;
        }

        // 5. Automatically allocate all active matching fees for this session & term
        $totalFeesAllocated = 0;
        $studentsWithFees = 0;

        if ($autoAllocateFees) {
            foreach ($students as $student) {
                try {
                    $allocated = FeeAllocationService::allocateAllMatchingFeesForStudent($student, $sessionId, $targetTermId);
                    if (!empty($allocated)) {
                        $totalFeesAllocated += count($allocated);
                        $studentsWithFees++;
                    }
                } catch (Throwable $e) {
                    // Fail-safe per student to not disrupt overall continuity
                    report($e);
                }
            }
        }

        // 6. Record financial audit log
        try {
            FinancialAuditLog::logAction('academic_session_transition', null, [], [
                'session_id' => $sessionId,
                'session_name' => $session->session_name,
                'term_id' => $targetTermId,
                'term_name' => $termName,
                'students_assigned' => $studentsAssigned,
                'category_promoted' => $categoryPromotedCount,
                'total_fees_allocated' => $totalFeesAllocated,
            ]);
        } catch (Throwable $t) {
            // Non-critical audit logging fail-safe
        }

        return [
            'success' => true,
            'session_id' => $sessionId,
            'session_name' => $session->session_name,
            'term_id' => $targetTermId,
            'term_name' => $termName,
            'students_assigned' => $studentsAssigned,
            'category_promoted' => $categoryPromotedCount,
            'fees_allocated' => $totalFeesAllocated,
            'students_with_fees' => $studentsWithFees,
            'message' => "Successfully set {$session->session_name} ({$termName}) as active. Assigned {$studentsAssigned} student(s) and applied {$totalFeesAllocated} scheduled fee structure allocation(s).",
        ];
    }

    /**
     * Resolve the target term ID from either a term number, terms.id, or academic_terms.id.
     */
    public static function resolveTargetTermId(int $sessionId, ?int $termId = null): int
    {
        if ($termId !== null && $termId > 0) {
            // Check if it directly matches a term in terms table
            $directTerm = Term::find($termId);
            if ($directTerm) {
                return (int) $directTerm->id;
            }

            // Check if it's an ID from academic_terms
            if (DB::getSchemaBuilder()->hasTable('academic_terms')) {
                $acTerm = DB::table('academic_terms')->where('id', $termId)->first();
                if ($acTerm && !empty($acTerm->term_code)) {
                    $code = (int) $acTerm->term_code;
                    $matched = Term::where('term_number', $code)->orWhere('id', $code)->first();
                    if ($matched) {
                        return (int) $matched->id;
                    }
                }
            }
        }

        // Check active term in terms table
        $activeTerm = Term::where('is_active', true)->first();
        if ($activeTerm) {
            return (int) $activeTerm->id;
        }

        // Check active term in academic_terms for this session
        if (DB::getSchemaBuilder()->hasTable('academic_terms')) {
            $activeAcTerm = DB::table('academic_terms')
                ->where('academic_session_link', $sessionId)
                ->where('is_active', 1)
                ->first();
            if ($activeAcTerm && !empty($activeAcTerm->term_code)) {
                $code = (int) $activeAcTerm->term_code;
                $matched = Term::where('term_number', $code)->orWhere('id', $code)->first();
                if ($matched) {
                    return (int) $matched->id;
                }
            }
        }

        // Default to Term 1
        $firstTerm = Term::orderBy('term_number', 'asc')->first();
        return $firstTerm ? (int) $firstTerm->id : 1;
    }

    /**
     * Assign / re-sync all matching fees to active students for a given session & term.
     */
    public static function assignFeesToAllStudents(?int $sessionId = null, ?int $termId = null): array
    {
        $activeSession = AcademicSession::where('is_active', true)->first() ?: AcademicSession::first();
        $targetSessionId = $sessionId ?: ($activeSession ? (int) $activeSession->id : 1);

        $activeTerm = Term::where('is_active', true)->first() ?: Term::first();
        $targetTermId = $termId ?: ($activeTerm ? (int) $activeTerm->id : 1);

        $students = Student::where(function($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })->where(function($q) {
            $q->where('admission_status', 'admitted')->orWhereNull('admission_status');
        })->get();

        $allocatedCount = 0;
        $skippedCount = 0;
        $processedStudents = 0;

        foreach ($students as $student) {
            $allocated = FeeAllocationService::allocateAllMatchingFeesForStudent($student, $targetSessionId, $targetTermId);
            $allocatedCount += count($allocated);
            $processedStudents++;
        }

        return [
            'students_processed' => $processedStudents,
            'fees_allocated' => $allocatedCount,
            'session_id' => $targetSessionId,
            'term_id' => $targetTermId,
        ];
    }
}
