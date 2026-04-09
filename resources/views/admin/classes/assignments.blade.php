@extends('admin.layout')

@section('title', 'Class Assignments')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">Class & Subject Assignments</div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Note:</strong> The assignments module is currently being ported from the legacy interface.
                    Please check back later or use the legacy interface.
                </div>
                <a href="{{ route('admin.legacy', ['path' => 'classes/assign_students.php']) }}" class="btn btn-primary me-2">Assign Students to Classes</a>
                <a href="{{ route('admin.legacy', ['path' => 'teachers/assign_subjects.php']) }}" class="btn btn-primary">Assign Subjects to Teachers</a>
            </div>
        </div>
    </div>
</div>
@endsection
