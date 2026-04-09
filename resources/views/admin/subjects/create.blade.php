@extends('admin.layout')

@section('title', 'Add Subject')

@section('content')
<h2>Add Subject</h2>

<form action="{{ route('admin.subjects.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="subject_code" class="form-label">Subject Code</label>
                <input type="text" class="form-control" id="subject_code" name="subject_code" required>
            </div>
            <div class="mb-3">
                <label for="subject_name" class="form-label">Subject Name</label>
                <input type="text" class="form-control" id="subject_name" name="subject_name" required>
            </div>
            <div class="mb-3">
                <label for="department" class="form-label">Department</label>
                <input type="text" class="form-control" id="department" name="department">
            </div>
            <div class="mb-3">
                <label for="credit_hours" class="form-label">Credit Hours</label>
                <input type="number" class="form-control" id="credit_hours" name="credit_hours" value="1" min="1">
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="subject_type" class="form-label">Subject Type</label>
                <select class="form-control" id="subject_type" name="subject_type">
                    <option value="core">Core</option>
                    <option value="elective">Elective</option>
                    <option value="optional">Optional</option>
                </select>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="is_practical" name="is_practical" value="1">
                <label class="form-check-label" for="is_practical">Practical</label>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="retired">Retired</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4"></textarea>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Create Subject</button>
</form>
@endsection
