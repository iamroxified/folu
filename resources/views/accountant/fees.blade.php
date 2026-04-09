@extends('accountant.layout')

@section('title', 'Fees Management')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">Fees Management</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Fee Structures</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Amount</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($feeStructures as $fee)
                            <tr>
                                <td>{{ $fee->name }}</td>
                                <td>₦{{ number_format($fee->amount, 2) }}</td>
                                <td>{{ $fee->description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">No fee structures found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Student Fees</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Fee Type</th>
                            <th>Amount Due</th>
                            <th>Amount Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($studentFees as $studentFee)
                            <tr>
                                <td>{{ $studentFee->student->first_name }} {{ $studentFee->student->last_name }}</td>
                                <td>{{ $studentFee->feeStructure->name }}</td>
                                <td>₦{{ number_format($studentFee->amount_due, 2) }}</td>
                                <td>₦{{ number_format($studentFee->amount_paid, 2) }}</td>
                                <td>₦{{ number_format($studentFee->balance, 2) }}</td>
                                <td>
                                    @php
                                        $status = $studentFee->status;
                                        $class = $status == 'paid' ? 'badge bg-success' : ($status == 'partial' ? 'badge bg-warning' : 'badge bg-danger');
                                    @endphp
                                    <span class="{{ $class }}">{{ ucfirst($status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No student fees found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection