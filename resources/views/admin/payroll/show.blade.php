<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Staff Payslip #{{ $payroll->payment_reference }} - Admin Panel</title>
    @include('admin.partials.links')
    <style>
        @media print {
            .no-print { display: none !important; }
            .main-panel { width: 100% !important; margin: 0 !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="no-print">
            @include('admin.partials.sidebar')
        </div>

        <div class="main-panel">
            <div class="no-print">
                @include('admin.partials.header')
            </div>
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-center justify-content-between pb-3 no-print">
                        <h2 class="text-dark fw-bold mb-0">Staff Payment Details & Payslip</h2>
                        <div>
                            <button onclick="window.print();" class="btn btn-primary me-2">
                                <i class="fas fa-print me-1"></i> Print Payslip
                            </button>
                            <a href="{{ route('admin.payroll') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to List
                            </a>
                        </div>
                    </div>

                    <!-- Payslip Card -->
                    <div class="card shadow border-0 mb-4" id="printable-payslip">
                        <div class="card-body p-5">
                            <!-- School Banner Header -->
                            <div class="row align-items-center mb-4 pb-4 border-bottom">
                                <div class="col-md-7">
                                    <h2 class="fw-bold text-primary mb-1">{{ $schoolSettings ? $schoolSettings->school_name : 'Folu School Management' }}</h2>
                                    <p class="text-muted mb-1">{{ $schoolSettings->school_address ?? 'Official Staff Payment Receipt & Salary Voucher' }}</p>
                                    <p class="text-muted mb-0"><i class="fas fa-phone-alt me-1"></i> {{ $schoolSettings->school_phone ?? '' }} | <i class="fas fa-envelope me-1"></i> {{ $schoolSettings->school_email ?? '' }}</p>
                                </div>
                                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                                    <div class="badge bg-secondary font-monospace fs-6 px-3 py-2 mb-2">Ref: {{ $payroll->payment_reference }}</div>
                                    <br>
                                    @if($payroll->status === 'paid')
                                        <span class="badge bg-success fs-6 px-4 py-2"><i class="fas fa-check-circle me-1"></i> PAYMENT COMPLETED</span>
                                    @elseif($payroll->status === 'not_paid' || $payroll->status === 'unpaid')
                                        <span class="badge bg-danger fs-6 px-4 py-2"><i class="fas fa-times-circle me-1"></i> NOT PAID</span>
                                    @else
                                        <span class="badge bg-warning text-dark fs-6 px-4 py-2"><i class="fas fa-hourglass-half me-1"></i> {{ strtoupper($payroll->status) }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Staff & Assignment Details Grid -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded border">
                                        <h6 class="text-uppercase text-muted fw-bold mb-3">Staff Information</h6>
                                        <p class="mb-1"><strong>Staff Name:</strong> <span class="fs-5 fw-bold text-dark">{{ $payroll->staff ? $payroll->staff->full_name : 'N/A' }}</span></p>
                                        <p class="mb-1"><strong>Staff Number:</strong> {{ $payroll->staff ? $payroll->staff->staff_number : 'N/A' }}</p>
                                        <p class="mb-1"><strong>Position / Department:</strong> {{ $payroll->staff ? $payroll->staff->position : 'N/A' }} {{ $payroll->staff && $payroll->staff->department ? '('.$payroll->staff->department.')' : '' }}</p>
                                        <p class="mb-0"><strong>Email / Phone:</strong> {{ $payroll->staff ? $payroll->staff->email : 'N/A' }} | {{ $payroll->staff ? $payroll->staff->phone : 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-3 mt-md-0">
                                    <div class="p-3 bg-light rounded border">
                                        <h6 class="text-uppercase text-muted fw-bold mb-3">Payroll & Teaching Details</h6>
                                        <p class="mb-1"><strong>Month Paid For:</strong> <span class="badge bg-dark fs-6 text-warning"><i class="fas fa-calendar-alt me-1"></i> {{ $payroll->pay_period_formatted }}</span></p>
                                        <p class="mb-1"><strong>Staff Type:</strong>
                                            @if($payroll->staff_type === 'part_time')
                                                <span class="badge bg-info text-dark">Part Time</span>
                                            @else
                                                <span class="badge bg-primary">Full Time</span>
                                            @endif
                                        </p>
                                        <p class="mb-1"><strong>Class / Subject Taken:</strong> <span class="fw-semibold text-primary">{{ $payroll->class_subject ?: ($payroll->staff ? $payroll->staff->class_or_subject : 'N/A') }}</span></p>
                                        <p class="mb-1"><strong>Payment Date:</strong> {{ $payroll->pay_date ? $payroll->pay_date->format('F d, Y') : 'N/A' }}</p>
                                        <p class="mb-0"><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $payroll->payment_method)) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Financial Breakdown Table -->
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Description / Component</th>
                                            <th class="text-end" style="width: 250px;">Amount (₦)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Base Salary</strong></td>
                                            <td class="text-end font-monospace fw-bold">₦{{ number_format($payroll->basic_salary, 2) }}</td>
                                        </tr>
                                        @if($payroll->allowances > 0)
                                            <tr>
                                                <td>Allowances</td>
                                                <td class="text-end font-monospace text-success">+ ₦{{ number_format($payroll->allowances, 2) }}</td>
                                            </tr>
                                        @endif
                                        @if($payroll->bonuses > 0)
                                            <tr>
                                                <td>Bonuses</td>
                                                <td class="text-end font-monospace text-success">+ ₦{{ number_format($payroll->bonuses, 2) }}</td>
                                            </tr>
                                        @endif
                                        @if($payroll->overtime_pay > 0)
                                            <tr>
                                                <td>Overtime Pay</td>
                                                <td class="text-end font-monospace text-success">+ ₦{{ number_format($payroll->overtime_pay, 2) }}</td>
                                            </tr>
                                        @endif
                                        <tr class="table-light">
                                            <td><strong>Gross Salary</strong></td>
                                            <td class="text-end font-monospace fw-bold text-dark">₦{{ number_format($payroll->gross_pay, 2) }}</td>
                                        </tr>
                                        @if($payroll->deductions > 0)
                                            <tr>
                                                <td class="text-danger">Deductions / Tax</td>
                                                <td class="text-end font-monospace text-danger">- ₦{{ number_format($payroll->deductions, 2) }}</td>
                                            </tr>
                                        @endif
                                        <tr class="table-success">
                                            <td class="fs-5 fw-bold text-dark">NET PAYABLE SALARY</td>
                                            <td class="text-end font-monospace fs-4 fw-bold text-success">₦{{ number_format($payroll->net_pay, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            @if($payroll->remarks)
                                <div class="p-3 bg-light rounded border mb-4">
                                    <strong>Remarks / Notes:</strong> {{ $payroll->remarks }}
                                </div>
                            @endif

                            <!-- Signatures Footer -->
                            <div class="row pt-5 mt-4 border-top text-center">
                                <div class="col-md-6">
                                    <div class="border-bottom mx-auto" style="width: 200px;"></div>
                                    <p class="mt-2 mb-0 fw-semibold">Staff Signature</p>
                                </div>
                                <div class="col-md-6 mt-4 mt-md-0">
                                    <div class="border-bottom mx-auto" style="width: 200px;"></div>
                                    <p class="mt-2 mb-0 fw-semibold">Authorized Admin / Bursar Signature</p>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
            <div class="no-print">
                @include('admin.partials.footer')
            </div>
        </div>
    </div>
</body>
</html>
