@extends('admin.layout')

@section('title', 'Students')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Students</h2>
    <div>
        <a href="{{ route('admin.students.export') }}" class="btn btn-secondary me-2">Export CSV</a>
        <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data" class="d-inline-block me-2">
            @csrf
            <div class="input-group">
                <input type="file" name="csv_file" class="form-control form-control-sm" accept=".csv" required>
                <button type="submit" class="btn btn-sm btn-success">Import CSV</button>
            </div>
        </form>
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary">Add Student</a>
    </div>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Student Number</th>
            <th>Name</th>
            <th>Email</th>
            <th>Class</th>
            <th>Admission Status</th>
            <th>Category</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $student)
        <tr>
            <td>{{ $student->student_number }}</td>
            <td>{{ $student->first_name }} {{ $student->last_name }}</td>
            <td>{{ $student->email }}</td>
            <td>{{ $student->currentClass ? $student->currentClass->class_name : 'N/A' }}</td>
            <td>
                <span class="badge bg-{{ $student->admission_status == 'admitted' ? 'success' : ($student->admission_status == 'pending' ? 'warning' : 'danger') }}">
                    {{ ucfirst($student->admission_status) }}
                </span>
            </td>
            <td>{{ $student->category }}</td>
            <td>
                <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-info">View</a>
                <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-sm btn-warning">Edit</a>
                <form action="{{ route('admin.students.destroy', $student) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $students->links() }}
@endsection
