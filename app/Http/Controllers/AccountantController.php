<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\FeeStructure;
use App\Models\StudentFee;
use App\Models\AdditionalCharge;
use App\Models\Payment;
use App\Models\Staff;
use App\Models\Payroll;
use App\Models\FinancialAuditLog;
use App\Services\FeeAllocationService;
use Illuminate\Support\Facades\DB;

class AccountantController extends Controller
{
    public function dashboard()
    {
        $totalStudents = Student::count();
        $newIntakeCount = Student::whereIn('category', ['NI', 'NEW_INTAKE', 'NEW'])->count();
        $returningCount = Student::whereIn('category', ['OS', 'RETURNING', 'OLD'])->count();

        $totalExpectedFees = StudentFee::sum('total_payable');
        $totalCollected = Payment::where('payable_type', 'student_fee')->orWhere('payable_type', 'App\\Models\\StudentFee')->where('status', 'completed')->sum('amount');
        $totalOutstanding = StudentFee::sum('amount_owed');

        $fullyPaidCount = StudentFee::where('status', 'paid')->count();
        $partiallyPaidCount = StudentFee::where('status', 'partially_paid')->count();
        $unpaidCount = StudentFee::where('status', 'unpaid')->count();
        $overpaidCount = StudentFee::where('status', 'overpaid')->count();

        $totalStaff = Staff::count();
        $totalPayroll = Payment::where('payable_type', 'staff_salary')->sum('amount');

        return view('accountant.dashboard', compact(
            'totalStudents',
            'newIntakeCount',
            'returningCount',
            'totalExpectedFees',
            'totalCollected',
            'totalOutstanding',
            'fullyPaidCount',
            'partiallyPaidCount',
            'unpaidCount',
            'overpaidCount',
            'totalStaff',
            'totalPayroll'
        ));
    }

    public function fees()
    {
        $feeStructures = FeeStructure::with(['session', 'term', 'schoolClass'])->get();
        $studentFees = StudentFee::with(['student.currentClass', 'feeStructure', 'additionalCharges'])->get();

        return view('accountant.fees', compact('feeStructures', 'studentFees'));
    }

    public function payments()
    {
        $payments = Payment::with('payable')->whereIn('payable_type', ['student_fee', 'App\\Models\\StudentFee', 'additional_charge', 'App\\Models\\AdditionalCharge'])->orderBy('created_at', 'desc')->get();
        $studentFees = StudentFee::with('student')->where('amount_owed', '>', 0)->get();

        return view('accountant.payments', compact('payments', 'studentFees'));
    }

    public function recordPayment(Request $request)
    {
        $request->validate([
            'student_fee_id' => 'required|exists:student_fees,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'payer_name' => 'required|string',
            'payer_phone' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $studentFee = StudentFee::findOrFail($request->student_fee_id);

        $payment = Payment::create([
            'payment_reference' => 'PAY-' . time() . '-' . rand(1000, 9999),
            'payable_type' => 'App\\Models\\StudentFee',
            'payable_id' => $studentFee->id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_date' => $request->payment_date,
            'payer_name' => $request->payer_name,
            'payer_phone' => $request->payer_phone,
            'description' => $request->description,
            'status' => 'completed',
        ]);

        // Recalculate totals automatically
        $studentFee->recalculateTotals();

        FinancialAuditLog::logAction('recorded_payment', $payment, [], [
            'student_fee_id' => $studentFee->id,
            'amount' => $request->amount,
            'payment_reference' => $payment->payment_reference,
        ]);

        return redirect()->route('accountant.payments')->with('success', 'Payment recorded successfully and student balance recalculated.');
    }

    public function storeAdditionalCharge(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'fee_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
        ]);

        $student = Student::findOrFail($request->student_id);

        FeeAllocationService::addAdditionalCharge(
            $student,
            $request->fee_name,
            (float) $request->amount,
            $request->reason
        );

        return back()->with('success', 'Additional fee charge added successfully.');
    }

    public function payroll()
    {
        $payrolls = Payment::with('payable')->where('payable_type', 'staff_salary')->get();
        $staff = Staff::all();

        return view('accountant.payroll', compact('payrolls', 'staff'));
    }

    public function createPayroll(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'payer_name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $staff = Staff::findOrFail($request->staff_id);

        $payment = Payment::create([
            'payment_reference' => 'SAL-' . time() . '-' . rand(1000, 9999),
            'payable_type' => 'staff_salary',
            'payable_id' => $staff->id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_date' => $request->payment_date,
            'payer_name' => $request->payer_name,
            'description' => $request->description,
            'status' => 'completed',
        ]);

        FinancialAuditLog::logAction('recorded_payroll', $payment, [], [
            'staff_id' => $staff->id,
            'amount' => $request->amount,
        ]);

        return redirect()->route('accountant.payroll')->with('success', 'Payroll payment recorded successfully.');
    }

