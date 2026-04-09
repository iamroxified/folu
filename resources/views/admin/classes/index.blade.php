@extends('admin.layout')

@section('title', 'Classes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Classes</h2>
    <a href="{{ route('admin.classes.create') }}" class="btn btn-primary">Add Class</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Grade Level</th>
            <th>Section</th>
            <th>Academic Year</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($classes as $class)
        <tr>
            <td>{{ $class->class_name }}</td>
            <td>{{ $class->grade_level }}</td>
            <td>{{ $class->section ?? 'N/A' }}</td>
            <td>{{ $class->academic_year }}</td>
            <td>{{ ucfirst($class->status) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
