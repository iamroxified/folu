@extends('admin.layout')

@section('title', 'Timetable Management')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">Timetable Management</div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Note:</strong> The timetable module is currently being ported from the legacy interface.
                    Please check back later or use the legacy interface.
                </div>
                <a href="{{ route('admin.legacy', ['path' => 'classes/timetable.php']) }}" class="btn btn-primary">Go to Legacy Timetable</a>
            </div>
        </div>
    </div>
</div>
@endsection
