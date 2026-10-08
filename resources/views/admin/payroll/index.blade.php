<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Staff Payroll Management - Admin Panel</title>
    @include('admin.partials.links')
</head>
<body>
    <div class="wrapper">
        @include('admin.partials.sidebar')

        <div class="main-panel">
            @include('admin.partials.header')
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
                        <div>
                            <h2 class="text-dark fw-bold mb-1"><i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Staff Payroll System</h2>
                            <p class="text-muted mb-0">Record, track, and manage staff salary payments, class/subject assignments, and payment statuses.</p>
                        </div>
                        <div class="ms-md-auto py-2 py-md-0">
                            <a href="{{ route('admin.payroll.export') }}" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-file-export me-1"></i> Export CSV
                            </a>
                            <a href="{{ route('admin.payroll.create') }}" class="btn btn-primary fw-bold">
                                <i class="fas fa-plus me-1"></i> Record Staff Payment
                            </a>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Summary Cards -->
                    <div class="row">
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                                <i class="fas fa-money-check-alt"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Total Payroll Paid</p>
                                                <h4 class="card-title text-success">₦{{ number_format($stats['total_payroll_paid'], 2) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-warning bubble-shadow-small">
                                                <i class="fas fa-exclamation-circle"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Unpaid / Pending</p>
                                                <h4 class="card-title text-warning">₦{{ number_format($stats['total_payroll_pending'], 2) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                                <i class="fas fa-users-cog"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Payroll Records</p>
                                                <h4 class="card-title">{{ number_format($stats['total_records']) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                                <i class="fas fa-user-check"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Paid Payments</p>
                                                <h4 class="card-title">{{ number_format($stats['paid_records_count']) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Bar -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.payroll') }}" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Filter by Staff</label>
                                    <select name="staff_id" class="form-select select2">
                                        <option value="">All Staff Members</option>
                                        @foreach($staffList as $st)
                                            <option value="{{ $st->id }}" {{ request('staff_id') == $st->id ? 'selected' : '' }}>
                                                {{ $st->full_name }} ({{ $st->staff_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Month</label>
                                    <select name="month" class="form-select">
                                        <option value="">All Months</option>
                                        @foreach($months as $m)
                                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ $m }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Staff Type</label>
                                    <select name="staff_type" class="form-select">
                                        <option value="">All Types</option>
                                        <option value="full_time" {{ request('staff_type') == 'full_time' ? 'selected' : '' }}>Full Time</option>
                                        <option value="part_time" {{ request('staff_type') == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">All Statuses</option>
                                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="not_paid" {{ request('status') == 'not_paid' ? 'selected' : '' }}>Not Paid</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Search</label>
                                    <input type="text" name="search" class="form-control" placeholder="Search ref, staff, subject, month..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100 me-2"><i class="fas fa-search me-1"></i> Filter</button>
                                    <a href="{{ route('admin.payroll') }}" class="btn btn-light" title="Reset"><i class="fas fa-undo"></i></a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Payroll Records Table -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h4 class="card-title text-dark fw-bold mb-0">Payroll Payment Records</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover table-bordered align-middle">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th style="width: 50px;">S/N</th>
                                                    <th>Reference</th>
                                                    <th>Staff Name</th>
                                                    <th>Staff Type</th>
                                                    <th>Class / Subject Taken</th>
                                                    <th>Month Paid For</th>
                                                    <th>Salary / Net Pay</th>
                                                    <th>Payment Date</th>
                                                    <th>Method</th>
                                                    <th>Status</th>
                                                    <th class="text-center" style="width: 140px;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($payrolls as $index => $payroll)
                                                    <tr>
                                                        <td>{{ $payrolls->firstItem() + $index }}</td>
                                                        <td><span class="badge bg-secondary font-monospace">{{ $payroll->payment_reference }}</span></td>
                                                        <td>
                                                            <div class="fw-bold text-dark">{{ $payroll->staff ? $payroll->staff->full_name : 'Staff #' . $payroll->staff_id }}</div>
                                                            <small class="text-muted">{{ $payroll->staff ? $payroll->staff->staff_number : '' }}</small>
                                                        </td>
                                                        <td>
                                                            @if($payroll->staff_type === 'part_time')
                                                                <span class="badge bg-info text-dark"><i class="fas fa-clock me-1"></i> Part Time</span>
                                                            @else
                                                                <span class="badge bg-primary"><i class="fas fa-briefcase me-1"></i> Full Time</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="fw-semibold">{{ $payroll->class_subject ?: ($payroll->staff ? $payroll->staff->class_or_subject : 'N/A') }}</div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-dark fs-6"><i class="fas fa-calendar-alt me-1 text-warning"></i> {{ $payroll->pay_period_formatted }}</span>
                                                        </td>
                                                        <td>
                                                            <div class="fw-bold text-success">₦{{ number_format($payroll->net_pay, 2) }}</div>
                                                         <td>{{ $payroll->pay_date ? $payroll->pay_date->format('d M, Y') : 'N/A' }}</td>
                                                        <td><span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $payroll->payment_method)) }}</span></td>
                                                        <td>
                                                            @if($payroll->status === 'paid')
                                                                <span class="badge bg-success px-3 py-2"><i class="fas fa-check-circle me-1"></i> Paid</span>
                                                            @elseif($payroll->status === 'not_paid' || $payroll->status === 'unpaid')
                                                                <span class="badge bg-danger px-3 py-2"><i class="fas fa-times-circle me-1"></i> Not Paid</span>
                                                            @else
                                                                <span class="badge bg-warning text-dark px-3 py-2"><i class="fas fa-hourglass-half me-1"></i> {{ ucfirst($payroll->status) }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="btn-group">
                                                                <a href="{{ route('admin.payroll.show', $payroll->id) }}" class="btn btn-sm btn-info text-white" title="View Payslip/Details">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="{{ route('admin.payroll.edit', $payroll->id) }}" class="btn btn-sm btn-warning text-white" title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <form action="{{ route('admin.payroll.destroy', $payroll->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this payroll record?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                                        <i class="fas fa-trash-alt"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="10" class="text-center py-5">
                                                            <div class="text-muted mb-2"><i class="fas fa-folder-open fa-3x"></i></div>
                                                            <h5>No payroll records found.</h5>
                                                            <p class="mb-3">Click below to record a new staff salary payment.</p>
                                                            <a href="{{ route('admin.payroll.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Record Staff Payment</a>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end mt-3">
                                        {{ $payrolls->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            @include('admin.partials.footer')
        </div>
    </div>
</body>
</html>
