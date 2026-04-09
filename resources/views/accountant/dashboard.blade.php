@extends('accountant.layout')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">Accountant Dashboard</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Students</h5>
                <h2>{{ $totalStudents }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Total Fees</h5>
                <h2>₦{{ number_format($totalFees, 2) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Total Payments</h5>
                <h2>₦{{ number_format($totalPayments, 2) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Pending Payments</h5>
                <h2>₦{{ number_format($pendingPayments, 2) }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Staff</h5>
                <h2>{{ $totalStaff }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5 class="card-title">Total Payroll</h5>
                <h2>₦{{ number_format($totalPayroll, 2) }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Quick Actions</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('accountant.payments') }}" class="btn btn-primary me-2">Record Payment</a>
                <a href="{{ route('accountant.payroll') }}" class="btn btn-success me-2">Manage Payroll</a>
                <a href="{{ route('accountant.reports') }}" class="btn btn-info">View Reports</a>
            </div>
        </div>
    </div>
</div>
@endsection