    public function getStudentFees($studentId)
    {
        $fees = StudentFee::where('student_id', $studentId)
            ->with('feeStructure')
            ->get()
            ->map(function($fee) {
                return [
                    'id' => $fee->id,
                    'fee_structure_name' => $fee->feeStructure ? $fee->feeStructure->name : 'General Fee',
                    'base_amount' => $fee->base_amount,
                    'additional_amount' => $fee->additional_amount,
                    'total_payable' => $fee->total_payable,
                    'amount_paid' => $fee->amount_paid,
                    'amount_owed' => $fee->amount_owed,
                    'status' => $fee->status,
                ];
            });

        return response()->json($fees);
    }

    public function reports()
    {
        $monthlyPayments = Payment::select(
            DB::raw('YEAR(payment_date) as year'),
            DB::raw('MONTH(payment_date) as month'),
            DB::raw('SUM(amount) as total')
        )->groupBy('year', 'month')->get();

        $studentFeeStats = StudentFee::with(['student.currentClass', 'session', 'term'])->get();

        $additionalFeeReports = AdditionalCharge::with(['student', 'addedBy'])->latest()->get();

        return view('accountant.reports', compact('monthlyPayments', 'studentFeeStats', 'additionalFeeReports'));
    }

    public function exportReport(Request $request)
    {
        $type = $request->query('type', 'payments');

        if ($type === 'outstanding') {
            $studentFees = StudentFee::with(['student.currentClass', 'session', 'term'])->where('amount_owed', '>', 0)->get();

            return response()->streamDownload(function() use ($studentFees) {
                echo "Student Number,Student Name,Class,Session,Term,Total Payable,Amount Paid,Amount Owed,Status\n";
                foreach ($studentFees as $fee) {
                    $studentName = $fee->student ? $fee->student->first_name . ' ' . $fee->student->last_name : 'N/A';
                    $studentNum = $fee->student ? $fee->student->student_number : 'N/A';
                    $class = $fee->student && $fee->student->currentClass ? $fee->student->currentClass->class_name : 'N/A';
                    $session = $fee->session ? $fee->session->session_name : 'N/A';
                    $term = $fee->term ? $fee->term->term_name : 'N/A';
                    echo "\"{$studentNum}\",\"{$studentName}\",\"{$class}\",\"{$session}\",\"{$term}\",\"{$fee->total_payable}\",\"{$fee->amount_paid}\",\"{$fee->amount_owed}\",\"{$fee->status}\"\n";
                }
            }, 'outstanding_fees_report.csv');
        }

        if ($type === 'additional_fees') {
            $charges = AdditionalCharge::with(['student', 'addedBy', 'session', 'term'])->get();

            return response()->streamDownload(function() use ($charges) {
                echo "Student Name,Fee Name,Amount,Reason,Date Added,Added By,Session,Term\n";
                foreach ($charges as $c) {
                    $studentName = $c->student ? $c->student->first_name . ' ' . $c->student->last_name : 'N/A';
                    $addedBy = $c->addedBy ? $c->addedBy->name : 'Admin';
                    $session = $c->session ? $c->session->session_name : 'N/A';
                    $term = $c->term ? $c->term->term_name : 'N/A';
                    $date = $c->created_at ? $c->created_at->format('Y-m-d') : '';
                    echo "\"{$studentName}\",\"{$c->fee_name}\",\"{$c->amount}\",\"{$c->reason}\",\"{$date}\",\"{$addedBy}\",\"{$session}\",\"{$term}\"\n";
                }
            }, 'additional_fees_report.csv');
        }

        // Default payments report
        $payments = Payment::with('payable')->whereIn('payable_type', ['student_fee', 'App\\Models\\StudentFee'])->get();

        return response()->streamDownload(function() use ($payments) {
            echo "Reference,Amount,Method,Date,Payer Name,Payer Phone,Status\n";
            foreach ($payments as $p) {
                echo "\"{$p->payment_reference}\",\"{$p->amount}\",\"{$p->payment_method}\",\"{$p->payment_date}\",\"{$p->payer_name}\",\"{$p->payer_phone}\",\"{$p->status}\"\n";
            }
        }, 'payments_report.csv');
    }
}
