@extends('admin.layout')

@section('title', 'Student Profile - ' . $student->first_name . ' ' . $student->last_name)

@section('content')
<div class="page-inner">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Student Header Banner -->
    <div class="card bg-primary text-white shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-2 text-center mb-3 mb-md-0">
                    @if($student->passport)
                        <img src="{{ asset('storage/' . $student->passport) }}" alt="Passport" class="img-fluid rounded-circle border border-3 border-white shadow" style="width: 120px; height: 120px; object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-white text-primary d-inline-flex align-items-center justify-content-center border border-3 border-white shadow" style="width: 120px; height: 120px;">
                            <i class="fas fa-user-graduate fa-4x"></i>
                        </div>
                    @endif
                </div>
                <div class="col-md-7">
                    <h2 class="fw-bold mb-1">{{ $student->first_name }} {{ $student->other_names }} {{ $student->last_name }}</h2>
                    <p class="mb-2 text-white-50"><i class="fas fa-id-card me-1"></i> Admission No: <strong>{{ $student->admission_no }}</strong> | Student No: <strong>{{ $student->student_number }}</strong></p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark"><i class="fas fa-chalkboard me-1"></i> {{ $student->currentClass ? $student->currentClass->class_name : 'Not Assigned' }}</span>
                        <span class="badge bg-light text-dark"><i class="fas fa-calendar-alt me-1"></i> {{ $student->currentSession ? $student->currentSession->session_name : 'N/A' }}</span>
                        <span class="badge bg-light text-dark"><i class="fas fa-clock me-1"></i> {{ $student->currentTerm ? $student->currentTerm->term_name : 'N/A' }}</span>
                        <span class="badge bg-{{ in_array($student->category, ['NI', 'NEW_INTAKE']) ? 'info' : 'secondary' }}">
                            {{ in_array($student->category, ['NI', 'NEW_INTAKE']) ? 'New Intake (NI)' : 'Returning Student (OS)' }}
                        </span>
                    </div>
                </div>
                <div class="col-md-3 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-light btn-sm fw-bold me-1 mb-1"><i class="fas fa-edit me-1"></i> Edit Profile</a>
                    <a href="{{ route('admin.students.admission_letter', $student) }}" target="_blank" class="btn btn-outline-light btn-sm me-1 mb-1"><i class="fas fa-print me-1"></i> Admission Letter</a>
                    <a href="{{ route('admin.students') }}" class="btn btn-dark btn-sm me-1 mb-1"><i class="fas fa-arrow-left me-1"></i> Back</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Financial Action Cards -->
    <div class="row mb-4">
        @php
            $activeFee = $student->studentFees->first();
            $baseAmount = $activeFee ? $activeFee->base_amount : 0;
            $additionalAmount = $student->additionalCharges->sum('amount');
            $totalPayable = $activeFee ? $activeFee->total_payable : $additionalAmount;
            $amountPaid = $activeFee ? $activeFee->amount_paid : 0;
            $amountOwed = $activeFee ? $activeFee->amount_owed : 0;
            $status = $activeFee ? $activeFee->status : 'unpaid';
        @endphp

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">Base Fee</div>
                            <h3 class="fw-bold text-dark mb-0">₦{{ number_format($baseAmount, 2) }}</h3>
                        </div>
                        <div class="avatar-sm rounded-circle bg-primary-soft text-primary d-flex align-items-center justify-content-center">
                            <i class="fas fa-file-invoice-dollar fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">Extra / Additional Fees</div>
                            <h3 class="fw-bold text-warning mb-0">₦{{ number_format($additionalAmount, 2) }}</h3>
                        </div>
                        <div class="avatar-sm rounded-circle bg-warning-soft text-warning d-flex align-items-center justify-content-center">
                            <i class="fas fa-plus-circle fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">Amount Paid</div>
                            <h3 class="fw-bold text-success mb-0">₦{{ number_format($amountPaid, 2) }}</h3>
                        </div>
                        <div class="avatar-sm rounded-circle bg-success-soft text-success d-flex align-items-center justify-content-center">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">Amount Owed</div>
                            <h3 class="fw-bold text-danger mb-0">₦{{ number_format($amountOwed, 2) }}</h3>
                        </div>
                        <div class="avatar-sm rounded-circle bg-danger-soft text-danger d-flex align-items-center justify-content-center">
                            <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons Bar -->
    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <div>
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-wallet me-2 text-primary"></i>Financial Actions</h5>
                <small class="text-muted">Manage extra charges and record fee payments for this student</small>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-warning fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#addExtraFeeModal">
                    <i class="fas fa-plus-circle me-1"></i> Add Extra Fee / Charge
                </button>
                <button type="button" class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                    <i class="fas fa-cash-register me-1"></i> Record Payment
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Column: Details & Additional Charges -->
        <div class="col-lg-8">
            <!-- Student Profile Information Tab Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title fw-bold mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Profile Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <p class="mb-1 text-muted small fw-bold">FULL NAME</p>
                            <h6>{{ $student->first_name }} {{ $student->other_names }} {{ $student->last_name }}</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="mb-1 text-muted small fw-bold">GENDER</p>
                            <h6>{{ ucfirst($student->gender) }}</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="mb-1 text-muted small fw-bold">DATE OF BIRTH</p>
                            <h6>{{ $student->date_of_birth ? $student->date_of_birth->format('d M, Y') : 'N/A' }}</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="mb-1 text-muted small fw-bold">EMAIL ADDRESS</p>
                            <h6>{{ $student->email }}</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="mb-1 text-muted small fw-bold">PHONE NUMBER</p>
                            <h6>{{ $student->phone ?? 'N/A' }}</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="mb-1 text-muted small fw-bold">STATE / LGA</p>
                            <h6>{{ $student->state_of_origin ?? 'N/A' }} / {{ $student->lga ?? 'N/A' }}</h6>
                        </div>
                        <div class="col-md-12 mb-3">
                            <p class="mb-1 text-muted small fw-bold">ADDRESS</p>
                            <h6>{{ $student->address ?? $student->home_address ?? 'N/A' }}</h6>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Charges List -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0"><i class="fas fa-plus-square me-2 text-warning"></i>Extra / Additional Fees Assigned</h5>
                    <button type="button" class="btn btn-sm btn-warning fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#addExtraFeeModal">
                        <i class="fas fa-plus me-1"></i> Add Extra Fee
                    </button>
                </div>
                <div class="card-body p-0">
                    @if($student->additionalCharges->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-info-circle fa-2x mb-2 d-block"></i> No additional charges added to this student yet.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="datatable table table-hover align-middle mb-0 datatable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fee Name</th>
                                        <th>Amount (₦)</th>
                                        <th>Reason / Description</th>
                                        <th>Date Added</th>
                                        <th>Added By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($student->additionalCharges as $charge)
                                        <tr>
                                            <td class="fw-bold text-dark">{{ $charge->fee_name }}</td>
                                            <td class="fw-bold text-warning">₦{{ number_format($charge->amount, 2) }}</td>
                                            <td>{{ $charge->reason ?? 'N/A' }}</td>
                                            <td>{{ $charge->created_at ? $charge->created_at->format('d M, Y') : 'N/A' }}</td>
                                            <td>{{ $charge->addedBy ? $charge->addedBy->name : 'Admin' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Financial Summary & Payments History -->
        <div class="col-lg-4">
            <!-- Allocated Fee Breakdown Card -->
            <div class="card shadow-sm mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-calculator me-2"></i>Financial Calculation</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Base Allocated Fee:</span>
                        <strong class="text-dark">₦{{ number_format($baseAmount, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Additional Fees:</span>
                        <strong class="text-warning">+ ₦{{ number_format($additionalAmount, 2) }}</strong>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold text-dark">Total Payable:</span>
                        <strong class="fw-bold text-primary fs-5">₦{{ number_format($totalPayable, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Paid:</span>
                        <strong class="text-success">- ₦{{ number_format($amountPaid, 2) }}</strong>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold text-dark">Amount Owed:</span>
                        <strong class="fw-bold text-danger fs-5">₦{{ number_format($amountOwed, 2) }}</strong>
                    </div>
                    <div class="text-center">
                        <span class="badge bg-{{ $status === 'paid' ? 'success' : ($status === 'partially_paid' ? 'warning' : ($status === 'overpaid' ? 'info' : 'danger')) }} w-100 py-2 fs-6">
                            STATUS: {{ strtoupper(str_replace('_', ' ', $status)) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Recent Payments Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title fw-bold mb-0"><i class="fas fa-history me-2 text-success"></i>Payment Transactions</h5>
                </div>
                <div class="card-body p-0">
                    @php
                        $payments = $student->payments->sortByDesc('created_at');
                    @endphp

                    @if($payments->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-info-circle fa-2x mb-2 d-block"></i> No payments recorded yet.
                        </div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($payments as $pmt)
                                <li class="list-group-item p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="text-success">₦{{ number_format($pmt->amount, 2) }}</strong>
                                        <small class="text-muted">{{ $pmt->payment_date ? \Carbon\Carbon::parse($pmt->payment_date)->format('d M, Y') : $pmt->created_at->format('d M, Y') }}</small>
                                    </div>
                                    <div class="small text-muted mb-1">
                                        <i class="fas fa-receipt me-1"></i> Ref: <code>{{ $pmt->payment_reference }}</code> | Method: {{ ucfirst($pmt->payment_method) }}
                                    </div>
                                    @if($pmt->description)
                                        <div class="small text-secondary">{{ $pmt->description }}</div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Extra Fee / Charge -->
<div class="modal fade" id="addExtraFeeModal" tabindex="-1" aria-labelledby="addExtraFeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold" id="addExtraFeeModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add Extra Fee / Charge
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.fees.additional_charge') }}" method="POST">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->id }}">
                <div class="modal-body">
                    <p class="text-muted small">Add a custom extra charge (e.g. Exam Fee, Damage Fine, Uniform Replacement) to {{ $student->first_name }} {{ $student->last_name }}'s profile.</p>

                    <div class="mb-3">
                        <label for="fee_name" class="form-label fw-bold">Fee Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="fee_name" id="fee_name" placeholder="e.g. Extra Exam Fee, Bus Levy" required>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label fw-bold">Amount (₦) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" class="form-control" name="amount" id="amount" placeholder="0.00" required>
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label fw-bold">Reason / Description</label>
                        <textarea class="form-control" name="reason" id="reason" rows="3" placeholder="State reason for this charge"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark">Add Charge</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Record Payment -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="recordPaymentModalLabel">
                    <i class="fas fa-cash-register me-2"></i>Record Payment - {{ $student->first_name }} {{ $student->last_name }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('accountant.payments.record') }}" method="POST">
                @csrf
                <input type="hidden" name="student_fee_id" value="{{ $activeFee ? $activeFee->id : '' }}">
                <div class="modal-body">
                    @if(!$activeFee)
                        <div class="alert alert-warning">No active fee allocation found for this student. Please allocate a fee structure first.</div>
                    @else
                        <div class="alert alert-info">
                            <strong>Outstanding Balance:</strong> ₦{{ number_format($amountOwed, 2) }}<br>
                            <small>Total Payable: ₦{{ number_format($totalPayable, 2) }} | Paid: ₦{{ number_format($amountPaid, 2) }}</small>
                        </div>

                        <div class="mb-3">
                            <label for="payment_amount" class="form-label fw-bold">Payment Amount (₦) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" class="form-control form-control-lg" name="amount" id="payment_amount" placeholder="0.00" required>
                        </div>

                        <div class="mb-3">
                            <label for="payment_date" class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="payment_date" id="payment_date" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="payment_method" class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-select" name="payment_method" id="payment_method" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="card">POS / Card</option>
                                <option value="cheque">Cheque</option>
                                <option value="online">Online Payment</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="payer_name" class="form-label fw-bold">Payer Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="payer_name" id="payer_name" value="{{ $student->first_name }} {{ $student->last_name }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="payer_phone" class="form-label fw-bold">Payer Phone</label>
                            <input type="text" class="form-control" name="payer_phone" id="payer_phone" value="{{ $student->phone }}">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">Description / Receipt Notes</label>
                            <textarea class="form-control" name="description" id="description" rows="2" placeholder="e.g. Part payment for tuition"></textarea>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    @if($activeFee)
                        <button type="submit" class="btn btn-success fw-bold">Record Payment</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
