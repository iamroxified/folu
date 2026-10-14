<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Payment;
use App\Models\StudentFee;
use App\Models\FeeStructure;
use App\Models\AdditionalCharge;
use App\Models\AcademicSession;
use App\Models\Term;
use App\Models\User;
use App\Models\SchoolSetting;
use App\Models\FinancialAuditLog;
use App\Services\FeeAllocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class ResetController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Admin');
    }

    /**
     * Display the Reset Center Hub.
     */
    public function index(Request $request)
    {
        $schoolSettings = SchoolSetting::first();

        // Dropdown collections
        $sessions = AcademicSession::orderBy('is_active', 'desc')->orderBy('id', 'desc')->get();
        $terms = Term::orderBy('id', 'asc')->get();
        $classes = SchoolClass::orderBy('grade_level')->orderBy('class_name')->orderBy('section')->get();
        $feeStructures = FeeStructure::where('is_active', true)->orderBy('name')->get();

        // Active Session & Term defaults
        $activeSession = AcademicSession::where('is_active', true)->first() ?: AcademicSession::first();
        $activeTerm = Term::where('is_active', true)->first() ?: Term::first();

        // Default session & term filter to 0 (All Sessions & All Terms) so no database records are hidden by default
        $sessionId = (int) $request->get('session_id', 0);
        $termId = (int) $request->get('term_id', 0);
        $classId = (int) $request->get('class_id', 0);
        $feeStructureId = (int) $request->get('fee_structure_id', 0);

        $activeTab = $request->get('tab', 'payments');
        if (!in_array($activeTab, ['payments', 'allocations', 'students', 'classes', 'subjects', 'bulk_allocate'], true)) {
            $activeTab = 'payments';
        }

        $search = trim((string) $request->get('search', ''));
        $status = trim((string) $request->get('status', ''));
        $category = trim((string) $request->get('category', ''));
        $gender = trim((string) $request->get('gender', ''));
        $gradeLevel = trim((string) $request->get('grade_level', ''));
        $classLevel = trim((string) $request->get('class_level', ''));
        $department = trim((string) $request->get('department', ''));

        // System overview statistics
        $overviewStats = [
            'total_payments' => Payment::count(),
            'total_payments_amount' => (float) Payment::where('status', 'completed')->sum('amount'),
            'total_allocations' => StudentFee::count(),
            'total_students' => Student::count(),
            'total_classes' => SchoolClass::count(),
            'total_subjects' => Subject::count(),
        ];

        $tabData = [];

        // Tab: Payments
        if ($activeTab === 'payments') {
            $pQuery = Payment::query();

            if ($sessionId > 0) {
                $pQuery->where(function ($q) use ($sessionId) {
                    $q->whereExists(function ($sub) use ($sessionId) {
                        $sub->select(DB::raw(1))
                            ->from('student_fees')
                            ->whereColumn('student_fees.id', 'payments.payable_id')
                            ->whereIn('payments.payable_type', ['student_fee', 'App\\Models\\StudentFee'])
                            ->where('student_fees.session_id', $sessionId);
                    })->orWhereExists(function ($sub) use ($sessionId) {
                        $sub->select(DB::raw(1))
                            ->from('additional_charges')
                            ->whereColumn('additional_charges.id', 'payments.payable_id')
                            ->whereIn('payments.payable_type', ['additional_charge', 'App\\Models\\AdditionalCharge'])
                            ->where('additional_charges.session_id', $sessionId);
                    });
                });
            }

            if ($termId > 0) {
                $pQuery->where(function ($q) use ($termId) {
                    $q->whereExists(function ($sub) use ($termId) {
                        $sub->select(DB::raw(1))
                            ->from('student_fees')
                            ->whereColumn('student_fees.id', 'payments.payable_id')
                            ->whereIn('payments.payable_type', ['student_fee', 'App\\Models\\StudentFee'])
                            ->where('student_fees.term_id', $termId);
                    })->orWhereExists(function ($sub) use ($termId) {
                        $sub->select(DB::raw(1))
                            ->from('additional_charges')
                            ->whereColumn('additional_charges.id', 'payments.payable_id')
                            ->whereIn('payments.payable_type', ['additional_charge', 'App\\Models\\AdditionalCharge'])
                            ->where('additional_charges.term_id', $termId);
                    });
                });
            }

            if ($classId > 0) {
                $pQuery->where(function ($q) use ($classId) {
                    $q->whereExists(function ($sub) use ($classId) {
                        $sub->select(DB::raw(1))
                            ->from('student_fees')
                            ->join('students', 'student_fees.student_id', '=', 'students.id')
                            ->whereColumn('student_fees.id', 'payments.payable_id')
                            ->whereIn('payments.payable_type', ['student_fee', 'App\\Models\\StudentFee'])
                            ->where(function ($sq) use ($classId) {
                                $sq->where('students.current_class_id', $classId)
                                   ->orWhere('students.class_link', $classId);
                            });
                    })->orWhereExists(function ($sub) use ($classId) {
                        $sub->select(DB::raw(1))
                            ->from('additional_charges')
                            ->join('students', 'additional_charges.student_id', '=', 'students.id')
                            ->whereColumn('additional_charges.id', 'payments.payable_id')
                            ->whereIn('payments.payable_type', ['additional_charge', 'App\\Models\\AdditionalCharge'])
                            ->where(function ($sq) use ($classId) {
                                $sq->where('students.current_class_id', $classId)
                                   ->orWhere('students.class_link', $classId);
                            });
                    });
                });
            }

            if ($search !== '') {
                $pQuery->where(function ($q) use ($search) {
                    $q->where('payment_reference', 'like', "%{$search}%")
                      ->orWhere('payer_name', 'like', "%{$search}%")
                      ->orWhere('transaction_id', 'like', "%{$search}%");
                });
            }

            $tabData['count'] = (clone $pQuery)->count();
            $tabData['sum'] = (float) (clone $pQuery)->sum('amount');
            $tabData['items'] = $pQuery->with('payable')->orderBy('payment_date', 'desc')->orderBy('id', 'desc')->get();

            // Preload student context for each payment
            $feeIds = [];
            $chargeIds = [];
            foreach ($tabData['items'] as $item) {
                if (in_array($item->payable_type, ['student_fee', 'App\\Models\\StudentFee'], true)) {
                    $feeIds[] = $item->payable_id;
                } elseif (in_array($item->payable_type, ['additional_charge', 'App\\Models\\AdditionalCharge'], true)) {
                    $chargeIds[] = $item->payable_id;
                }
            }

            $tabData['feeContexts'] = !empty($feeIds)
                ? StudentFee::with(['student.currentClass', 'feeStructure', 'session', 'term'])->whereIn('id', array_unique($feeIds))->get()->keyBy('id')
                : collect();

            $tabData['chargeContexts'] = !empty($chargeIds)
                ? AdditionalCharge::with(['student.currentClass', 'studentFee.feeStructure'])->whereIn('id', array_unique($chargeIds))->get()->keyBy('id')
                : collect();
        }

        // Tab: Allocations
        if ($activeTab === 'allocations') {
            $allocQuery = StudentFee::with(['student.currentClass', 'feeStructure', 'session', 'term']);

            if ($sessionId > 0) {
                $allocQuery->where('session_id', $sessionId);
            }
            if ($termId > 0) {
                $allocQuery->where('term_id', $termId);
            }
            if ($feeStructureId > 0) {
                $allocQuery->where('fee_structure_id', $feeStructureId);
            }
            if ($classId > 0) {
                $allocQuery->whereHas('student', function ($q) use ($classId) {
                    $q->where('current_class_id', $classId)->orWhere('class_link', $classId);
                });
            }
            if ($status !== '') {
                $allocQuery->where('status', $status);
            }
            if ($search !== '') {
                $allocQuery->whereHas('student', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('admission_no', 'like', "%{$search}%");
                });
            }

            $tabData['count'] = (clone $allocQuery)->count();
            $tabData['total_payable'] = (float) (clone $allocQuery)->sum('total_payable');
            $tabData['total_paid'] = (float) (clone $allocQuery)->sum('amount_paid');
            $tabData['total_balance'] = (float) (clone $allocQuery)->sum('balance');
            $tabData['items'] = $allocQuery->orderBy('id', 'desc')->get();
        }

        // Tab: Students
        if ($activeTab === 'students') {
            $sQuery = Student::with(['currentClass', 'currentSession', 'currentTerm']);

            if ($sessionId > 0) {
                $sQuery->where('current_session_id', $sessionId);
            }
            if ($termId > 0) {
                $sQuery->where('current_term_id', $termId);
            }
            if ($classId > 0) {
                $sQuery->where(function ($q) use ($classId) {
                    $q->where('current_class_id', $classId)->orWhere('class_link', $classId);
                });
            }
            if ($category !== '') {
                $sQuery->where('category', $category);
            }
            if ($status !== '') {
                $sQuery->where('status', $status);
            }
            if ($search !== '') {
                $sQuery->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('admission_no', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $tabData['count'] = (clone $sQuery)->count();
            $tabData['active_count'] = (clone $sQuery)->where('status', 'active')->count();
            $tabData['inactive_count'] = (clone $sQuery)->where('status', '!=', 'active')->count();
            $tabData['items'] = $sQuery->orderBy('first_name')->orderBy('last_name')->get();
        }

        // Tab: Classes
        if ($activeTab === 'classes') {
            $cQuery = SchoolClass::query();

            if ($gradeLevel !== '') {
                $cQuery->where('grade_level', $gradeLevel);
            }
            if ($search !== '') {
                $cQuery->where(function ($q) use ($search) {
                    $q->where('class_name', 'like', "%{$search}%")
                      ->orWhere('section', 'like', "%{$search}%");
                });
            }

            $tabData['items'] = $cQuery->withCount('students')->orderBy('grade_level')->orderBy('class_name')->get();
            $tabData['count'] = $tabData['items']->count();
            $tabData['students_count'] = $tabData['items']->sum('students_count');
        }

        // Tab: Subjects
        if ($activeTab === 'subjects') {
            $subQuery = Subject::query();

            if ($classLevel !== '') {
                $subQuery->where('class_level', $classLevel);
            }
            if ($department !== '') {
                $subQuery->where('department', $department);
            }
            if ($search !== '') {
                $subQuery->where(function ($q) use ($search) {
                    $q->where('subject_name', 'like', "%{$search}%")
                      ->orWhere('subject_code', 'like', "%{$search}%");
                });
            }

            $tabData['items'] = $subQuery->orderBy('class_level')->orderBy('subject_name')->get();
            $tabData['count'] = $tabData['items']->count();
        }

        // Tab: Bulk Allocate Preview
        if ($activeTab === 'bulk_allocate') {
            $targetSessionId = $sessionId > 0 ? $sessionId : ($activeSession ? (int) $activeSession->id : 1);
            $targetTermId = $termId > 0 ? $termId : ($activeTerm ? (int) $activeTerm->id : 1);

            $bulkQuery = Student::with(['currentClass', 'studentFees' => function ($q) use ($targetSessionId, $targetTermId) {
                if ($targetSessionId > 0) $q->where('session_id', $targetSessionId);
                if ($targetTermId > 0) $q->where('term_id', $targetTermId);
            }]);

            if ($classId > 0) {
                $bulkQuery->where(function ($q) use ($classId) {
                    $q->where('current_class_id', $classId)->orWhere('class_link', $classId);
                });
            }
            if ($category !== '') {
                $bulkQuery->where('category', $category);
            }
            if ($gender !== '') {
                $bulkQuery->where('gender', $gender);
            }

            $bulkStudents = $bulkQuery->where('status', 'active')->orderBy('first_name')->orderBy('last_name')->get();

            $tabData['items'] = $bulkStudents;
            $tabData['count'] = $bulkStudents->count();
            $tabData['allocated_count'] = $bulkStudents->filter(fn ($s) => $s->studentFees->isNotEmpty())->count();
            $tabData['pending_count'] = $bulkStudents->filter(fn ($s) => $s->studentFees->isEmpty())->count();
            $tabData['target_session_id'] = $targetSessionId;
            $tabData['target_term_id'] = $targetTermId;
        }

        return view('admin.reset.index', compact(
            'schoolSettings',
            'sessions',
            'terms',
            'classes',
            'feeStructures',
            'activeSession',
            'activeTerm',
            'sessionId',
            'termId',
            'classId',
            'feeStructureId',
            'activeTab',
            'search',
            'status',
            'category',
            'gender',
            'gradeLevel',
            'classLevel',
            'department',
            'overviewStats',
            'tabData'
        ));
    }

    /**
     * Reset / Delete Payments and synchronize affected Student Fee balances.
     */
    public function resetPayments(Request $request)
    {
        $scope = $request->input('scope', 'selected');
        $paymentIds = array_filter(array_map('intval', (array) $request->input('payment_ids', [])));
        $sessionId = (int) $request->input('session_id', 0);
        $termId = (int) $request->input('term_id', 0);
        $classId = (int) $request->input('class_id', 0);
        $confirmPhrase = strtoupper(trim((string) $request->input('confirm_phrase', '')));

        if ($confirmPhrase !== 'RESET') {
            return back()->with('error', 'Confirmation failed! Please type "RESET" into the confirmation field.');
        }

        DB::beginTransaction();
        try {
            if ($scope === 'all_filtered') {
                $pQuery = Payment::query();

                if ($sessionId > 0) {
                    $pQuery->where(function ($q) use ($sessionId) {
                        $q->whereExists(function ($sub) use ($sessionId) {
                            $sub->select(DB::raw(1))
                                ->from('student_fees')
                                ->whereColumn('student_fees.id', 'payments.payable_id')
                                ->whereIn('payments.payable_type', ['student_fee', 'App\\Models\\StudentFee'])
                                ->where('student_fees.session_id', $sessionId);
                        })->orWhereExists(function ($sub) use ($sessionId) {
                            $sub->select(DB::raw(1))
                                ->from('additional_charges')
                                ->whereColumn('additional_charges.id', 'payments.payable_id')
                                ->whereIn('payments.payable_type', ['additional_charge', 'App\\Models\\AdditionalCharge'])
                                ->where('additional_charges.session_id', $sessionId);
                        });
                    });
                }

                if ($termId > 0) {
                    $pQuery->where(function ($q) use ($termId) {
                        $q->whereExists(function ($sub) use ($termId) {
                            $sub->select(DB::raw(1))
                                ->from('student_fees')
                                ->whereColumn('student_fees.id', 'payments.payable_id')
                                ->whereIn('payments.payable_type', ['student_fee', 'App\\Models\\StudentFee'])
                                ->where('student_fees.term_id', $termId);
                        })->orWhereExists(function ($sub) use ($termId) {
                            $sub->select(DB::raw(1))
                                ->from('additional_charges')
                                ->whereColumn('additional_charges.id', 'payments.payable_id')
                                ->whereIn('payments.payable_type', ['additional_charge', 'App\\Models\\AdditionalCharge'])
                                ->where('additional_charges.term_id', $termId);
                        });
                    });
                }

                if ($classId > 0) {
                    $pQuery->where(function ($q) use ($classId) {
                        $q->whereExists(function ($sub) use ($classId) {
                            $sub->select(DB::raw(1))
                                ->from('student_fees')
                                ->join('students', 'student_fees.student_id', '=', 'students.id')
                                ->whereColumn('student_fees.id', 'payments.payable_id')
                                ->whereIn('payments.payable_type', ['student_fee', 'App\\Models\\StudentFee'])
                                ->where(function ($sq) use ($classId) {
                                    $sq->where('students.current_class_id', $classId)
                                       ->orWhere('students.class_link', $classId);
                                });
                        })->orWhereExists(function ($sub) use ($classId) {
                            $sub->select(DB::raw(1))
                                ->from('additional_charges')
                                ->join('students', 'additional_charges.student_id', '=', 'students.id')
                                ->whereColumn('additional_charges.id', 'payments.payable_id')
                                ->whereIn('payments.payable_type', ['additional_charge', 'App\\Models\\AdditionalCharge'])
                                ->where(function ($sq) use ($classId) {
                                    $sq->where('students.current_class_id', $classId)
                                       ->orWhere('students.class_link', $classId);
                                });
                        });
                    });
                }

                $paymentIds = $pQuery->pluck('id')->toArray();
            }

            if (empty($paymentIds)) {
                DB::rollBack();
                return back()->with('error', 'No payments were found matching the selected scope.');
            }

            $payments = Payment::whereIn('id', $paymentIds)->get();
            $studentFeeIds = [];
            $additionalChargeIds = [];

            foreach ($payments as $p) {
                if (in_array($p->payable_type, ['student_fee', 'App\\Models\\StudentFee'], true)) {
                    $studentFeeIds[] = $p->payable_id;
                } elseif (in_array($p->payable_type, ['additional_charge', 'App\\Models\\AdditionalCharge'], true)) {
                    $additionalChargeIds[] = $p->payable_id;
                }
            }

            // Clean up receipts if receipts table exists
            if (Schema::hasTable('receipts')) {
                DB::table('receipts')->whereIn('payment_id', $paymentIds)->delete();
            }

            // Delete payment records
            $deletedCount = Payment::whereIn('id', $paymentIds)->delete();

            // Recalculate totals on affected StudentFee records
            $studentFeeIds = array_unique(array_filter($studentFeeIds));
            foreach ($studentFeeIds as $sfId) {
                $sf = StudentFee::find($sfId);
                if ($sf) {
                    $sf->recalculateTotals();
                }
            }

            // Recalculate totals on affected AdditionalCharge records
            $additionalChargeIds = array_unique(array_filter($additionalChargeIds));
            foreach ($additionalChargeIds as $acId) {
                $ac = AdditionalCharge::find($acId);
                if ($ac) {
                    $chargePaid = (float) Payment::where(function ($q) use ($acId) {
                        $q->where('payable_type', 'additional_charge')
                          ->orWhere('payable_type', 'App\\Models\\AdditionalCharge');
                    })->where('payable_id', $acId)->where('status', 'completed')->sum('amount');

                    $ac->amount_paid = $chargePaid;
                    $ac->balance = max(0.00, (float) $ac->amount - $chargePaid);
                    $ac->status = $chargePaid >= (float) $ac->amount ? 'paid' : ($chargePaid > 0 ? 'partially_paid' : 'unpaid');
                    $ac->save();
                }
            }

            FinancialAuditLog::logAction('reset_payments', null, [], [
                'deleted_payments_count' => $deletedCount,
                'affected_fees_count' => count($studentFeeIds),
                'session_id' => $sessionId,
                'term_id' => $termId,
                'class_id' => $classId,
                'scope' => $scope,
            ]);

            DB::commit();

            return redirect()->route('admin.reset.index', [
                'tab' => 'payments',
                'session_id' => $sessionId,
                'term_id' => $termId,
                'class_id' => $classId,
            ])->with('success', "Successfully reset and deleted {$deletedCount} payment record(s). All affected student balances have been automatically restored.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reset payments: ' . $e->getMessage());
        }
    }

    /**
     * Reset / Delete Student Fee Allocations.
     */
    public function resetAllocations(Request $request)
    {
        $scope = $request->input('scope', 'selected');
        $allocationIds = array_filter(array_map('intval', (array) $request->input('allocation_ids', [])));
        $sessionId = (int) $request->input('session_id', 0);
        $termId = (int) $request->input('term_id', 0);
        $classId = (int) $request->input('class_id', 0);
        $feeStructureId = (int) $request->input('fee_structure_id', 0);
        $mode = $request->input('mode', 'deallocate_safe'); // 'deallocate_safe', 'deallocate_force', 'reset_balances'
        $confirmPhrase = strtoupper(trim((string) $request->input('confirm_phrase', '')));

        if ($confirmPhrase !== 'RESET' && $confirmPhrase !== 'DELETE') {
            return back()->with('error', 'Confirmation failed! Please type "RESET" or "DELETE" into the confirmation field.');
        }

        DB::beginTransaction();
        try {
            if ($scope === 'all_filtered') {
                $allocQuery = StudentFee::query();
                if ($sessionId > 0) $allocQuery->where('session_id', $sessionId);
                if ($termId > 0) $allocQuery->where('term_id', $termId);
                if ($feeStructureId > 0) $allocQuery->where('fee_structure_id', $feeStructureId);
                if ($classId > 0) {
                    $allocQuery->whereHas('student', function ($q) use ($classId) {
                        $q->where('current_class_id', $classId)->orWhere('class_link', $classId);
                    });
                }
                $allocationIds = $allocQuery->pluck('id')->toArray();
            }

            if (empty($allocationIds)) {
                DB::rollBack();
                return back()->with('error', 'No allocations were found matching the selected scope.');
            }

            $allocations = StudentFee::whereIn('id', $allocationIds)->get();
            $processedCount = 0;
            $skippedCount = 0;

            foreach ($allocations as $alloc) {
                $hasPayments = ((float) $alloc->amount_paid > 0.001);
                if (!$hasPayments) {
                    $pCount = Payment::where(function ($q) use ($alloc) {
                        $q->where('payable_type', 'student_fee')->orWhere('payable_type', 'App\\Models\\StudentFee');
                    })->where('payable_id', $alloc->id)->count();
                    if ($pCount > 0) $hasPayments = true;
                }

                if ($mode === 'deallocate_safe') {
                    if ($hasPayments) {
                        $skippedCount++;
                        continue;
                    }
                    AdditionalCharge::where('student_fee_id', $alloc->id)->update(['student_fee_id' => null]);
                    $alloc->delete();
                    $processedCount++;
                } elseif ($mode === 'deallocate_force') {
                    // Delete attached payments and receipts
                    $paymentIds = Payment::where(function ($q) use ($alloc) {
                        $q->where('payable_type', 'student_fee')->orWhere('payable_type', 'App\\Models\\StudentFee');
                    })->where('payable_id', $alloc->id)->pluck('id')->toArray();

                    if (!empty($paymentIds)) {
                        if (Schema::hasTable('receipts')) {
                            DB::table('receipts')->whereIn('payment_id', $paymentIds)->delete();
                        }
                        Payment::whereIn('id', $paymentIds)->delete();
                    }

                    AdditionalCharge::where('student_fee_id', $alloc->id)->update(['student_fee_id' => null]);
                    $alloc->delete();
                    $processedCount++;
                } elseif ($mode === 'reset_balances') {
                    $paymentIds = Payment::where(function ($q) use ($alloc) {
                        $q->where('payable_type', 'student_fee')->orWhere('payable_type', 'App\\Models\\StudentFee');
                    })->where('payable_id', $alloc->id)->pluck('id')->toArray();

                    if (!empty($paymentIds)) {
                        if (Schema::hasTable('receipts')) {
                            DB::table('receipts')->whereIn('payment_id', $paymentIds)->delete();
                        }
                        Payment::whereIn('id', $paymentIds)->delete();
                    }

                    $alloc->amount_paid = 0.00;
                    $alloc->amount_due = (float) $alloc->total_payable;
                    $alloc->amount_owed = (float) $alloc->total_payable;
                    $alloc->balance = (float) $alloc->total_payable;
                    $alloc->status = 'unpaid';
                    $alloc->save();
                    $processedCount++;
                }
            }

            FinancialAuditLog::logAction('reset_allocations', null, [], [
                'mode' => $mode,
                'processed' => $processedCount,
                'skipped' => $skippedCount,
                'scope' => $scope,
            ]);

            DB::commit();

            $msg = "Processed {$processedCount} fee allocation(s).";
            if ($skippedCount > 0) {
                $msg .= " ({$skippedCount} allocation(s) were protected from deletion because payments exist. Select 'Force Reset & Delete' to reset their payments first).";
            }

            return redirect()->route('admin.reset.index', [
                'tab' => 'allocations',
                'session_id' => $sessionId,
                'term_id' => $termId,
                'class_id' => $classId,
            ])->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reset allocations: ' . $e->getMessage());
        }
    }

    /**
     * Delete Students with full foreign-key cascade cleanup or soft delete.
     */
    public function deleteStudents(Request $request)
    {
        $scope = $request->input('scope', 'selected');
        $studentIds = array_filter(array_map('intval', (array) $request->input('student_ids', [])));
        $sessionId = (int) $request->input('session_id', 0);
        $termId = (int) $request->input('term_id', 0);
        $classId = (int) $request->input('class_id', 0);
        $mode = $request->input('mode', 'permanent'); // 'permanent' or 'soft'
        $confirmPhrase = strtoupper(trim((string) $request->input('confirm_phrase', '')));

        if ($confirmPhrase !== 'DELETE') {
            return back()->with('error', 'Confirmation failed! Please type "DELETE" into the confirmation field.');
        }

        DB::beginTransaction();
        try {
            if ($scope === 'all_filtered') {
                $sQuery = Student::query();
                if ($sessionId > 0) $sQuery->where('current_session_id', $sessionId);
                if ($termId > 0) $sQuery->where('current_term_id', $termId);
                if ($classId > 0) {
                    $sQuery->where(function ($q) use ($classId) {
                        $q->where('current_class_id', $classId)->orWhere('class_link', $classId);
                    });
                }
                $studentIds = $sQuery->pluck('id')->toArray();
            }

            if (empty($studentIds)) {
                DB::rollBack();
                return back()->with('error', 'No students were found matching the selected scope.');
            }

            $students = Student::whereIn('id', $studentIds)->get();
            $count = 0;

            foreach ($students as $student) {
                if ($mode === 'soft') {
                    $student->status = 'inactive';
                    $student->admission_status = 'withdrawn';
                    $student->save();
                    $count++;
                } else {
                    $sId = $student->id;

                    // 1. Delete receipts and payments
                    $feeIds = StudentFee::where('student_id', $sId)->pluck('id')->toArray();
                    $chargeIds = AdditionalCharge::where('student_id', $sId)->pluck('id')->toArray();

                    $paymentIds = Payment::where(function ($q) use ($sId, $feeIds, $chargeIds) {
                        $q->where(function ($sub) use ($feeIds) {
                            $sub->whereIn('payable_type', ['student_fee', 'App\\Models\\StudentFee'])
                                ->whereIn('payable_id', $feeIds);
                        })->orWhere(function ($sub) use ($chargeIds) {
                            $sub->whereIn('payable_type', ['additional_charge', 'App\\Models\\AdditionalCharge'])
                                ->whereIn('payable_id', $chargeIds);
                        })->orWhere(function ($sub) use ($sId) {
                            $sub->whereIn('payable_type', ['student', 'App\\Models\\Student'])
                                ->where('payable_id', $sId);
                        });
                    })->pluck('id')->toArray();

                    if (!empty($paymentIds)) {
                        if (Schema::hasTable('receipts')) {
                            DB::table('receipts')->whereIn('payment_id', $paymentIds)->delete();
                        }
                        Payment::whereIn('id', $paymentIds)->delete();
                    }

                    // 2. Delete additional charges
                    AdditionalCharge::where('student_id', $sId)->delete();

                    // 3. Delete student fees
                    StudentFee::where('student_id', $sId)->delete();

                    // 4. Delete fee assignments
                    if (Schema::hasTable('student_fee_assignments')) {
                        DB::table('student_fee_assignments')->where('student_id', $sId)->delete();
                    }

                    // 5. Delete attendance
                    if (Schema::hasTable('student_attendance')) {
                        DB::table('student_attendance')->where('student_id', $sId)->delete();
                    }
                    if (Schema::hasTable('student_attendances')) {
                        DB::table('student_attendances')->where('student_id', $sId)->delete();
                    }

                    // 6. Delete grades
                    if (Schema::hasTable('student_grades')) {
                        DB::table('student_grades')->where('student_id', $sId)->delete();
                    }

                    // 7. Delete subjects
                    if (Schema::hasTable('student_subjects')) {
                        DB::table('student_subjects')->where('student_id', $sId)->delete();
                    }

                    // 8. Delete class enrollments
                    if (Schema::hasTable('student_class_enrollments')) {
                        DB::table('student_class_enrollments')->where('student_id', $sId)->delete();
                    }

                    // 9. Delete complaints
                    if (Schema::hasTable('complaints')) {
                        DB::table('complaints')->where('student_id', $sId)->delete();
                    }

                    // 10. Delete profiles, guardians, documents
                    if (Schema::hasTable('student_guardians')) {
                        DB::table('student_guardians')->where('student_id', $sId)->delete();
                    }
                    if (Schema::hasTable('student_documents')) {
                        DB::table('student_documents')->where('student_id', $sId)->delete();
                    }
                    if (Schema::hasTable('academic_records')) {
                        DB::table('academic_records')->where('student_id', $sId)->delete();
                    }
                    if (Schema::hasTable('student_profiles')) {
                        DB::table('student_profiles')->where('student_id', $sId)->delete();
                    }

                    // 11. Delete passport
                    if ($student->passport) {
                        Storage::disk('public')->delete($student->passport);
                    }

                    // 12. Delete User account if exists
                    if ($student->user_id) {
                        $u = User::find($student->user_id);
                        if ($u && $u->role && $u->role->role_name === 'Student') {
                            $u->delete();
                        }
                    }

                    // 13. Delete student record
                    $student->delete();
                    $count++;
                }
            }

            FinancialAuditLog::logAction('delete_students', null, [], [
                'mode' => $mode,
                'count' => $count,
                'session_id' => $sessionId,
                'term_id' => $termId,
                'class_id' => $classId,
            ]);

            DB::commit();

            $actionWord = $mode === 'soft' ? 'deactivated / soft-deleted' : 'permanently deleted';
            return redirect()->route('admin.reset.index', [
                'tab' => 'students',
                'session_id' => $sessionId,
                'term_id' => $termId,
                'class_id' => $classId,
            ])->with('success', "Successfully {$actionWord} {$count} student record(s) and all linked relations.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete students: ' . $e->getMessage());
        }
    }

    /**
     * Delete Classes with options to unassign students and clear class assignments.
     */
    public function deleteClasses(Request $request)
    {
        $classIds = array_filter(array_map('intval', (array) $request->input('class_ids', [])));
        $unassignStudents = (bool) $request->input('unassign_students', false);
        $confirmPhrase = strtoupper(trim((string) $request->input('confirm_phrase', '')));

        if ($confirmPhrase !== 'DELETE') {
            return back()->with('error', 'Confirmation failed! Please type "DELETE" into the confirmation field.');
        }

        if (empty($classIds)) {
            return back()->with('error', 'Please select at least one class to delete.');
        }

        DB::beginTransaction();
        try {
            $classes = SchoolClass::whereIn('id', $classIds)->get();
            $deletedCount = 0;

            foreach ($classes as $class) {
                $cId = $class->id;
                $studentsCount = Student::where('current_class_id', $cId)->orWhere('class_link', $cId)->count();

                if ($studentsCount > 0 && !$unassignStudents) {
                    DB::rollBack();
                    return back()->with('error', "Class '{$class->class_name} {$class->section}' has {$studentsCount} student(s) currently enrolled. Check 'Unassign Enrolled Students' to proceed.");
                }

                // Unassign students from this class
                Student::where('current_class_id', $cId)->orWhere('class_link', $cId)->update([
                    'current_class_id' => null,
                    'class_link' => null,
                ]);

                // Clear class_subject_assignments
                if (Schema::hasTable('class_subject_assignments')) {
                    DB::table('class_subject_assignments')
                        ->where(function ($q) use ($cId) {
                            $q->where('school_class_id', $cId);
                            if (Schema::hasColumn('class_subject_assignments', 'class_id')) {
                                $q->orWhere('class_id', $cId);
                            }
                            if (Schema::hasColumn('class_subject_assignments', 'class_link')) {
                                $q->orWhere('class_link', $cId);
                            }
                        })->delete();
                }

                // Clear student_class_enrollments
                if (Schema::hasTable('student_class_enrollments')) {
                    DB::table('student_class_enrollments')->where('school_class_id', $cId)->delete();
                }

                // Clear timetable_periods
                if (Schema::hasTable('timetable_periods')) {
                    DB::table('timetable_periods')->where('school_class_id', $cId)->delete();
                }

                // Null out class_id on fee_structures
                FeeStructure::where('class_id', $cId)->update(['class_id' => null]);

                // Clear assigned_class_id on staff
                if (Schema::hasTable('staff') && Schema::hasColumn('staff', 'assigned_class_id')) {
                    DB::table('staff')->where('assigned_class_id', $cId)->update(['assigned_class_id' => null]);
                }

                // Clear teacher_subjects
                if (Schema::hasTable('teacher_subjects') && Schema::hasColumn('teacher_subjects', 'class_link')) {
                    DB::table('teacher_subjects')->where('class_link', $cId)->update(['class_link' => null]);
                }

                // Delete from legacy classes table if exists
                if (Schema::hasTable('classes')) {
                    DB::table('classes')->where('id', $cId)->orWhere('class_name', $class->class_name)->delete();
                }

                $class->delete();
                $deletedCount++;
            }

            FinancialAuditLog::logAction('delete_classes', null, [], [
                'deleted_count' => $deletedCount,
                'class_ids' => $classIds,
            ]);

            DB::commit();

            return redirect()->route('admin.reset.index', ['tab' => 'classes'])
                ->with('success', "Successfully deleted {$deletedCount} class(es).");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete classes: ' . $e->getMessage());
        }
    }

    /**
     * Delete Subjects and clear related curriculum assignments.
     */
    public function deleteSubjects(Request $request)
    {
        $subjectIds = array_filter(array_map('intval', (array) $request->input('subject_ids', [])));
        $confirmPhrase = strtoupper(trim((string) $request->input('confirm_phrase', '')));

        if ($confirmPhrase !== 'DELETE') {
            return back()->with('error', 'Confirmation failed! Please type "DELETE" into the confirmation field.');
        }

        if (empty($subjectIds)) {
            return back()->with('error', 'Please select at least one subject to delete.');
        }

        DB::beginTransaction();
        try {
            $subjects = Subject::whereIn('id', $subjectIds)->get();
            $deletedCount = 0;

            foreach ($subjects as $subject) {
                $subId = $subject->id;

                // Clear class_subject_assignments
                if (Schema::hasTable('class_subject_assignments')) {
                    DB::table('class_subject_assignments')
                        ->where(function ($q) use ($subId) {
                            $q->where('subject_id', $subId);
                            if (Schema::hasColumn('class_subject_assignments', 'subject_link')) {
                                $q->orWhere('subject_link', $subId);
                            }
                        })->delete();
                }

                // Clear teacher_subjects
                if (Schema::hasTable('teacher_subjects')) {
                    DB::table('teacher_subjects')
                        ->where(function ($q) use ($subId) {
                            if (Schema::hasColumn('teacher_subjects', 'subject_id')) {
                                $q->where('subject_id', $subId);
                            }
                            if (Schema::hasColumn('teacher_subjects', 'subject_link')) {
                                $q->orWhere('subject_link', $subId);
                            }
                        })->delete();
                }

                // Clear student_subjects
                if (Schema::hasTable('student_subjects')) {
                    DB::table('student_subjects')->where('subject_id', $subId)->delete();
                }

                // Clear staff assigned_subject_id
                if (Schema::hasTable('staff') && Schema::hasColumn('staff', 'assigned_subject_id')) {
                    DB::table('staff')->where('assigned_subject_id', $subId)->update(['assigned_subject_id' => null]);
                }

                // Clear timetable_periods
                if (Schema::hasTable('timetable_periods')) {
                    DB::table('timetable_periods')->where('subject_id', $subId)->delete();
                }

                $subject->delete();
                $deletedCount++;
            }

            FinancialAuditLog::logAction('delete_subjects', null, [], [
                'deleted_count' => $deletedCount,
                'subject_ids' => $subjectIds,
            ]);

            DB::commit();

            return redirect()->route('admin.reset.index', ['tab' => 'subjects'])
                ->with('success', "Successfully deleted {$deletedCount} subject(s).");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete subjects: ' . $e->getMessage());
        }
    }

    /**
     * Bulk Allocate Fees for selected or filtered students.
     */
    public function bulkAllocate(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:academic_sessions,id',
            'term_id' => 'required|exists:terms,id',
        ]);

        $sessionId = (int) $request->session_id;
        $termId = (int) $request->term_id;
        $classId = (int) $request->get('class_id', 0);
        $feeStructureId = (int) $request->get('fee_structure_id', 0);
        $category = $request->get('category', '');
        $gender = $request->get('gender', '');
        $selectedStudentIds = array_filter(array_map('intval', (array) $request->get('student_ids', [])));

        if (empty($selectedStudentIds)) {
            $sQuery = Student::where('status', 'active');
            if ($classId > 0) {
                $sQuery->where(function ($q) use ($classId) {
                    $q->where('current_class_id', $classId)->orWhere('class_link', $classId);
                });
            }
            if (!empty($category)) {
                $sQuery->where('category', $category);
            }
            if (!empty($gender)) {
                $sQuery->where('gender', $gender);
            }
            $selectedStudentIds = $sQuery->pluck('id')->toArray();
        }

        if (empty($selectedStudentIds)) {
            return back()->with('error', 'No eligible active students found to allocate fees.');
        }

        try {
            $result = FeeAllocationService::bulkAllocateFees(
                $selectedStudentIds,
                $feeStructureId > 0 ? $feeStructureId : null,
                $sessionId,
                $termId,
                true
            );

            $msg = "Bulk Allocation Complete: Successfully allocated fees to {$result['allocated']} student(s).";
            if ($result['skipped'] > 0) {
                $msg .= " ({$result['skipped']} student(s) already had active fees allocated).";
            }
            if ($result['failed'] > 0) {
                $msg .= " ({$result['failed']} failed).";
            }

            return redirect()->route('admin.reset.index', [
                'tab' => 'bulk_allocate',
                'session_id' => $sessionId,
                'term_id' => $termId,
                'class_id' => $classId,
            ])->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'Bulk allocation failed: ' . $e->getMessage());
        }
    }
}

