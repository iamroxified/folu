@extends('accountant.layout')

@section('title', 'Financial Reports')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">Financial Reports</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Monthly Payment Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Year</th>
                            <th>Month</th>
                            <th>Total Payments</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlyPayments as $payment)
                            <tr>
                                <td>{{ $payment->year }}</td>
                                <td>{{ $payment->month }}</td>
                                <td>₦{{ number_format($payment->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">No payment data available.</td>
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
                <h5>Fee Collection Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Fee Type</th>
                            <th>Total Assigned</th>
                            <th>Total Paid</th>
                            <th>Outstanding</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($feeCollections as $fee)
                            <tr>
                                <td>{{ $fee->name }}</td>
                                <td>₦{{ number_format($fee->total_assigned, 2) }}</td>
                                <td>₦{{ number_format($fee->total_paid ?? 0, 2) }}</td>
                                <td>₦{{ number_format(($fee->total_assigned ?? 0) - ($fee->total_paid ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No fee collection data available.</td>
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
                <h5>Export Reports</h5>
            </div>
            <div class="card-body">
                <button class="btn btn-primary me-2" onclick="exportReport('monthly')">Export Monthly Payments</button>
                <button class="btn btn-success" onclick="exportReport('fees')">Export Fee Collections</button>
            </div>
        </div>
    </div>
</div>

<script>
function exportReport(type) {
    // Simple export functionality - in a real application, this would generate and download a file
    alert('Export functionality for ' + type + ' report would be implemented here.');
}
</script>
@endsection