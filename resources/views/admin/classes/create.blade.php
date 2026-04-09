@extends('admin.layout')

@section('title', 'Add Class')

@section('content')
<h2>Add Class</h2>

<form action="{{ route('admin.classes.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="class_name" class="form-label">Class Name</label>
                <input type="text" class="form-control" id="class_name" name="class_name" required>
            </div>
            <div class="mb-3">
                <label for="grade_level" class="form-label">Grade Level</label>
                <input type="text" class="form-control" id="grade_level" name="grade_level" required>
            </div>
            <div class="mb-3">
                <label for="section" class="form-label">Section</label>
                <input type="text" class="form-control" id="section" name="section">
            </div>
            <div class="mb-3">
                <label for="academic_year" class="form-label">Academic Year</label>
                <input type="text" class="form-control" id="academic_year" name="academic_year" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="class_teacher_id" class="form-label">Class Teacher</label>
                <select class="form-control" id="class_teacher_id" name="class_teacher_id">
                    <option value="">No teacher assigned</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->name }} ({{ $teacher->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="classroom_location" class="form-label">Classroom Location</label>
                <input type="text" class="form-control" id="classroom_location" name="classroom_location">
            </div>
            <div class="mb-3">
                <label for="max_capacity" class="form-label">Max Capacity</label>
                <input type="number" class="form-control" id="max_capacity" name="max_capacity" value="30" min="1">
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Create Class</button>
</form>
@endsection
