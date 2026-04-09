@extends('admin.layout')

@section('title', 'Subjects')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Subjects</h2>
    <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary">Add Subject</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Department</th>
            <th>Type</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($subjects as $subject)
        <tr>
            <td>{{ $subject->subject_code }}</td>
            <td>{{ $subject->subject_name }}</td>
            <td>{{ $subject->department }}</td>
            <td>{{ ucfirst($subject->subject_type) }}</td>
            <td>{{ ucfirst($subject->status) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
