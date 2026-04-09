@extends('student.layout')

@section('title', 'My Payments')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">My Fee Payments</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Fee Summary</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Fee Type</th>
                                <th>Amount Due</th>
                                <th>Amount Paid</th>
                                <th>Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($studentFees as $fee)
                                <tr>
                                    <td>{{ $fee->feeStructure->name }}</td>
                                    <td>₦{{ number_format($fee->amount_due, 2) }}</td>
                                    <td>₦{{ number_format($fee->amount_paid, 2) }}</td>
                                    <td>₦{{ number_format($fee->balance, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $fee->status == 'paid' ? 'success' : ($fee->status == 'partial' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($fee->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No fees assigned yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Fee Type</th>
                                <th>Amount</th>
                                <th>Payment Date</th>
                                <th>Method</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                                <tr>
                                    <td>{{ $payment->payable->feeStructure->name }}</td>
                                    <td>₦{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                    <td>{{ $payment->payment_reference }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No payment history available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection