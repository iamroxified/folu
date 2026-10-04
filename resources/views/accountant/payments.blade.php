@extends('accountant.layout')

@section('title', 'Payments Management')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h2 class="fw-bold mb-4">Payments Management</h2>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="fas fa-cash-register me-2"></i>Record New Payment</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('accountant.payments.record') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="student_id" class="form-label fw-bold">Student <span class="text-danger">*</span></label>
                                <select class="form-select" id="student_id" name="student_id" required>
                                    <option value="">Select Student</option>
                                    @foreach(\App\Models\Student::orderBy('first_name')->get() as $student)
                                        <option value="{{ $student->id }}">{{ $student->first_name }} {{ $student->last_name }} ({{ $student->student_number }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="student_fee_id" class="form-label fw-bold">Fee Allocation <span class="text-danger">*</span></label>
                                <select class="form-select" id="student_fee_id" name="student_fee_id" required>
                                    <option value="">Select Fee Allocation</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="amount" class="form-label fw-bold">Amount (₦) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="payment_date" class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="payment_method" class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="card">Card / POS</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="payer_name" class="form-label fw-bold">Payer Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="payer_name" name="payer_name" placeholder="Payer Name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payer_phone" class="form-label fw-bold">Payer Phone</label>
                                <input type="text" class="form-control" id="payer_phone" name="payer_phone" placeholder="Payer Contact Phone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="description" class="form-label fw-bold">Description / Remark</label>
                                <textarea class="form-control" id="description" name="description" rows="2" placeholder="Optional notes"></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success fw-bold"><i class="fas fa-check-circle me-1"></i> Record Payment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="card-title mb-0"><i class="fas fa-history me-2"></i>Payment History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="datatable table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">S/N</th>
                                <th>Reference</th>
                                <th>Student Name</th>
                                <th>Amount (₦)</th>
                                <th>Payment Date</th>
                                <th>Method</th>
                                <th>Payer Name</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><code>{{ $payment->payment_reference }}</code></td>
                                    <td class="fw-bold">
                                        @if($payment->payable && $payment->payable->student)
                                            {{ $payment->payable->student->first_name }} {{ $payment->payable->student->last_name }}
                                        @else
                                            {{ $payment->payer_name }}
                                        @endif
                                    </td>
                                    <td class="fw-bold text-success">₦{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') : 'N/A' }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                    <td>{{ $payment->payer_name }}</td>
                                    <td>
                                        <span class="badge bg-success">Completed</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No payments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('student_id').addEventListener('change', function() {
    var studentId = this.value;
    var feeSelect = document.getElementById('student_fee_id');

    if (studentId) {
        fetch('/accountant/get-student-fees/' + studentId)
            .then(response => response.json())
            .then(data => {
                feeSelect.innerHTML = '<option value="">Select Fee Allocation</option>';
                data.forEach(function(fee) {
                    feeSelect.innerHTML += '<option value="' + fee.id + '">' + fee.fee_structure_name + ' (Total: ₦' + parseFloat(fee.total_payable).toLocaleString() + ', Owed: ₦' + parseFloat(fee.amount_owed).toLocaleString() + ')</option>';
                });
            });
    } else {
        feeSelect.innerHTML = '<option value="">Select Fee Allocation</option>';
    }
});
</script>
@endsection