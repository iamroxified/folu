@extends('admin.layout')

@section('title', 'Fees')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Fee Structures</h2>
    <a href="{{ route('admin.fees.create') }}" class="btn btn-primary">Create Fee</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Amount</th>
            <th>Frequency</th>
            <th>Type</th>
            <th>Session</th>
            <th>Term</th>
            <th>Class</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($fees as $fee)
        <tr>
            <td>{{ $fee->name }}</td>
            <td>{{ number_format($fee->amount, 2) }}</td>
            <td>{{ ucfirst(str_replace('_', ' ', $fee->frequency)) }}</td>
            <td>{{ ucfirst($fee->fee_type) }}</td>
            <td>{{ $fee->session?->session_name ?? 'N/A' }}</td>
            <td>{{ $fee->term?->term_name ?? 'N/A' }}</td>
            <td>{{ $fee->schoolClass?->class_name ?? 'All' }}</td>
            <td>{{ $fee->is_active ? 'Active' : 'Inactive' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $fees->links() }}
@endsection
