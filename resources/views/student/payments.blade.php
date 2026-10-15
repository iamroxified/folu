@extends('student.layout')

@section('title', 'Fees & Payments - ' . ($student->first_name ?? 'Student'))

@section('content')
@php
    $progressPct = $totalDue > 0 ? min(100, round(($totalPaid / $totalDue) * 100)) : 100;
    $statusPill = match(true) {
        $totalBalance <= 0.01 && $totalPaid > 0 => ['label' => 'Fully Cleared', 'badge' => 'bg-success', 'icon' => 'fa-check-circle'],
        $totalPaid > 0 => ['label' => 'Partial Payment', 'badge' => 'bg-warning text-dark', 'icon' => 'fa-clock'],
        default => ['label' => 'Payment Due', 'badge' => 'bg-danger', 'icon' => 'fa-exclamation-circle']
    };
@endphp

<!-- Page Header & Breadcrumbs -->
<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Fees & Financial Records</h3>
        <ul class="breadcrumbs ps-0 mb-0 d-flex align-items-center text-muted" style="list-style: none; font-size: 13px;">
            <li class="nav-home"><a href="{{ route('student.dashboard') }}" class="text-muted"><i class="fas fa-home"></i></a></li>
            <li class="separator mx-2"><i class="fas fa-chevron-right text-muted" style="font-size: 10px;"></i></li>
            <li class="nav-item text-dark fw-semibold">School Fees & Billing Statement</li>
        </ul>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('student.receipts') }}" class="btn btn-primary btn-round btn-sm px-3 shadow-sm">
            <i class="fas fa-receipt me-1"></i> Payment Receipts
        </a>
    </div>
</div>

