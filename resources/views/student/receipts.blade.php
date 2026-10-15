@extends('student.layout')

@section('title', 'Payment Receipts - ' . ($student->first_name ?? 'Student'))

@section('content')
<!-- Page Header & Breadcrumbs -->
<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Official Payment Receipts</h3>
        <ul class="breadcrumbs ps-0 mb-0 d-flex align-items-center text-muted" style="list-style: none; font-size: 13px;">
            <li class="nav-home"><a href="{{ route('student.dashboard') }}" class="text-muted"><i class="fas fa-home"></i></a></li>
            <li class="separator mx-2"><i class="fas fa-chevron-right text-muted" style="font-size: 10px;"></i></li>
            <li class="nav-item"><a href="{{ route('student.payments') }}" class="text-muted">Fees & Payments</a></li>
            <li class="separator mx-2"><i class="fas fa-chevron-right text-muted" style="font-size: 10px;"></i></li>
            <li class="nav-item text-dark fw-semibold">Receipt Repository</li>
        </ul>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('student.payments') }}" class="btn btn-outline-secondary btn-round btn-sm px-3 shadow-sm">
            <i class="fas fa-wallet me-1"></i> Fee Breakdown
        </a>
    </div>
</div>

@if($payments->count() > 0)
    <div class="row g-4">
        @foreach($payments as $payment)
            @php
                $feeName = $payment->payable?->feeStructure?->name ?? ($payment->payable?->feeStructure?->description ?? ($payment->description ?? 'School Tuition Fee'));
                $paymentDate = !empty($payment->payment_date) ? \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') : $payment->created_at?->format('M d, Y');
                $receiptRef = $payment->payment_reference ?: ('RCPT-' . str_pad($payment->id, 5, '0', STR_PAD_LEFT));
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm border-0 rounded-4 h-100 position-relative overflow-hidden transition-all hover-shadow">
                    <!-- Top Accent Border -->
                    <div style="height: 5px; background: linear-gradient(135deg, #720922, #8e1532);"></div>
                    
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <!-- Receipt Header -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 mb-1" style="font-size: 10px;">
                                        <i class="fas fa-shield-alt me-1"></i> Verified Receipt
                                    </span>
                                    <h6 class="fw-bold text-dark font-monospace mb-0" style="font-size: 13px;">
                                        {{ $receiptRef }}
                                    </h6>
                                </div>
                                <div class="rounded-circle bg-light p-2 text-success border d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>

                            <!-- Amount Paid Callout -->
                            <div class="p-3 bg-light rounded-3 mb-3 border">
                                <span class="text-muted small d-block mb-1">Amount Settled</span>
                                <h3 class="fw-bold text-success mb-0">₦{{ number_format($payment->amount, 2) }}</h3>
                            </div>

                            <!-- Key Particulars -->
                            <div class="small">
                                <div class="d-flex justify-content-between py-1 border-bottom border-light-subtle">
                                    <span class="text-muted">Fee Type:</span>
                                    <span class="fw-bold text-dark text-truncate ms-2" style="max-width: 170px;">{{ $feeName }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-1 border-bottom border-light-subtle">
                                    <span class="text-muted">Payment Date:</span>
                                    <span class="fw-semibold text-dark">{{ $paymentDate }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-1 border-bottom border-light-subtle">
                                    <span class="text-muted">Method:</span>
                                    <span class="badge bg-light text-dark border">
                                        {{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'Bank Transfer')) }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted">Status:</span>
                                    <span class="badge bg-success text-white px-2 py-0">Completed</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="mt-4 pt-2 border-top">
                            <a href="/student/receipt.php?id={{ $payment->id }}" target="_blank" class="btn btn-outline-primary btn-round w-100 btn-sm fw-bold">
                                <i class="fas fa-print me-1"></i> View & Print Official Slip
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <!-- Empty State -->
    <div class="card shadow-sm border-0 rounded-4 text-center py-5">
        <div class="card-body">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center p-4 mb-3" style="background: rgba(114, 9, 34, 0.08); width: 80px; height: 80px;">
                <i class="fas fa-receipt text-primary fa-2x"></i>
            </div>
            <h4 class="fw-bold text-dark mb-2">No Payment Receipts Available</h4>
            <p class="text-muted mx-auto" style="max-width: 480px;">
                Official school receipts are automatically issued and archived here whenever your tuition, books, uniform, or sundry fee payments are posted and verified.
            </p>
            <a href="{{ route('student.payments') }}" class="btn btn-outline-primary btn-round px-4">
                <i class="fas fa-wallet me-1"></i> Check Fee Schedule
            </a>
        </div>
    </div>
@endif
@endsection