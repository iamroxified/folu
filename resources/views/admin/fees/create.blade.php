@extends('admin.layout')

@section('title', 'Create Fee Structure')

@section('content')
<h2>Create Fee Structure</h2>

<form action="{{ route('admin.fees.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="name" class="form-label">Fee Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">Amount</label>
                <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
            </div>
            <div class="mb-3">
                <label for="frequency" class="form-label">Frequency</label>
                <select class="form-control" id="frequency" name="frequency" required>
                    <option value="one_time">One-time</option>
                    <option value="monthly">Monthly</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="semi_annual">Semi Annual</option>
                    <option value="annual">Annual</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="fee_type" class="form-label">Fee Type</label>
                <select class="form-control" id="fee_type" name="fee_type" required>
                    <option value="tuition">Tuition</option>
                    <option value="registration">Registration</option>
                    <option value="transport">Transport</option>
                    <option value="library">Library</option>
                    <option value="sports">Sports</option>
                    <option value="laboratory">Laboratory</option>
                    <option value="uniform">Uniform</option>
                    <option value="exam">Exam</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="effective_from" class="form-label">Effective From</label>
                <input type="date" class="form-control" id="effective_from" name="effective_from" required>
            </div>
            <div class="mb-3">
                <label for="effective_to" class="form-label">Effective To</label>
                <input type="date" class="form-control" id="effective_to" name="effective_to">
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-control" id="category" name="category" required>
                    <option value="NI">New Intake</option>
                    <option value="OS">Old Student</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="gender" class="form-label">Gender</label>
                <select class="form-control" id="gender" name="gender" required>
                    <option value="All">All</option>
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="session_id" class="form-label">Academic Session</label>
                <select class="form-control" id="session_id" name="session_id" required>
                    <option value="">Choose Session</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}">{{ $session->session_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="term_id" class="form-label">Term</label>
                <select class="form-control" id="term_id" name="term_id" required>
                    <option value="">Choose Term</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}">{{ $term->term_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="class_id" class="form-label">Class</label>
                <select class="form-control" id="class_id" name="class_id">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Save Fee Structure</button>
</form>
@endsection