<!-- 3 Modern Financial Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-stats card-round shadow-sm border-0 h-100">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-4">
                        <div class="icon-big text-center icon-primary bubble-shadow-small rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(114, 9, 34, 0.1); color: #720922;">
                            <i class="fas fa-file-invoice-dollar fa-lg"></i>
                        </div>
                    </div>
                    <div class="col-8 col-stats">
                        <div class="numbers">
                            <p class="card-category text-muted small mb-0">Total Invoiced Fees</p>
                            <h4 class="card-title fw-bold text-dark mb-0">₦{{ number_format($totalDue, 2) }}</h4>
                            <span class="text-muted small" style="font-size: 11px;">All Scheduled Fees</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-stats card-round shadow-sm border-0 h-100">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-4">
                        <div class="icon-big text-center icon-success bubble-shadow-small rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(40, 167, 69, 0.12); color: #28a745;">
                            <i class="fas fa-check-double fa-lg"></i>
                        </div>
                    </div>
                    <div class="col-8 col-stats">
                        <div class="numbers">
                            <p class="card-category text-muted small mb-0">Total Amount Paid</p>
                            <h4 class="card-title fw-bold text-success mb-0">₦{{ number_format($totalPaid, 2) }}</h4>
                            <div class="progress mt-1" style="height: 4px;">
                                <div class="progress-bar bg-success rounded" role="progressbar" style="width: {{ $progressPct }}%;"></div>
                            </div>
                            <span class="text-muted small" style="font-size: 11px;">{{ $progressPct }}% Cleared</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-stats card-round shadow-sm border-0 h-100">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-4">
                        <div class="icon-big text-center icon-danger bubble-shadow-small rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(220, 53, 69, 0.12); color: #dc3545;">
                            <i class="fas fa-balance-scale-right fa-lg"></i>
                        </div>
                    </div>
                    <div class="col-8 col-stats">
                        <div class="numbers">
                            <p class="card-category text-muted small mb-0">Outstanding Balance</p>
                            <h4 class="card-title fw-bold {{ $totalBalance > 0 ? 'text-danger' : 'text-success' }} mb-0">
                                ₦{{ number_format($totalBalance, 2) }}
                            </h4>
                            <span class="badge {{ $statusPill['badge'] }} px-2 py-1 mt-1" style="font-size: 10px;">
                                <i class="fas {{ $statusPill['icon'] }} me-1"></i> {{ $statusPill['label'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fee Schedules & Invoices Table -->
<div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle text-white p-2 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #720922, #8e1532); width: 34px; height: 34px;">
                <i class="fas fa-layer-group"></i>
            </div>
            <h5 class="card-title fw-bold mb-0 text-dark" style="font-size: 16px;">Allocated Tuition & Institutional Fees</h5>
        </div>
        <span class="badge bg-light text-dark border px-3 py-1">{{ $studentFees->count() }} Fee Items</span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Fee Item / Type</th>
                        <th>Academic Session & Term</th>
                        <th class="text-end">Amount Due</th>
                        <th class="text-end">Amount Paid</th>
                        <th class="text-end">Balance Owed</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($studentFees as $fee)
                        @php
                            $feeDue = (float)($fee->base_amount > 0.001 ? $fee->base_amount : $fee->amount_due);
                            $feePaid = (float)$fee->amount_paid;
                            $feeBal = max(0.00, (float)$fee->balance);
                            $fStatus = match(true) {
                                $feeBal <= 0.01 && $feePaid > 0 => 'paid',
                                $feePaid > 0 => 'partial',
                                default => 'unpaid'
                            };
                            $feeBadge = match($fStatus) {
                                'paid' => 'bg-success',
                                'partial' => 'bg-warning text-dark',
                                default => 'bg-danger'
                            };
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded p-2 bg-light text-primary border">
                                        <i class="fas fa-money-check-alt"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold text-dark d-block">
                                            {{ $fee->feeStructure?->name ?? ($fee->feeStructure?->description ?? 'School Tuition Fee') }}
                                        </span>
                                        <span class="text-muted small">
                                            Type: <code>{{ ucfirst($fee->feeStructure?->fee_type ?? 'Tuition') }}</code>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark d-block small">
                                    {{ $fee->session?->session_name ?? 'Current Session' }}
                                </span>
                                <span class="text-muted small">
                                    {{ $fee->term?->term_name ?? 'Full Session' }}
                                </span>
                            </td>
                            <td class="text-end fw-bold text-dark">₦{{ number_format($feeDue, 2) }}</td>
                            <td class="text-end fw-bold text-success">₦{{ number_format($feePaid, 2) }}</td>
                            <td class="text-end fw-bold {{ $feeBal > 0 ? 'text-danger' : 'text-muted' }}">
                                ₦{{ number_format($feeBal, 2) }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $feeBadge }} px-3 py-1 rounded-pill" style="font-size: 11px;">
                                    {{ ucfirst($fStatus) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-info-circle me-1"></i> No fee schedules have been assigned to your profile.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Additional Charges Table (if any) -->
@if($additionalCharges->count() > 0)
<div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title fw-bold mb-0 text-dark" style="font-size: 16px;">
            <i class="fas fa-plus-circle text-info me-2"></i> Additional Sundry Charges
        </h5>
        <span class="badge bg-light text-dark border">{{ $additionalCharges->count() }} Charges</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Charge Description</th>
                        <th class="text-end">Amount Due</th>
                        <th class="text-end">Amount Paid</th>
                        <th class="text-end">Balance</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($additionalCharges as $charge)
                        @php
                            $chDue = (float)$charge->amount;
                            $chPaid = (float)$charge->amount_paid;
                            $chBal = max(0.00, $chDue - $chPaid);
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold text-dark d-block">{{ $charge->fee_name ?? 'Additional Charge' }}</span>
                                <span class="text-muted small">{{ $charge->reason ?: 'Sundry Fee' }}</span>
                            </td>
                            <td class="text-end fw-bold text-dark">₦{{ number_format($chDue, 2) }}</td>
                            <td class="text-end fw-bold text-success">₦{{ number_format($chPaid, 2) }}</td>
                            <td class="text-end fw-bold {{ $chBal > 0 ? 'text-danger' : 'text-muted' }}">₦{{ number_format($chBal, 2) }}</td>
                            <td class="text-center">
                                <span class="badge {{ $chBal <= 0 ? 'bg-success' : 'bg-warning text-dark' }} px-3 py-1 rounded-pill">
                                    {{ $chBal <= 0 ? 'Paid' : 'Unpaid' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Payment Transactions History Table -->
<div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle text-white p-2 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #720922, #8e1532); width: 34px; height: 34px;">
                <i class="fas fa-history"></i>
            </div>
            <h5 class="card-title fw-bold mb-0 text-dark" style="font-size: 16px;">Verified Payment Transactions</h5>
        </div>
        <span class="badge bg-light text-dark border px-3 py-1">{{ $payments->count() }} Payments Recorded</span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Reference Number</th>
                        <th>Fee Particulars</th>
                        <th class="text-end">Amount Paid</th>
                        <th>Date of Payment</th>
                        <th>Payment Method</th>
                        <th class="text-center">Official Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $pmt)
                        <tr>
                            <td class="ps-4">
                                <code class="text-primary fw-bold">{{ $pmt->payment_reference }}</code>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark d-block">
                                    {{ $pmt->payable?->feeStructure?->name ?? ($pmt->description ?? 'School Tuition Payment') }}
                                </span>
                            </td>
                            <td class="text-end fw-bold text-success">
                                ₦{{ number_format($pmt->amount, 2) }}
                            </td>
                            <td class="text-muted small">
                                <i class="fas fa-calendar-alt me-1 text-muted"></i>
                                {{ !empty($pmt->payment_date) ? \Carbon\Carbon::parse($pmt->payment_date)->format('d/m/Y') : $pmt->created_at?->format('d/m/Y') }}
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1">
                                    <i class="fas fa-credit-card me-1 text-primary"></i>
                                    {{ ucfirst(str_replace('_', ' ', $pmt->payment_method ?? 'Bank Transfer')) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="/student/receipt.php?id={{ $pmt->id }}" target="_blank" class="btn btn-outline-primary btn-sm btn-round px-3">
                                    <i class="fas fa-print me-1"></i> View Receipt
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-receipt me-1"></i> No completed payments have been recorded for your account yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection