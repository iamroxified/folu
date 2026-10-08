<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>School Expenditures Module - Admin Panel</title>
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
                            <h2 class="text-dark fw-bold mb-1"><i class="fas fa-receipt me-2 text-danger"></i>School Expenditure Module</h2>
                            <p class="text-muted mb-0">Capture, manage, and audit all operational expenses and payouts made in the school.</p>
                        </div>
                        <div class="ms-md-auto py-2 py-md-0">
                            <a href="{{ route('admin.expenditures.export') }}" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-file-export me-1"></i> Export CSV
                            </a>
                            <a href="{{ route('admin.expenditures.create') }}" class="btn btn-danger fw-bold">
                                <i class="fas fa-plus me-1"></i> Record Expenditure
                            </a>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Summary Cards -->
                    <div class="row">
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-danger bubble-shadow-small">
                                                <i class="fas fa-wallet"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Total Spent</p>
                                                <h4 class="card-title text-danger">₦{{ number_format($stats['total_amount'], 2) }}</h4>
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
                                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                                <i class="fas fa-check-double"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Paid Expenses</p>
                                                <h4 class="card-title text-success">₦{{ number_format($stats['paid_amount'], 2) }}</h4>
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
                                                <i class="fas fa-hourglass-start"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Pending Expenses</p>
                                                <h4 class="card-title text-warning">₦{{ number_format($stats['pending_amount'], 2) }}</h4>
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
                                                <i class="fas fa-file-invoice"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Total Records</p>
                                                <h4 class="card-title">{{ number_format($stats['total_count']) }}</h4>
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
                            <form method="GET" action="{{ route('admin.expenditures') }}" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Category</label>
                                    <select name="category" class="form-select">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">All Statuses</option>
                                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">From Date</label>
                                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">To Date</label>
                                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Search</label>
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" placeholder="Title, vendor, ref..." value="{{ request('search') }}">
                                        <button type="submit" class="btn btn-danger"><i class="fas fa-search"></i></button>
                                        <a href="{{ route('admin.expenditures') }}" class="btn btn-light border" title="Reset"><i class="fas fa-undo"></i></a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Expenditures Datatable -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h4 class="card-title text-dark fw-bold mb-0">School Expenditure Log</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover table-bordered align-middle">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th style="width: 50px;">S/N</th>
                                                    <th>Expenditure No</th>
                                                    <th>What was paid for</th>
                                                    <th>Category</th>
                                                    <th>Amount (₦)</th>
                                                    <th>Date Paid</th>
                                                    <th>Vendor / Recipient</th>
                                                    <th>Method</th>
                                                    <th>Status</th>
                                                    <th class="text-center" style="width: 130px;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($expenditures as $index => $exp)
                                                    <tr>
                                                        <td>{{ $expenditures->firstItem() + $index }}</td>
                                                        <td><span class="badge bg-secondary font-monospace">{{ $exp->expenditure_number }}</span></td>
                                                        <td>
                                                            <div class="fw-bold text-dark">{{ $exp->title }}</div>
                                                            @if($exp->receipt_path)
                                                                <small class="text-primary"><i class="fas fa-paperclip me-1"></i> Receipt attached</small>
                                                            @endif
                                                        </td>
                                                        <td><span class="badge bg-light text-dark border">{{ $exp->category }}</span></td>
                                                        <td class="fw-bold text-danger">₦{{ number_format($exp->amount, 2) }}</td>
                                                        <td>{{ $exp->expenditure_date ? $exp->expenditure_date->format('d M, Y') : 'N/A' }}</td>
                                                        <td>{{ $exp->vendor_recipient ?: 'N/A' }}</td>
                                                        <td>{{ ucfirst(str_replace('_', ' ', $exp->payment_method)) }}</td>
                                                        <td>
                                                            @if($exp->status === 'paid')
                                                                <span class="badge bg-success px-3 py-2"><i class="fas fa-check-circle me-1"></i> Paid</span>
                                                            @elseif($exp->status === 'pending')
                                                                <span class="badge bg-warning text-dark px-3 py-2"><i class="fas fa-clock me-1"></i> Pending</span>
                                                            @elseif($exp->status === 'approved')
                                                                <span class="badge bg-info text-white px-3 py-2"><i class="fas fa-thumbs-up me-1"></i> Approved</span>
                                                            @else
                                                                <span class="badge bg-danger px-3 py-2"><i class="fas fa-ban me-1"></i> Cancelled</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="btn-group">
                                                                <a href="{{ route('admin.expenditures.show', $exp->id) }}" class="btn btn-sm btn-info text-white" title="View Details">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="{{ route('admin.expenditures.edit', $exp->id) }}" class="btn btn-sm btn-warning text-white" title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <form action="{{ route('admin.expenditures.destroy', $exp->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this expenditure record?');">
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
                                                            <div class="text-muted mb-2"><i class="fas fa-receipt fa-3x"></i></div>
                                                            <h5>No expenditure records found.</h5>
                                                            <p class="mb-3">Click below to record a new school expenditure.</p>
                                                            <a href="{{ route('admin.expenditures.create') }}" class="btn btn-danger"><i class="fas fa-plus me-1"></i> Record Expenditure</a>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end mt-3">
                                        {{ $expenditures->links() }}
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
