@extends('accountant.layout')

@section('title', 'Financial Reports')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h2 class="fw-bold mb-4">Financial Reports & Analytics</h2>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="fas fa-calendar-alt me-2"></i>Monthly Payment Collections Summary</h5>
                <a href="{{ route('accountant.reports.export', ['type' => 'payments']) }}" class="btn btn-sm btn-light font-weight-bold">
                    <i class="fas fa-download me-1"></i> Export Payments CSV
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="datatable table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">S/N</th>
                                <th>Year</th>
                                <th>Month</th>
                                <th>Total Collected Amount (₦)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($monthlyPayments as $payment)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $payment->year }}</td>
                                    <td>{{ date('F', mktime(0, 0, 0, $payment->month, 10)) }}</td>
                                    <td class="fw-bold text-success">₦{{ number_format($payment->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No payment data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Outstanding Fees Report</h5>
                <a href="{{ route('accountant.reports.export', ['type' => 'outstanding']) }}" class="btn btn-sm btn-light font-weight-bold">
                    <i class="fas fa-download me-1"></i> Export Outstanding Fees CSV
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="datatable table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">S/N</th>
                                <th>Student Number</th>
                                <th>Student Name</th>
                                <th>Class</th>
                                <th>Session / Term</th>
                                <th>Total Payable (₦)</th>
                                <th>Amount Paid (₦)</th>
                                <th>Amount Owed (₦)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($studentFeeStats as $fee)
                                @if($fee->amount_owed > 0)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><code>{{ $fee->student ? $fee->student->student_number : 'N/A' }}</code></td>
                                        <td class="fw-bold">{{ $fee->student ? $fee->student->first_name . ' ' . $fee->student->last_name : 'N/A' }}</td>
                                        <td>{{ $fee->student && $fee->student->currentClass ? $fee->student->currentClass->class_name : 'N/A' }}</td>
                                        <td>
                                            {{ $fee->session ? $fee->session->session_name : 'N/A' }}
                                            {{ $fee->term ? '(' . $fee->term->term_name . ')' : '' }}
                                        </td>
                                        <td>₦{{ number_format($fee->total_payable, 2) }}</td>
                                        <td class="text-success fw-bold">₦{{ number_format($fee->amount_paid, 2) }}</td>
                                        <td class="text-danger fw-bold">₦{{ number_format($fee->amount_owed, 2) }}</td>
                                        <td>
                                            <span class="badge bg-warning text-dark">{{ strtoupper(str_replace('_', ' ', $fee->status)) }}</span>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No outstanding student fees found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="fas fa-plus-circle me-2"></i>Additional / Extra Fees Report</h5>
                <a href="{{ route('accountant.reports.export', ['type' => 'additional_fees']) }}" class="btn btn-sm btn-dark font-weight-bold">
                    <i class="fas fa-download me-1"></i> Export Extra Fees CSV
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="datatable table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">S/N</th>
                                <th>Student Name</th>
                                <th>Fee Name</th>
                                <th>Amount (₦)</th>
                                <th>Reason</th>
                                <th>Date Added</th>
                                <th>Added By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($additionalFeeReports as $charge)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-bold">{{ $charge->student ? $charge->student->first_name . ' ' . $charge->student->last_name : 'N/A' }}</td>
                                    <td class="fw-bold text-dark">{{ $charge->fee_name }}</td>
                                    <td class="fw-bold text-warning">₦{{ number_format($charge->amount, 2) }}</td>
                                    <td>{{ $charge->reason ?? 'N/A' }}</td>
                                    <td>{{ $charge->created_at ? $charge->created_at->format('d/m/Y') : 'N/A' }}</td>
                                    <td>{{ $charge->addedBy ? $charge->addedBy->name : 'Admin' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No additional fees logged yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection