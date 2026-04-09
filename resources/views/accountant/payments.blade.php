@extends('accountant.layout')

@section('title', 'Payments Management')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">Payments Management</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Record New Payment</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('accountant.payments.record') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="student_id" class="form-label">Student</label>
                                <select class="form-control" id="student_id" name="student_id" required>
                                    <option value="">Select Student</option>
                                    @foreach(\App\Models\Student::all() as $student)
                                        <option value="{{ $student->id }}">{{ $student->first_name }} {{ $student->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="student_fee_id" class="form-label">Fee Type</label>
                                <select class="form-control" id="student_fee_id" name="student_fee_id" required>
                                    <option value="">Select Fee</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount</label>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="payment_date" class="form-label">Payment Date</label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select class="form-control" id="payment_method" name="payment_method" required>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="card">Card</option>
                                    <option value="mobile_money">Mobile Money</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="payer_name" class="form-label">Payer Name</label>
                                <input type="text" class="form-control" id="payer_name" name="payer_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payer_phone" class="form-label">Payer Phone</label>
                                <input type="text" class="form-control" id="payer_phone" name="payer_phone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Record Payment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Payment History</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Fee Type</th>
                            <th>Amount</th>
                            <th>Payment Date</th>
                            <th>Method</th>
                            <th>Payer</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>{{ $payment->payable->student->first_name }} {{ $payment->payable->student->last_name }}</td>
                                <td>{{ $payment->payable->feeStructure->name }}</td>
                                <td>₦{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                <td>{{ $payment->payer_name }}</td>
                                <td>{{ $payment->payment_reference }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No payments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
                feeSelect.innerHTML = '<option value="">Select Fee</option>';
                data.forEach(function(fee) {
                    feeSelect.innerHTML += '<option value="' + fee.id + '">' + fee.fee_structure_name + ' - ₦' + fee.amount + '</option>';
                });
            });
    } else {
        feeSelect.innerHTML = '<option value="">Select Fee</option>';
    }
});
</script>
@endsection