@extends('accountant.layout')

@section('title', 'Fees Management')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h2 class="fw-bold mb-4">Fees Management</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Configured Fee Structures</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="datatable table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">S/N</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Gender</th>
                                <th>Session / Term</th>
                                <th>Amount (₦)</th>
                                <th>Frequency</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($feeStructures as $fee)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-bold">{{ $fee->name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $fee->category === 'NI' ? 'info' : 'secondary' }}">
                                            {{ $fee->category === 'NI' ? 'New Intake (NI)' : 'Returning (OS)' }}
                                        </span>
                                    </td>
                                    <td>{{ $fee->gender }}</td>
                                    <td>
                                        {{ $fee->session ? $fee->session->session_name : 'N/A' }}
                                        {{ $fee->term ? '(' . $fee->term->term_name . ')' : '' }}
                                    </td>
                                    <td class="fw-bold text-primary">₦{{ number_format($fee->amount, 2) }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $fee->frequency)) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No fee structures found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0"><i class="fas fa-user-graduate me-2"></i>Student Fee Allocations</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="datatable table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">S/N</th>
                                <th>Student Name</th>
                                <th>Class</th>
                                <th>Fee Structure</th>
                                <th>Base Amount</th>
                                <th>Extra Fees</th>
                                <th>Total Payable</th>
                                <th>Amount Paid</th>
                                <th>Amount Owed</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($studentFees as $studentFee)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-bold">
                                        @if($studentFee->student)
                                            <a href="{{ route('admin.students.show', $studentFee->student) }}" class="text-decoration-none">
                                                {{ $studentFee->student->first_name }} {{ $studentFee->student->last_name }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $studentFee->student && $studentFee->student->currentClass ? $studentFee->student->currentClass->class_name : 'N/A' }}</td>
                                    <td>{{ $studentFee->feeStructure ? $studentFee->feeStructure->name : 'General Fee' }}</td>
                                    <td>₦{{ number_format($studentFee->base_amount, 2) }}</td>
                                    <td class="text-warning fw-bold">+ ₦{{ number_format($studentFee->additional_amount, 2) }}</td>
                                    <td class="fw-bold text-primary">₦{{ number_format($studentFee->total_payable, 2) }}</td>
                                    <td class="text-success fw-bold">₦{{ number_format($studentFee->amount_paid, 2) }}</td>
                                    <td class="text-danger fw-bold">₦{{ number_format($studentFee->amount_owed, 2) }}</td>
                                    <td>
                                        @php
                                            $st = strtolower($studentFee->status);
                                            $badgeClass = ($st === 'paid') ? 'bg-success' : (($st === 'partially_paid' || $st === 'partial') ? 'bg-warning text-dark' : (($st === 'overpaid') ? 'bg-info' : 'bg-danger'));
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ strtoupper(str_replace('_', ' ', $st)) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted">No student fees found.</td>
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