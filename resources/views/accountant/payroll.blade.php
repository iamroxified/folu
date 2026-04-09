@extends('accountant.layout')

@section('title', 'Payroll Management')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">Payroll Management</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Create Payroll</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('accountant.payroll.create') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="staff_id" class="form-label">Staff Member</label>
                                <select class="form-control" id="staff_id" name="staff_id" required>
                                    <option value="">Select Staff</option>
                                    @foreach(\App\Models\Staff::all() as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->first_name }} {{ $staff->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount</label>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
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
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="1"></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Record Payroll Payment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Payroll History</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Staff Member</th>
                            <th>Amount</th>
                            <th>Payment Date</th>
                            <th>Method</th>
                            <th>Payer</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payrolls as $payroll)
                            <tr>
                                <td>{{ $payroll->payable->first_name }} {{ $payroll->payable->last_name }}</td>
                                <td>₦{{ number_format($payroll->amount, 2) }}</td>
                                <td>{{ $payroll->payment_date->format('d/m/Y') }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $payroll->payment_method)) }}</td>
                                <td>{{ $payroll->payer_name }}</td>
                                <td>{{ $payroll->payment_reference }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No payroll records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection