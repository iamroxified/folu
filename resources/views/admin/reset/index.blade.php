<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>System Reset & Data Management - Admin Panel</title>
    @include('admin.partials.links')
    <style>
        .badge-danger-soft {
            background-color: rgba(235, 87, 87, 0.15);
            color: #d9534f;
            border: 1px solid rgba(235, 87, 87, 0.3);
        }
        .badge-warning-soft {
            background-color: rgba(243, 156, 18, 0.15);
            color: #d68910;
            border: 1px solid rgba(243, 156, 18, 0.3);
        }
        .badge-success-soft {
            background-color: rgba(46, 204, 113, 0.15);
            color: #27ae60;
            border: 1px solid rgba(46, 204, 113, 0.3);
        }
        .badge-primary-soft {
            background-color: rgba(52, 152, 219, 0.15);
            color: #2980b9;
            border: 1px solid rgba(52, 152, 219, 0.3);
        }
        .reset-nav-pill.active {
            background: linear-gradient(135deg, #e74c3c, #c0392b) !important;
            color: #fff !important;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(192, 57, 43, 0.3);
        }
        .card-reset-highlight {
            border-top: 4px solid #e74c3c;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        @include('admin.partials.sidebar')

        <div class="main-panel">
            @include('admin.partials.header')
            <div class="container">
                <div class="page-inner">
                    <!-- Page Header -->
                    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
                        <div>
                            <div class="d-flex align-items-center mb-1">
                                <h2 class="text-dark fw-bold mb-0">
                                    <i class="fas fa-undo-alt text-danger me-2"></i>System Reset & Maintenance
                                </h2>
                                <span class="badge bg-danger text-uppercase px-3 py-1 ms-3 fw-bold">Admin Reset Center</span>
                            </div>
                            <p class="text-muted mb-0">
                                Safely delete, reset, or bulk-manage payments, fee allocations, students, classes, and subjects with session, term, and class targeting.
                            </p>
                        </div>
                        <div class="ms-md-auto py-2 py-md-0 d-flex gap-2">
                            <a href="{{ route('admin.reset.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-sync-alt me-1"></i> Refresh Data
                            </a>
                            <a href="{{ url('/admin/fees/bulk_allocation.php') }}" class="btn btn-primary">
                                <i class="fas fa-layer-group me-1"></i> Bulk Allocation Page
                            </a>
                        </div>
                    </div>

                    <!-- Flash Notifications -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Safety Advisory Banner -->
                    <div class="alert alert-warning border-start border-4 border-warning shadow-sm" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shield-alt fa-2x text-warning me-3"></i>
                            <div>
                                <h5 class="alert-heading fw-bold mb-1">Precise Selection & Confirmation Required</h5>
                                <p class="mb-0 text-dark small">
                                    Every database record is presented in the lists below. Filter by <strong>Session</strong>, <strong>Term</strong>, and <strong>Class</strong> to inspect matching entries before performing any deletion or balance reset. Destructive actions require entering the confirmation code (e.g. <code>RESET</code> or <code>DELETE</code>).
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- System Overview Metrics -->
                    <div class="row">
                        <div class="col-6 col-sm-4 col-lg-2">
                            <div class="card card-stats card-round">
                                <div class="card-body p-3 text-center">
                                    <div class="text-muted small text-uppercase fw-bold">Payments</div>
                                    <h4 class="fw-bold text-danger mb-0 mt-1">{{ number_format($overviewStats['total_payments']) }}</h4>
                                    <small class="text-muted">₦{{ number_format($overviewStats['total_payments_amount'], 2) }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-lg-2">
                            <div class="card card-stats card-round">
                                <div class="card-body p-3 text-center">
                                    <div class="text-muted small text-uppercase fw-bold">Allocations</div>
                                    <h4 class="fw-bold text-primary mb-0 mt-1">{{ number_format($overviewStats['total_allocations']) }}</h4>
                                    <small class="text-muted">Fee records</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-lg-2">
                            <div class="card card-stats card-round">
                                <div class="card-body p-3 text-center">
                                    <div class="text-muted small text-uppercase fw-bold">Students</div>
                                    <h4 class="fw-bold text-success mb-0 mt-1">{{ number_format($overviewStats['total_students']) }}</h4>
                                    <small class="text-muted">Enrolled</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-lg-2">
                            <div class="card card-stats card-round">
                                <div class="card-body p-3 text-center">
                                    <div class="text-muted small text-uppercase fw-bold">Classes</div>
                                    <h4 class="fw-bold text-info mb-0 mt-1">{{ number_format($overviewStats['total_classes']) }}</h4>
                                    <small class="text-muted">Class arms</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-lg-2">
                            <div class="card card-stats card-round">
                                <div class="card-body p-3 text-center">
                                    <div class="text-muted small text-uppercase fw-bold">Subjects</div>
                                    <h4 class="fw-bold text-secondary mb-0 mt-1">{{ number_format($overviewStats['total_subjects']) }}</h4>
                                    <small class="text-muted">Curriculum</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-lg-2">
                            <div class="card card-stats card-round bg-light">
                                <div class="card-body p-3 text-center">
                                    <div class="text-muted small text-uppercase fw-bold">Active Scope</div>
                                    <div class="fw-bold text-dark mt-1 text-truncate" title="{{ $activeSession->session_name ?? 'N/A' }}">
                                        {{ $activeSession->session_name ?? 'N/A' }}
                                    </div>
                                    <small class="badge bg-secondary">{{ $activeTerm->term_name ?? 'Term 1' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Pills / Tabs -->
                    <ul class="nav nav-pills nav-secondary mb-3 gap-2" id="resetTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link reset-nav-pill {{ $activeTab === 'payments' ? 'active' : '' }}" href="{{ route('admin.reset.index', ['tab' => 'payments', 'session_id' => $sessionId, 'term_id' => $termId, 'class_id' => $classId]) }}">
                                <i class="fas fa-money-check-alt me-1"></i> Reset Payments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link reset-nav-pill {{ $activeTab === 'allocations' ? 'active' : '' }}" href="{{ route('admin.reset.index', ['tab' => 'allocations', 'session_id' => $sessionId, 'term_id' => $termId, 'class_id' => $classId]) }}">
                                <i class="fas fa-file-invoice-dollar me-1"></i> Reset Allocations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link reset-nav-pill {{ $activeTab === 'students' ? 'active' : '' }}" href="{{ route('admin.reset.index', ['tab' => 'students', 'session_id' => $sessionId, 'term_id' => $termId, 'class_id' => $classId]) }}">
                                <i class="fas fa-user-graduate me-1"></i> Delete Students
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link reset-nav-pill {{ $activeTab === 'classes' ? 'active' : '' }}" href="{{ route('admin.reset.index', ['tab' => 'classes']) }}">
                                <i class="fas fa-school me-1"></i> Delete Classes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link reset-nav-pill {{ $activeTab === 'subjects' ? 'active' : '' }}" href="{{ route('admin.reset.index', ['tab' => 'subjects']) }}">
                                <i class="fas fa-book me-1"></i> Delete Subjects
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link reset-nav-pill {{ $activeTab === 'bulk_allocate' ? 'active' : '' }}" href="{{ route('admin.reset.index', ['tab' => 'bulk_allocate', 'session_id' => $sessionId, 'term_id' => $termId, 'class_id' => $classId]) }}">
                                <i class="fas fa-layer-group me-1"></i> Bulk Fee Allocate
                            </a>
                        </li>
                    </ul>

                    <!-- Universal Scope Filter Bar -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light py-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="fw-bold text-dark small">
                                    <i class="fas fa-filter text-primary me-1"></i> Target Database Scope & Filter
                                </span>
                                <span class="text-muted small">Select Session, Term & Class to isolate database records</span>
                            </div>
                        </div>
                        <div class="card-body py-3">
                            <form method="GET" action="{{ route('admin.reset.index') }}" class="row g-2 align-items-end">
                                <input type="hidden" name="tab" value="{{ $activeTab }}">

                                @if(in_array($activeTab, ['payments', 'allocations', 'students', 'bulk_allocate'], true))
                                    <div class="col-md-3 col-sm-6">
                                        <label class="form-label small fw-bold mb-1">Academic Session</label>
                                        <select name="session_id" class="form-select form-select-sm">
                                            <option value="0">-- All Sessions --</option>
                                            @foreach($sessions as $sess)
                                                <option value="{{ $sess->id }}" {{ $sessionId == $sess->id ? 'selected' : '' }}>
                                                    {{ $sess->session_name }} {{ $sess->is_active ? '(Active)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3 col-sm-6">
                                        <label class="form-label small fw-bold mb-1">Academic Term</label>
                                        <select name="term_id" class="form-select form-select-sm">
                                            <option value="0">-- All Terms --</option>
                                            @foreach($terms as $t)
                                                <option value="{{ $t->id }}" {{ $termId == $t->id ? 'selected' : '' }}>
                                                    {{ $t->term_name }} {{ $t->is_active ? '(Active)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3 col-sm-6">
                                        <label class="form-label small fw-bold mb-1">Class</label>
                                        <select name="class_id" class="form-select form-select-sm">
                                            <option value="0">-- All Classes --</option>
                                            @foreach($classes as $c)
                                                <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>
                                                    {{ $c->class_name }} {{ $c->section ? '(' . $c->section . ')' : '' }} - {{ $c->grade_level }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                @if($activeTab === 'allocations')
                                    <div class="col-md-3 col-sm-6">
                                        <label class="form-label small fw-bold mb-1">Fee Structure</label>
                                        <select name="fee_structure_id" class="form-select form-select-sm">
                                            <option value="0">-- All Fee Structures --</option>
                                            @foreach($feeStructures as $fs)
                                                <option value="{{ $fs->id }}" {{ $feeStructureId == $fs->id ? 'selected' : '' }}>
                                                    {{ $fs->name }} (₦{{ number_format($fs->amount, 2) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-6">
                                        <label class="form-label small fw-bold mb-1">Status</label>
                                        <select name="status" class="form-select form-select-sm">
                                            <option value="">All Statuses</option>
                                            <option value="unpaid" {{ $status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                            <option value="partially_paid" {{ $status === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                                            <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Fully Paid</option>
                                            <option value="overpaid" {{ $status === 'overpaid' ? 'selected' : '' }}>Overpaid</option>
                                        </select>
                                    </div>
                                @endif

                                @if($activeTab === 'students')
                                    <div class="col-md-2 col-sm-6">
                                        <label class="form-label small fw-bold mb-1">Category</label>
                                        <select name="category" class="form-select form-select-sm">
                                            <option value="">All Categories</option>
                                            <option value="NI" {{ $category === 'NI' ? 'selected' : '' }}>New Intake (NI)</option>
                                            <option value="OS" {{ $category === 'OS' ? 'selected' : '' }}>Returning (OS)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-6">
                                        <label class="form-label small fw-bold mb-1">Status</label>
                                        <select name="status" class="form-select form-select-sm">
                                            <option value="">All Statuses</option>
                                            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            <option value="withdrawn" {{ $status === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                                        </select>
                                    </div>
                                @endif

                                @if($activeTab === 'classes')
                                    <div class="col-md-4 col-sm-6">
                                        <label class="form-label small fw-bold mb-1">Grade Level</label>
                                        <select name="grade_level" class="form-select form-select-sm">
                                            <option value="">-- All Grade Levels --</option>
                                            <option value="Nursery" {{ $gradeLevel === 'Nursery' ? 'selected' : '' }}>Nursery</option>
                                            <option value="Primary" {{ $gradeLevel === 'Primary' ? 'selected' : '' }}>Primary</option>
                                            <option value="Junior Secondary" {{ $gradeLevel === 'Junior Secondary' ? 'selected' : '' }}>Junior Secondary (JSS)</option>
                                            <option value="Senior Secondary" {{ $gradeLevel === 'Senior Secondary' ? 'selected' : '' }}>Senior Secondary (SSS)</option>
                                        </select>
                                    </div>
                                @endif

                                @if($activeTab === 'subjects')
                                    <div class="col-md-3 col-sm-6">
                                        <label class="form-label small fw-bold mb-1">Class Level</label>
                                        <input type="text" name="class_level" value="{{ $classLevel }}" class="form-control form-control-sm" placeholder="e.g. JSS, SSS">
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <label class="form-label small fw-bold mb-1">Department</label>
                                        <select name="department" class="form-select form-select-sm">
                                            <option value="">All Departments</option>
                                            <option value="general" {{ $department === 'general' ? 'selected' : '' }}>General</option>
                                            <option value="science" {{ $department === 'science' ? 'selected' : '' }}>Science</option>
                                            <option value="arts" {{ $department === 'arts' ? 'selected' : '' }}>Arts / Humanities</option>
                                            <option value="commercial" {{ $department === 'commercial' ? 'selected' : '' }}>Commercial</option>
                                        </select>
                                    </div>
                                @endif

                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label small fw-bold mb-1">Search Keywords</label>
                                    <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Name, Code, Ref, etc.">
                                </div>

                                <div class="col-md-2 col-sm-6 d-flex gap-2">
                                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold">
                                        <i class="fas fa-search me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('admin.reset.index', ['tab' => $activeTab]) }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- TAB 1: RESET PAYMENTS -->
                    @if($activeTab === 'payments')
                        <div class="card card-reset-highlight shadow-sm">
                            <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
                                <div>
                                    <h4 class="card-title fw-bold text-danger mb-0">
                                        <i class="fas fa-money-check-alt me-2"></i>Reset & Delete Payments
                                    </h4>
                                    <small class="text-muted">
                                        Found <strong>{{ number_format($tabData['count']) }}</strong> payment(s) totaling <strong>₦{{ number_format($tabData['sum'], 2) }}</strong> in this database scope.
                                    </small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-outline-danger btn-sm fw-bold" id="btnResetSelectedPayments" disabled data-bs-toggle="modal" data-bs-target="#modalResetSelectedPayments">
                                        <i class="fas fa-trash-alt me-1"></i> Reset Selected (<span id="countSelectedPayments">0</span>)
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalResetAllPayments">
                                        <i class="fas fa-exclamation-triangle me-1"></i> Reset ALL in Scope ({{ number_format($tabData['count']) }})
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <form id="formPaymentsReset" method="POST" action="{{ route('admin.reset.payments') }}">
                                    @csrf
                                    <input type="hidden" name="scope" value="selected">
                                    <input type="hidden" name="confirm_phrase" id="inputConfirmPhraseSelectedPayments" value="">

                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0 no-datatable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="40" class="text-center">
                                                        <input type="checkbox" class="form-check-input" id="checkAllPayments">
                                                    </th>
                                                    <th>Ref ID / Trx</th>
                                                    <th>Student Details</th>
                                                    <th>Class</th>
                                                    <th>Fee Description</th>
                                                    <th>Amount Paid</th>
                                                    <th>Payment Method</th>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($tabData['items'] as $pmt)
                                                    @php
                                                        $feeContext = null;
                                                        $chargeContext = null;
                                                        if (in_array($pmt->payable_type, ['student_fee', 'App\Models\StudentFee'], true)) {
                                                            $feeContext = $tabData['feeContexts'][$pmt->payable_id] ?? null;
                                                        } elseif (in_array($pmt->payable_type, ['additional_charge', 'App\Models\AdditionalCharge'], true)) {
                                                            $chargeContext = $tabData['chargeContexts'][$pmt->payable_id] ?? null;
                                                        }

                                                        $studentName = $pmt->payer_name;
                                                        $studentAdm = '';
                                                        $studentClass = 'N/A';
                                                        $feeTitle = $pmt->description ?: 'Fee Payment';

                                                        if ($feeContext && $feeContext->student) {
                                                            $studentName = $feeContext->student->first_name . ' ' . $feeContext->student->last_name;
                                                            $studentAdm = $feeContext->student->admission_no;
                                                            $studentClass = $feeContext->student->currentClass ? $feeContext->student->currentClass->class_name . ' ' . $feeContext->student->currentClass->section : 'N/A';
                                                            $feeTitle = $feeContext->feeStructure->name ?? 'Standard Fee';
                                                        } elseif ($chargeContext && $chargeContext->student) {
                                                            $studentName = $chargeContext->student->first_name . ' ' . $chargeContext->student->last_name;
                                                            $studentAdm = $chargeContext->student->admission_no;
                                                            $studentClass = $chargeContext->student->currentClass ? $chargeContext->student->currentClass->class_name . ' ' . $chargeContext->student->currentClass->section : 'N/A';
                                                            $feeTitle = $chargeContext->fee_name . ' (Additional Charge)';
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td class="text-center">
                                                            <input type="checkbox" name="payment_ids[]" value="{{ $pmt->id }}" class="form-check-input payment-checkbox">
                                                        </td>
                                                        <td>
                                                            <span class="fw-bold text-dark font-monospace">{{ $pmt->payment_reference }}</span>
                                                            @if($pmt->transaction_id)
                                                                <br><small class="text-muted font-monospace">{{ $pmt->transaction_id }}</small>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="fw-bold text-dark">{{ $studentName }}</div>
                                                            @if($studentAdm)
                                                                <small class="badge bg-light text-muted border">{{ $studentAdm }}</small>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary">{{ $studentClass }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-dark">{{ $feeTitle }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="fw-bold text-success">₦{{ number_format($pmt->amount, 2) }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-light text-dark border text-capitalize">{{ str_replace('_', ' ', $pmt->payment_method) }}</span>
                                                        </td>
                                                        <td>
                                                            <small class="text-muted">{{ $pmt->payment_date ? \Carbon\Carbon::parse($pmt->payment_date)->format('M d, Y') : $pmt->created_at->format('M d, Y') }}</small>
                                                        </td>
                                                        <td>
                                                            <span class="badge {{ $pmt->status === 'completed' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                                {{ ucfirst($pmt->status) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="9" class="text-center py-5">
                                                            <i class="fas fa-receipt fa-3x text-muted mb-2 d-block"></i>
                                                            <h5 class="text-dark fw-bold">No Payments Found</h5>
                                                            <p class="text-muted small">No payment transactions match the selected session, term, or class filters.</p>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                            </div>
                            @if($tabData['items']->hasPages())
                                <div class="card-footer bg-white d-flex justify-content-end py-2">
                                    {{ $tabData['items']->links() }}
                                </div>
                            @endif
                        </div>

                        <!-- Modal: Reset Selected Payments -->
                        <div class="modal fade" id="modalResetSelectedPayments" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Reset Selected Payments</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>You have selected <strong id="modalSelectedPaymentsCount">0</strong> payment record(s) to reset.</p>
                                        <div class="alert alert-warning small">
                                            <strong>What this does:</strong>
                                            <ul class="mb-0 ps-3 mt-1">
                                                <li>Deletes the payment records and associated receipts.</li>
                                                <li>Automatically recalculates student fee balances, setting their status back to <em>unpaid</em> or <em>partially paid</em>.</li>
                                                <li>Updates financial audit logs.</li>
                                            </ul>
                                        </div>
                                        <label class="form-label fw-bold small text-danger">Type "RESET" below to confirm:</label>
                                        <input type="text" id="inputResetSelectedConfirm" class="form-control text-uppercase" placeholder="RESET">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-danger fw-bold" id="btnSubmitSelectedPayments" disabled>
                                            Confirm & Reset Payments
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal: Reset ALL Filtered Payments -->
                        <div class="modal fade" id="modalResetAllPayments" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.reset.payments') }}">
                                        @csrf
                                        <input type="hidden" name="scope" value="all_filtered">
                                        <input type="hidden" name="session_id" value="{{ $sessionId }}">
                                        <input type="hidden" name="term_id" value="{{ $termId }}">
                                        <input type="hidden" name="class_id" value="{{ $classId }}">

                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title fw-bold"><i class="fas fa-trash-alt me-2"></i>Reset ALL Payments in Scope</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="alert alert-danger">
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                <strong>WARNING: High-impact action!</strong> You are about to purge and reset <strong>ALL {{ number_format($tabData['count']) }}</strong> payments in the current scope.
                                            </div>
                                            <p class="small text-muted">
                                                Scope: <strong>Session:</strong> {{ $sessionId ? $sessions->firstWhere('id', $sessionId)->session_name ?? 'Any' : 'All Sessions' }},
                                                <strong>Term:</strong> {{ $termId ? $terms->firstWhere('id', $termId)->term_name ?? 'Any' : 'All Terms' }},
                                                <strong>Class:</strong> {{ $classId ? $classes->firstWhere('id', $classId)->class_name ?? 'Any' : 'All Classes' }}.
                                            </p>
                                            <label class="form-label fw-bold small text-danger">Type "RESET" to confirm this full purge:</label>
                                            <input type="text" name="confirm_phrase" id="inputResetAllConfirm" class="form-control text-uppercase" placeholder="RESET" required>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger fw-bold" id="btnSubmitAllPayments" disabled>
                                                Execute Full Payment Reset
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- TAB 2: RESET ALLOCATIONS -->
                    @if($activeTab === 'allocations')
                        <div class="card card-reset-highlight shadow-sm">
                            <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
                                <div>
                                    <h4 class="card-title fw-bold text-danger mb-0">
                                        <i class="fas fa-file-invoice-dollar me-2"></i>Reset & Delete Fee Allocations
                                    </h4>
                                    <small class="text-muted">
                                        Found <strong>{{ number_format($tabData['count']) }}</strong> fee allocation(s) in database (Total: ₦{{ number_format($tabData['total_payable'], 2) }}, Paid: ₦{{ number_format($tabData['total_paid'], 2) }}).
                                    </small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-outline-danger btn-sm fw-bold" id="btnResetSelectedAllocations" disabled data-bs-toggle="modal" data-bs-target="#modalResetSelectedAllocations">
                                        <i class="fas fa-trash-alt me-1"></i> Process Selected (<span id="countSelectedAllocations">0</span>)
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalResetAllAllocations">
                                        <i class="fas fa-bolt me-1"></i> Process ALL in Scope ({{ number_format($tabData['count']) }})
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <form id="formAllocationsReset" method="POST" action="{{ route('admin.reset.allocations') }}">
                                    @csrf
                                    <input type="hidden" name="scope" value="selected">
                                    <input type="hidden" name="mode" id="inputAllocModeSelected" value="deallocate_safe">
                                    <input type="hidden" name="confirm_phrase" id="inputConfirmPhraseSelectedAllocations" value="">

                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0 no-datatable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="40" class="text-center">
                                                        <input type="checkbox" class="form-check-input" id="checkAllAllocations">
                                                    </th>
                                                    <th>Student</th>
                                                    <th>Class</th>
                                                    <th>Fee Structure</th>
                                                    <th>Session / Term</th>
                                                    <th>Base Amount</th>
                                                    <th>Paid</th>
                                                    <th>Balance</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($tabData['items'] as $alloc)
                                                    @php
                                                        $s = $alloc->student;
                                                    @endphp
                                                    <tr>
                                                        <td class="text-center">
                                                            <input type="checkbox" name="allocation_ids[]" value="{{ $alloc->id }}" class="form-check-input allocation-checkbox" data-has-payments="{{ (float)$alloc->amount_paid > 0 ? '1' : '0' }}">
                                                        </td>
                                                        <td>
                                                            @if($s)
                                                                <div class="fw-bold text-dark">{{ $s->first_name }} {{ $s->last_name }}</div>
                                                                <small class="badge bg-light text-muted border">{{ $s->admission_no }}</small>
                                                            @else
                                                                <span class="text-muted">Unlinked (ID: {{ $alloc->student_id }})</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary">{{ $s && $s->currentClass ? $s->currentClass->class_name . ' ' . $s->currentClass->section : 'N/A' }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="fw-bold text-dark">{{ $alloc->feeStructure->name ?? 'Standard Fee' }}</span>
                                                        </td>
                                                        <td>
                                                            <small class="text-muted">
                                                                {{ $alloc->session->session_name ?? 'N/A' }}
                                                                @if($alloc->term)
                                                                    <br><span class="badge bg-light text-dark border">{{ $alloc->term->term_name }}</span>
                                                                @endif
                                                            </small>
                                                        </td>
                                                        <td>
                                                            ₦{{ number_format($alloc->total_payable, 2) }}
                                                        </td>
                                                        <td>
                                                            <span class="fw-bold {{ (float)$alloc->amount_paid > 0 ? 'text-success' : 'text-muted' }}">
                                                                ₦{{ number_format($alloc->amount_paid, 2) }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="fw-bold {{ (float)$alloc->balance > 0 ? 'text-danger' : 'text-muted' }}">
                                                                ₦{{ number_format($alloc->balance, 2) }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge {{ $alloc->status === 'paid' ? 'bg-success' : ($alloc->status === 'partially_paid' ? 'bg-info' : 'bg-warning text-dark') }}">
                                                                {{ ucfirst(str_replace('_', ' ', $alloc->status)) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="9" class="text-center py-5">
                                                            <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-2 d-block"></i>
                                                            <h5 class="text-dark fw-bold">No Fee Allocations Found</h5>
                                                            <p class="text-muted small">No allocations match the selected criteria.</p>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                            </div>
                            @if($tabData['items']->hasPages())
                                <div class="card-footer bg-white d-flex justify-content-end py-2">
                                    {{ $tabData['items']->links() }}
                                </div>
                            @endif
                        </div>

                        <!-- Modal: Process Selected Allocations -->
                        <div class="modal fade" id="modalResetSelectedAllocations" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title fw-bold"><i class="fas fa-tasks me-2"></i>Action for Selected Allocations</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Targeting <strong id="modalSelectedAllocationsCount">0</strong> selected allocation(s).</p>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Choose Operation Mode:</label>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="modal_alloc_mode" id="allocModeSafe" value="deallocate_safe" checked>
                                                <label class="form-check-label" for="allocModeSafe">
                                                    <strong>Safe De-allocation</strong>
                                                    <div class="small text-muted">Deallocates only records that have ZERO recorded payments. Protected records are skipped.</div>
                                                </label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="modal_alloc_mode" id="allocModeForce" value="deallocate_force">
                                                <label class="form-check-label text-danger" for="allocModeForce">
                                                    <strong>Force Reset & Delete</strong>
                                                    <div class="small text-muted">Deletes attached payments and receipts first, then deletes the fee allocation completely.</div>
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="modal_alloc_mode" id="allocModeResetBalance" value="reset_balances">
                                                <label class="form-check-label" for="allocModeResetBalance">
                                                    <strong>Reset Balances Only (Keep Allocation)</strong>
                                                    <div class="small text-muted">Deletes payments and sets Amount Paid = ₦0.00, restoring full balance to unpaid.</div>
                                                </label>
                                            </div>
                                        </div>
                                        <label class="form-label fw-bold small text-danger">Type "RESET" below to confirm:</label>
                                        <input type="text" id="inputResetSelectedAllocConfirm" class="form-control text-uppercase" placeholder="RESET">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-danger fw-bold" id="btnSubmitSelectedAllocations" disabled>
                                            Execute Operation
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal: Process ALL Filtered Allocations -->
                        <div class="modal fade" id="modalResetAllAllocations" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.reset.allocations') }}">
                                        @csrf
                                        <input type="hidden" name="scope" value="all_filtered">
                                        <input type="hidden" name="session_id" value="{{ $sessionId }}">
                                        <input type="hidden" name="term_id" value="{{ $termId }}">
                                        <input type="hidden" name="class_id" value="{{ $classId }}">
                                        <input type="hidden" name="fee_structure_id" value="{{ $feeStructureId }}">

                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Reset ALL Allocations in Scope</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>This will affect <strong>ALL {{ number_format($tabData['count']) }}</strong> fee allocations matching the current filter.</p>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">Choose Operation Mode:</label>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="radio" name="mode" id="allocModeAllSafe" value="deallocate_safe" checked>
                                                    <label class="form-check-label" for="allocModeAllSafe">
                                                        <strong>Safe De-allocation</strong> (Skip any with payments)
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="radio" name="mode" id="allocModeAllForce" value="deallocate_force">
                                                    <label class="form-check-label text-danger" for="allocModeAllForce">
                                                        <strong>Force Reset & Delete</strong> (Purge payments and remove fee)
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="mode" id="allocModeAllResetBalance" value="reset_balances">
                                                    <label class="form-check-label" for="allocModeAllResetBalance">
                                                        <strong>Reset Balances Only</strong> (Zero paid amount, restore unpaid)
                                                    </label>
                                                </div>
                                            </div>
                                            <label class="form-label fw-bold small text-danger">Type "RESET" to confirm:</label>
                                            <input type="text" name="confirm_phrase" id="inputResetAllAllocConfirm" class="form-control text-uppercase" placeholder="RESET" required>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger fw-bold" id="btnSubmitAllAllocations" disabled>
                                                Execute Full Reset
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- TAB 3: DELETE STUDENTS -->
                    @if($activeTab === 'students')
                        <div class="card card-reset-highlight shadow-sm">
                            <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
                                <div>
                                    <h4 class="card-title fw-bold text-danger mb-0">
                                        <i class="fas fa-user-graduate me-2"></i>Delete / Deactivate Students
                                    </h4>
                                    <small class="text-muted">
                                        Found <strong>{{ number_format($tabData['count']) }}</strong> student(s) in this database filter (Active: {{ $tabData['active_count'] }}, Inactive: {{ $tabData['inactive_count'] }}).
                                    </small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-outline-danger btn-sm fw-bold" id="btnDeleteSelectedStudents" disabled data-bs-toggle="modal" data-bs-target="#modalDeleteSelectedStudents">
                                        <i class="fas fa-trash-alt me-1"></i> Delete Selected (<span id="countSelectedStudents">0</span>)
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalDeleteAllStudents">
                                        <i class="fas fa-user-slash me-1"></i> Delete ALL in Scope ({{ number_format($tabData['count']) }})
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <form id="formStudentsDelete" method="POST" action="{{ route('admin.reset.students') }}">
                                    @csrf
                                    <input type="hidden" name="scope" value="selected">
                                    <input type="hidden" name="mode" id="inputStudentModeSelected" value="permanent">
                                    <input type="hidden" name="confirm_phrase" id="inputConfirmPhraseSelectedStudents" value="">

                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0 no-datatable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="40" class="text-center">
                                                        <input type="checkbox" class="form-check-input" id="checkAllStudents">
                                                    </th>
                                                    <th>Admission No</th>
                                                    <th>Student Name</th>
                                                    <th>Class</th>
                                                    <th>Category</th>
                                                    <th>Gender</th>
                                                    <th>Session / Term</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($tabData['items'] as $student)
                                                    <tr>
                                                        <td class="text-center">
                                                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="form-check-input student-checkbox">
                                                        </td>
                                                        <td>
                                                            <span class="font-monospace fw-bold text-dark">{{ $student->admission_no }}</span>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar avatar-sm me-2 bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold">
                                                                    {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                                                </div>
                                                                <div>
                                                                    <div class="fw-bold text-dark">{{ $student->first_name }} {{ $student->last_name }}</div>
                                                                    <small class="text-muted">{{ $student->email ?: 'No email' }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary">
                                                                {{ $student->currentClass ? $student->currentClass->class_name . ' ' . $student->currentClass->section : 'Unassigned' }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge {{ $student->category === 'NI' ? 'bg-info' : 'bg-primary' }}">
                                                                {{ $student->category === 'NI' ? 'New Intake' : 'Returning' }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="text-capitalize text-muted">{{ $student->gender }}</span>
                                                        </td>
                                                        <td>
                                                            <small class="text-muted">
                                                                {{ $student->currentSession->session_name ?? 'N/A' }}
                                                                @if($student->currentTerm)
                                                                    <br><span class="badge bg-light text-dark border">{{ $student->currentTerm->term_name }}</span>
                                                                @endif
                                                            </small>
                                                        </td>
                                                        <td>
                                                            <span class="badge {{ $student->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                                                {{ ucfirst($student->status) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="text-center py-5">
                                                            <i class="fas fa-user-graduate fa-3x text-muted mb-2 d-block"></i>
                                                            <h5 class="text-dark fw-bold">No Students Found</h5>
                                                            <p class="text-muted small">No student records match the selected class, session, or term.</p>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                            </div>
                            @if($tabData['items']->hasPages())
                                <div class="card-footer bg-white d-flex justify-content-end py-2">
                                    {{ $tabData['items']->links() }}
                                </div>
                            @endif
                        </div>

                        <!-- Modal: Delete Selected Students -->
                        <div class="modal fade" id="modalDeleteSelectedStudents" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title fw-bold"><i class="fas fa-user-times me-2"></i>Confirm Delete Selected Students</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>You have selected <strong id="modalSelectedStudentsCount">0</strong> student(s) to process.</p>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Deletion Method:</label>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="modal_student_mode" id="studentModePerm" value="permanent" checked>
                                                <label class="form-check-label text-danger" for="studentModePerm">
                                                    <strong>Permanent Delete (Cascading)</strong>
                                                    <div class="small text-muted">Purges all attendances, grades, fee allocations, payments, subjects, enrollments, user accounts, and the student profile.</div>
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="modal_student_mode" id="studentModeSoft" value="soft">
                                                <label class="form-check-label" for="studentModeSoft">
                                                    <strong>Soft Delete / Inactivate</strong>
                                                    <div class="small text-muted">Marks status as inactive and withdrawn without removing historical grades or fees.</div>
                                                </label>
                                            </div>
                                        </div>
                                        <label class="form-label fw-bold small text-danger">Type "DELETE" below to confirm:</label>
                                        <input type="text" id="inputDeleteSelectedStudentConfirm" class="form-control text-uppercase" placeholder="DELETE">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-danger fw-bold" id="btnSubmitSelectedStudents" disabled>
                                            Confirm & Delete Students
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal: Delete ALL Filtered Students -->
                        <div class="modal fade" id="modalDeleteAllStudents" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.reset.students') }}">
                                        @csrf
                                        <input type="hidden" name="scope" value="all_filtered">
                                        <input type="hidden" name="session_id" value="{{ $sessionId }}">
                                        <input type="hidden" name="term_id" value="{{ $termId }}">
                                        <input type="hidden" name="class_id" value="{{ $classId }}">

                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Delete ALL Students in Scope</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="alert alert-danger">
                                                <strong>CRITICAL ACTION:</strong> This will delete <strong>ALL {{ number_format($tabData['count']) }}</strong> student(s) in the selected scope:
                                                <div class="mt-1 small">
                                                    <strong>Class:</strong> {{ $classId ? $classes->firstWhere('id', $classId)->class_name ?? 'Any' : 'All Classes' }}<br>
                                                    <strong>Session:</strong> {{ $sessionId ? $sessions->firstWhere('id', $sessionId)->session_name ?? 'Any' : 'All Sessions' }}
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">Choose Mode:</label>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="radio" name="mode" id="studentModeAllPerm" value="permanent" checked>
                                                    <label class="form-check-label text-danger" for="studentModeAllPerm">
                                                        <strong>Permanent Delete (Cascading)</strong>
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="mode" id="studentModeAllSoft" value="soft">
                                                    <label class="form-check-label" for="studentModeAllSoft">
                                                        <strong>Soft Delete / Inactivate</strong>
                                                    </label>
                                                </div>
                                            </div>
                                            <label class="form-label fw-bold small text-danger">Type "DELETE" to confirm:</label>
                                            <input type="text" name="confirm_phrase" id="inputDeleteAllStudentConfirm" class="form-control text-uppercase" placeholder="DELETE" required>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger fw-bold" id="btnSubmitAllStudents" disabled>
                                                Execute Full Student Deletion
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- TAB 4: DELETE CLASSES -->
                    @if($activeTab === 'classes')
                        <div class="card card-reset-highlight shadow-sm">
                            <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
                                <div>
                                    <h4 class="card-title fw-bold text-danger mb-0">
                                        <i class="fas fa-school me-2"></i>Delete Classes
                                    </h4>
                                    <small class="text-muted">
                                        Found <strong>{{ $tabData['count'] }}</strong> classes ({{ $tabData['students_count'] }} total enrolled students).
                                    </small>
                                </div>
                                <button type="button" class="btn btn-danger btn-sm fw-bold" id="btnDeleteSelectedClasses" disabled data-bs-toggle="modal" data-bs-target="#modalDeleteClasses">
                                    <i class="fas fa-trash-alt me-1"></i> Delete Selected Classes (<span id="countSelectedClasses">0</span>)
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <form id="formClassesDelete" method="POST" action="{{ route('admin.reset.classes') }}">
                                    @csrf
                                    <input type="hidden" name="unassign_students" id="inputUnassignStudents" value="0">
                                    <input type="hidden" name="confirm_phrase" id="inputConfirmPhraseClasses" value="">

                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0 no-datatable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="40" class="text-center">
                                                        <input type="checkbox" class="form-check-input" id="checkAllClasses">
                                                    </th>
                                                    <th>Class Name</th>
                                                    <th>Section / Arm</th>
                                                    <th>Grade Level</th>
                                                    <th>Class Teacher</th>
                                                    <th>Enrolled Students</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($tabData['items'] as $class)
                                                    <tr>
                                                        <td class="text-center">
                                                            <input type="checkbox" name="class_ids[]" value="{{ $class->id }}" class="form-check-input class-checkbox" data-students="{{ $class->students_count }}">
                                                        </td>
                                                        <td>
                                                            <span class="fw-bold text-dark">{{ $class->class_name }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-light text-dark border">{{ $class->section ?: 'Default' }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary">{{ $class->grade_level }}</span>
                                                        </td>
                                                        <td>
                                                            <small class="text-muted">{{ $class->teacher ? $class->teacher->name : 'Not Assigned' }}</small>
                                                        </td>
                                                        <td>
                                                            @if($class->students_count > 0)
                                                                <span class="badge bg-warning text-dark">
                                                                    <i class="fas fa-users me-1"></i> {{ $class->students_count }} Students Enrolled
                                                                </span>
                                                            @else
                                                                <span class="badge bg-light text-muted border">0 Students (Empty)</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center py-5">
                                                            <i class="fas fa-school fa-3x text-muted mb-2 d-block"></i>
                                                            <h5 class="text-dark fw-bold">No Classes Found</h5>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Modal: Delete Classes Confirmation -->
                        <div class="modal fade" id="modalDeleteClasses" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title fw-bold"><i class="fas fa-trash-alt me-2"></i>Confirm Delete Classes</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>You have selected <strong id="modalSelectedClassesCount">0</strong> class(es) to delete.</p>
                                        <div id="alertClassesHaveStudents" class="alert alert-warning small d-none">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            <strong>Warning:</strong> One or more selected classes currently have students enrolled!
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="checkUnassignStudentsModal" checked>
                                            <label class="form-check-label fw-bold" for="checkUnassignStudentsModal">
                                                Unassign Enrolled Students
                                                <div class="small text-muted fw-normal">Sets student class to Unassigned so their profile, fees, and results are safely preserved.</div>
                                            </label>
                                        </div>
                                        <label class="form-label fw-bold small text-danger">Type "DELETE" below to confirm:</label>
                                        <input type="text" id="inputDeleteClassesConfirm" class="form-control text-uppercase" placeholder="DELETE">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-danger fw-bold" id="btnSubmitDeleteClasses" disabled>
                                            Confirm & Delete Classes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- TAB 5: DELETE SUBJECTS -->
                    @if($activeTab === 'subjects')
                        <div class="card card-reset-highlight shadow-sm">
                            <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
                                <div>
                                    <h4 class="card-title fw-bold text-danger mb-0">
                                        <i class="fas fa-book me-2"></i>Delete Subjects
                                    </h4>
                                    <small class="text-muted">
                                        Found <strong>{{ $tabData['count'] }}</strong> subjects currently configured.
                                    </small>
                                </div>
                                <button type="button" class="btn btn-danger btn-sm fw-bold" id="btnDeleteSelectedSubjects" disabled data-bs-toggle="modal" data-bs-target="#modalDeleteSubjects">
                                    <i class="fas fa-trash-alt me-1"></i> Delete Selected Subjects (<span id="countSelectedSubjects">0</span>)
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <form id="formSubjectsDelete" method="POST" action="{{ route('admin.reset.subjects') }}">
                                    @csrf
                                    <input type="hidden" name="confirm_phrase" id="inputConfirmPhraseSubjects" value="">

                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0 no-datatable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="40" class="text-center">
                                                        <input type="checkbox" class="form-check-input" id="checkAllSubjects">
                                                    </th>
                                                    <th>Code</th>
                                                    <th>Subject Name</th>
                                                    <th>Class Level</th>
                                                    <th>Department</th>
                                                    <th>Subject Type</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($tabData['items'] as $sub)
                                                    <tr>
                                                        <td class="text-center">
                                                            <input type="checkbox" name="subject_ids[]" value="{{ $sub->id }}" class="form-check-input subject-checkbox">
                                                        </td>
                                                        <td>
                                                            <span class="font-monospace fw-bold text-dark">{{ $sub->subject_code ?: 'N/A' }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="fw-bold text-dark">{{ $sub->subject_name }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary">{{ $sub->class_level ?: 'All Levels' }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-light text-dark border text-capitalize">{{ $sub->department ?: 'General' }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-muted small">{{ $sub->subject_type ?: 'Standard' }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge {{ $sub->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                                {{ $sub->is_active ? 'Active' : 'Inactive' }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center py-5">
                                                            <i class="fas fa-book fa-3x text-muted mb-2 d-block"></i>
                                                            <h5 class="text-dark fw-bold">No Subjects Found</h5>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Modal: Delete Subjects Confirmation -->
                        <div class="modal fade" id="modalDeleteSubjects" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title fw-bold"><i class="fas fa-trash-alt me-2"></i>Confirm Delete Subjects</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>You have selected <strong id="modalSelectedSubjectsCount">0</strong> subject(s) to permanently delete.</p>
                                        <div class="alert alert-warning small">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            This will automatically remove class-subject assignments, teacher assignments, and student enrolled subjects for the selected entries.
                                        </div>
                                        <label class="form-label fw-bold small text-danger">Type "DELETE" below to confirm:</label>
                                        <input type="text" id="inputDeleteSubjectsConfirm" class="form-control text-uppercase" placeholder="DELETE">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-danger fw-bold" id="btnSubmitDeleteSubjects" disabled>
                                            Confirm & Delete Subjects
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- TAB 6: BULK ALLOCATE -->
                    @if($activeTab === 'bulk_allocate')
                        <div class="card card-reset-highlight shadow-sm">
                            <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
                                <div>
                                    <h4 class="card-title fw-bold text-primary mb-0">
                                        <i class="fas fa-layer-group me-2"></i>Bulk Fee Allocation
                                    </h4>
                                    <small class="text-muted">
                                        Target: <strong>{{ number_format($tabData['count']) }}</strong> eligible student(s) |
                                        <span class="text-success fw-bold">{{ $tabData['allocated_count'] }} Already Allocated</span> |
                                        <span class="text-warning fw-bold">{{ $tabData['pending_count'] }} Pending Allocation</span>
                                    </small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-success btn-sm fw-bold" id="btnRunBulkAllocation" data-bs-toggle="modal" data-bs-target="#modalRunBulkAllocation">
                                        <i class="fas fa-play me-1"></i> Allocate Fees to Students
                                    </button>
                                    <a href="{{ url('/admin/fees/bulk_allocation.php') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-external-link-alt me-1"></i> Detailed Allocation Tool
                                    </a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <form id="formBulkAllocate" method="POST" action="{{ route('admin.reset.bulk_allocate') }}">
                                    @csrf
                                    <input type="hidden" name="session_id" value="{{ $sessionId > 0 ? $sessionId : ($tabData['target_session_id'] ?? ($sessions->first()->id ?? 1)) }}">
                                    <input type="hidden" name="term_id" value="{{ $termId > 0 ? $termId : ($tabData['target_term_id'] ?? ($terms->first()->id ?? 1)) }}">
                                    <input type="hidden" name="class_id" value="{{ $classId }}">
                                    <input type="hidden" name="fee_structure_id" id="inputBulkFeeStructureId" value="0">

                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0 no-datatable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="40" class="text-center">
                                                        <input type="checkbox" class="form-check-input" id="checkAllBulkStudents" checked>
                                                    </th>
                                                    <th>Admission No</th>
                                                    <th>Student Name</th>
                                                    <th>Class</th>
                                                    <th>Category</th>
                                                    <th>Gender</th>
                                                    <th>Allocation Status for Target Session/Term</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($tabData['items'] as $student)
                                                    @php
                                                        $isAllocated = $student->studentFees->isNotEmpty();
                                                    @endphp
                                                    <tr>
                                                        <td class="text-center">
                                                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="form-check-input bulk-student-checkbox" checked>
                                                        </td>
                                                        <td>
                                                            <span class="font-monospace fw-bold text-dark">{{ $student->admission_no }}</span>
                                                        </td>
                                                        <td>
                                                            <div class="fw-bold text-dark">{{ $student->first_name }} {{ $student->last_name }}</div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary">
                                                                {{ $student->currentClass ? $student->currentClass->class_name . ' ' . $student->currentClass->section : 'Unassigned' }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge {{ $student->category === 'NI' ? 'bg-info' : 'bg-primary' }}">
                                                                {{ $student->category === 'NI' ? 'New Intake' : 'Returning' }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="text-capitalize text-muted">{{ $student->gender }}</span>
                                                        </td>
                                                        <td>
                                                            @if($isAllocated)
                                                                @php $alloc = $student->studentFees->first(); @endphp
                                                                <span class="badge bg-success-soft">
                                                                    <i class="fas fa-check-circle me-1"></i> Allocated (₦{{ number_format($alloc->total_payable, 2) }})
                                                                </span>
                                                            @else
                                                                <span class="badge bg-warning-soft">
                                                                    <i class="fas fa-clock me-1"></i> Pending Allocation
                                                                </span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center py-5">
                                                            <i class="fas fa-users-slash fa-3x text-muted mb-2 d-block"></i>
                                                            <h5 class="text-dark fw-bold">No Students Available</h5>
                                                            <p class="text-muted small">No active students match the selected class or criteria.</p>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Modal: Run Bulk Allocation -->
                        <div class="modal fade" id="modalRunBulkAllocation" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title fw-bold"><i class="fas fa-layer-group me-2"></i>Execute Bulk Fee Allocation</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>You are about to bulk allocate fees for the selected students in:</p>
                                        <div class="alert alert-light border small">
                                            <div><strong>Academic Session:</strong> {{ $sessions->firstWhere('id', $sessionId)->session_name ?? 'Default Active' }}</div>
                                            <div><strong>Academic Term:</strong> {{ $terms->firstWhere('id', $termId)->term_name ?? 'Term 1' }}</div>
                                            <div><strong>Target Students:</strong> <span id="modalBulkStudentCount">{{ $tabData['count'] }}</span> selected</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Specific Fee Structure (Optional):</label>
                                            <select id="modalSelectFeeStructure" class="form-select form-select-sm">
                                                <option value="0">Auto-Detect by Student Category & Class (Recommended)</option>
                                                @foreach($feeStructures as $fs)
                                                    <option value="{{ $fs->id }}">{{ $fs->name }} (₦{{ number_format($fs->amount, 2) }})</option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">Leaving on auto-detect automatically applies New Intake or Returning student fees appropriate for each class.</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-success fw-bold" id="btnConfirmBulkAllocation">
                                            <i class="fas fa-play me-1"></i> Start Allocation
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
            @include('admin.partials.footer')
        </div>
    </div>

    <!-- Client-side script for Checkbox management & Safety confirmation logic -->
    <script>
        $(document).ready(function() {
            // Check All Toggle helper
            function setupCheckAll(masterId, itemClass, countBadgeId, actionBtnId) {
                $('#' + masterId).on('change', function() {
                    var isChecked = $(this).is(':checked');
                    $('.' + itemClass).prop('checked', isChecked);
                    updateCount();
                });

                $(document).on('change', '.' + itemClass, function() {
                    var total = $('.' + itemClass).length;
                    var checked = $('.' + itemClass + ':checked').length;
                    $('#' + masterId).prop('checked', total > 0 && total === checked);
                    updateCount();
                });

                function updateCount() {
                    var checked = $('.' + itemClass + ':checked').length;
                    if ($('#' + countBadgeId).length) {
                        $('#' + countBadgeId).text(checked);
                    }
                    if ($('#' + actionBtnId).length) {
                        $('#' + actionBtnId).prop('disabled', checked === 0);
                    }
                }
                updateCount();
            }

            // 1. Payments Tab
            setupCheckAll('checkAllPayments', 'payment-checkbox', 'countSelectedPayments', 'btnResetSelectedPayments');
            $('#btnResetSelectedPayments').on('click', function() {
                var checked = $('.payment-checkbox:checked').length;
                $('#modalSelectedPaymentsCount').text(checked);
                $('#inputResetSelectedConfirm').val('');
                $('#btnSubmitSelectedPayments').prop('disabled', true);
            });
            $('#inputResetSelectedConfirm').on('input', function() {
                $('#btnSubmitSelectedPayments').prop('disabled', $(this).val().trim().toUpperCase() !== 'RESET');
            });
            $('#btnSubmitSelectedPayments').on('click', function() {
                $('#inputConfirmPhraseSelectedPayments').val('RESET');
                $('#formPaymentsReset').submit();
            });
            $('#inputResetAllConfirm').on('input', function() {
                $('#btnSubmitAllPayments').prop('disabled', $(this).val().trim().toUpperCase() !== 'RESET');
            });

            // 2. Allocations Tab
            setupCheckAll('checkAllAllocations', 'allocation-checkbox', 'countSelectedAllocations', 'btnResetSelectedAllocations');
            $('#btnResetSelectedAllocations').on('click', function() {
                var checked = $('.allocation-checkbox:checked').length;
                $('#modalSelectedAllocationsCount').text(checked);
                $('#inputResetSelectedAllocConfirm').val('');
                $('#btnSubmitSelectedAllocations').prop('disabled', true);
            });
            $('#inputResetSelectedAllocConfirm').on('input', function() {
                var val = $(this).val().trim().toUpperCase();
                $('#btnSubmitSelectedAllocations').prop('disabled', val !== 'RESET' && val !== 'DELETE');
            });
            $('#btnSubmitSelectedAllocations').on('click', function() {
                var mode = $('input[name="modal_alloc_mode"]:checked').val();
                $('#inputAllocModeSelected').val(mode);
                $('#inputConfirmPhraseSelectedAllocations').val('RESET');
                $('#formAllocationsReset').submit();
            });
            $('#inputResetAllAllocConfirm').on('input', function() {
                var val = $(this).val().trim().toUpperCase();
                $('#btnSubmitAllAllocations').prop('disabled', val !== 'RESET' && val !== 'DELETE');
            });

            // 3. Students Tab
            setupCheckAll('checkAllStudents', 'student-checkbox', 'countSelectedStudents', 'btnDeleteSelectedStudents');
            $('#btnDeleteSelectedStudents').on('click', function() {
                var checked = $('.student-checkbox:checked').length;
                $('#modalSelectedStudentsCount').text(checked);
                $('#inputDeleteSelectedStudentConfirm').val('');
                $('#btnSubmitSelectedStudents').prop('disabled', true);
            });
            $('#inputDeleteSelectedStudentConfirm').on('input', function() {
                $('#btnSubmitSelectedStudents').prop('disabled', $(this).val().trim().toUpperCase() !== 'DELETE');
            });
            $('#btnSubmitSelectedStudents').on('click', function() {
                var mode = $('input[name="modal_student_mode"]:checked').val();
                $('#inputStudentModeSelected').val(mode);
                $('#inputConfirmPhraseSelectedStudents').val('DELETE');
                $('#formStudentsDelete').submit();
            });
            $('#inputDeleteAllStudentConfirm').on('input', function() {
                $('#btnSubmitAllStudents').prop('disabled', $(this).val().trim().toUpperCase() !== 'DELETE');
            });

            // 4. Classes Tab
            setupCheckAll('checkAllClasses', 'class-checkbox', 'countSelectedClasses', 'btnDeleteSelectedClasses');
            $('#btnDeleteSelectedClasses').on('click', function() {
                var checked = $('.class-checkbox:checked').length;
                $('#modalSelectedClassesCount').text(checked);
                $('#inputDeleteClassesConfirm').val('');
                $('#btnSubmitDeleteClasses').prop('disabled', true);

                var hasStudents = false;
                $('.class-checkbox:checked').each(function() {
                    if (parseInt($(this).data('students'), 10) > 0) {
                        hasStudents = true;
                    }
                });
                if (hasStudents) {
                    $('#alertClassesHaveStudents').removeClass('d-none');
                } else {
                    $('#alertClassesHaveStudents').addClass('d-none');
                }
            });
            $('#inputDeleteClassesConfirm').on('input', function() {
                $('#btnSubmitDeleteClasses').prop('disabled', $(this).val().trim().toUpperCase() !== 'DELETE');
            });
            $('#btnSubmitDeleteClasses').on('click', function() {
                var unassign = $('#checkUnassignStudentsModal').is(':checked') ? '1' : '0';
                $('#inputUnassignStudents').val(unassign);
                $('#inputConfirmPhraseClasses').val('DELETE');
                $('#formClassesDelete').submit();
            });

            // 5. Subjects Tab
            setupCheckAll('checkAllSubjects', 'subject-checkbox', 'countSelectedSubjects', 'btnDeleteSelectedSubjects');
            $('#btnDeleteSelectedSubjects').on('click', function() {
                var checked = $('.subject-checkbox:checked').length;
                $('#modalSelectedSubjectsCount').text(checked);
                $('#inputDeleteSubjectsConfirm').val('');
                $('#btnSubmitDeleteSubjects').prop('disabled', true);
            });
            $('#inputDeleteSubjectsConfirm').on('input', function() {
                $('#btnSubmitDeleteSubjects').prop('disabled', $(this).val().trim().toUpperCase() !== 'DELETE');
            });
            $('#btnSubmitDeleteSubjects').on('click', function() {
                $('#inputConfirmPhraseSubjects').val('DELETE');
                $('#formSubjectsDelete').submit();
            });

            // 6. Bulk Allocate Tab
            setupCheckAll('checkAllBulkStudents', 'bulk-student-checkbox', null, null);
            $('#btnRunBulkAllocation').on('click', function() {
                var checked = $('.bulk-student-checkbox:checked').length;
                $('#modalBulkStudentCount').text(checked);
            });
            $('#btnConfirmBulkAllocation').on('click', function() {
                var fsId = $('#modalSelectFeeStructure').val();
                $('#inputBulkFeeStructureId').val(fsId);
                $('#formBulkAllocate').submit();
            });
        });
    </script>
</body>
</html>

