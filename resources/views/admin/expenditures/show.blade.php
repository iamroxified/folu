<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Expenditure #{{ $expenditure->expenditure_number }} - Admin Panel</title>
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
                        <h2 class="text-dark fw-bold mb-0">Expenditure Voucher & Receipt Details</h2>
                        <div>
                            <button onclick="window.print();" class="btn btn-primary me-2">
                                <i class="fas fa-print me-1"></i> Print Voucher
                            </button>
                            <a href="{{ route('admin.expenditures') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Expenditures
                            </a>
                        </div>
                    </div>

                    <!-- Expenditure Voucher Card -->
                    <div class="card shadow border-0 mb-4" id="printable-voucher">
                        <div class="card-body p-5">
                            <div class="row align-items-center mb-4 pb-4 border-bottom">
                                <div class="col-md-7">
                                    <h2 class="fw-bold text-danger mb-1">{{ $schoolSettings ? $schoolSettings->school_name : 'Folu School Management' }}</h2>
                                    <p class="text-muted mb-1">Official School Expenditure Payment Voucher</p>
                                    <p class="text-muted mb-0"><i class="fas fa-phone-alt me-1"></i> {{ $schoolSettings->school_phone ?? '' }} | <i class="fas fa-envelope me-1"></i> {{ $schoolSettings->school_email ?? '' }}</p>
                                </div>
                                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                                    <div class="badge bg-secondary font-monospace fs-6 px-3 py-2 mb-2">Voucher No: {{ $expenditure->expenditure_number }}</div>
                                    <br>
                                    @if($expenditure->status === 'paid')
                                        <span class="badge bg-success fs-6 px-4 py-2"><i class="fas fa-check-circle me-1"></i> PAID</span>
                                    @elseif($expenditure->status === 'pending')
                                        <span class="badge bg-warning text-dark fs-6 px-4 py-2"><i class="fas fa-clock me-1"></i> PENDING</span>
                                    @elseif($expenditure->status === 'approved')
                                        <span class="badge bg-info text-white fs-6 px-4 py-2"><i class="fas fa-thumbs-up me-1"></i> APPROVED</span>
                                    @else
                                        <span class="badge bg-danger fs-6 px-4 py-2"><i class="fas fa-ban me-1"></i> CANCELLED</span>
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded border">
                                        <h6 class="text-uppercase text-muted fw-bold mb-3">Item Details</h6>
                                        <p class="mb-2"><strong>What was paid for:</strong> <br><span class="fs-5 fw-bold text-dark">{{ $expenditure->title }}</span></p>
                                        <p class="mb-1"><strong>Category:</strong> <span class="badge bg-primary">{{ $expenditure->category }}</span></p>
                                        <p class="mb-0"><strong>Vendor / Recipient:</strong> {{ $expenditure->vendor_recipient ?: 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-3 mt-md-0">
                                    <div class="p-3 bg-light rounded border">
                                        <h6 class="text-uppercase text-muted fw-bold mb-3">Transaction Information</h6>
                                        <p class="mb-1"><strong>Amount Paid:</strong> <span class="fs-4 fw-bold text-danger">₦{{ number_format($expenditure->amount, 2) }}</span></p>
                                        <p class="mb-1"><strong>Expenditure Date:</strong> {{ $expenditure->expenditure_date ? $expenditure->expenditure_date->format('F d, Y') : 'N/A' }}</p>
                                        <p class="mb-1"><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $expenditure->payment_method)) }}</p>
                                        <p class="mb-0"><strong>Recorded By:</strong> {{ $expenditure->recorder ? $expenditure->recorder->name : 'Admin System' }}</p>
                                    </div>
                                </div>
                            </div>

                            @if($expenditure->description)
                                <div class="p-3 bg-light rounded border mb-4">
                                    <h6 class="fw-bold mb-2">Description / Notes:</h6>
                                    <p class="mb-0 text-secondary">{{ $expenditure->description }}</p>
                                </div>
                            @endif

                            @if($expenditure->receipt_path)
                                <div class="p-3 bg-light rounded border mb-4 no-print">
                                    <h6 class="fw-bold mb-2"><i class="fas fa-paperclip me-1"></i> Attached Receipt Document:</h6>
                                    <a href="{{ asset('storage/' . $expenditure->receipt_path) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-external-link-alt me-1"></i> View / Download Attached Receipt File
                                    </a>
                                </div>
                            @endif

                            <div class="row pt-5 mt-4 border-top text-center">
                                <div class="col-md-6">
                                    <div class="border-bottom mx-auto" style="width: 200px;"></div>
                                    <p class="mt-2 mb-0 fw-semibold">Prepared / Recorded By</p>
                                </div>
                                <div class="col-md-6 mt-4 mt-md-0">
                                    <div class="border-bottom mx-auto" style="width: 200px;"></div>
                                    <p class="mt-2 mb-0 fw-semibold">Authorized Admin / Bursar Approval</p>
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
