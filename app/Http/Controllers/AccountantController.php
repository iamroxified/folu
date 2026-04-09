<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\FeeStructure;
use App\Models\StudentFee;
use App\Models\Payment;
use App\Models\Staff;
use App\Models\Payroll;
use Illuminate\Support\Facades\DB;

class AccountantController extends Controller
{
    public function dashboard()
    {
        // Get financial overview data
        $totalStudents = Student::count();
        $totalFees = StudentFee::sum('amount_due');
        $totalPayments = Payment::where('payable_type', 'student_fee')->sum('amount');
        $pendingPayments = $totalFees - $totalPayments;
        $totalStaff = Staff::count();
        $totalPayroll = Payment::where('payable_type', 'staff_salary')->sum('amount');

        return view('accountant.dashboard', compact(
            'totalStudents', 'totalFees', 'totalPayments', 'pendingPayments', 'totalStaff', 'totalPayroll'
        ));
    }

    public function fees()
    {
        $feeStructures = FeeStructure::all();
        $studentFees = StudentFee::with(['student', 'feeStructure'])->get();

        return view('accountant.fees', compact('feeStructures', 'studentFees'));
    }

    public function payments()
    {
        $payments = Payment::with('payable')->where('payable_type', 'student_fee')->orderBy('created_at', 'desc')->get();

        return view('accountant.payments', compact('payments'));
    }

    public function recordPayment(Request $request)
    {
        $request->validate([
            'student_fee_id' => 'required|exists:student_fees,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'payer_name' => 'required|string',
            'payer_phone' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $studentFee = StudentFee::findOrFail($request->student_fee_id);

        $payment = Payment::create([
            'payment_reference' => 'PAY-' . time() . '-' . rand(1000, 9999),
            'payable_type' => 'student_fee',
            'payable_id' => $studentFee->id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_date' => $request->payment_date,
            'payer_name' => $request->payer_name,
            'payer_phone' => $request->payer_phone,
            'description' => $request->description,
            'status' => 'completed',
        ]);

        // Update student fee amounts
        $studentFee->amount_paid += $request->amount;
        $studentFee->balance = $studentFee->amount_due - $studentFee->amount_paid;
        $studentFee->status = $studentFee->balance <= 0 ? 'paid' : 'partial';
        $studentFee->save();

        return redirect()->route('accountant.payments')->with('success', 'Payment recorded successfully.');
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

        Payment::create([
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
                    'fee_structure_name' => $fee->feeStructure->name,
                    'amount' => $fee->amount_due,
                    'balance' => $fee->balance,
                ];
            });

        return response()->json($fees);
    }

    public function reports()
    {
        // Financial reports data
        $monthlyPayments = Payment::select(
            DB::raw('YEAR(payment_date) as year'),
            DB::raw('MONTH(payment_date) as month'),
            DB::raw('SUM(amount) as total')
        )->groupBy('year', 'month')->get();

        $feeCollections = StudentFee::select(
            'fee_structures.name',
            DB::raw('SUM(student_fees.amount_due) as total_assigned'),
            DB::raw('SUM(student_fees.amount_paid) as total_paid')
        )->join('fee_structures', 'student_fees.fee_structure_id', '=', 'fee_structures.id')
         ->groupBy('fee_structures.name')->get();

        return view('accountant.reports', compact('monthlyPayments', 'feeCollections'));
    }
}
