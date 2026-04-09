@extends('admin.layout')

@section('title', 'Add Academic Session')

@section('content')
<h2>Add Academic Session</h2>

<form action="{{ route('admin.sessions.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="session_name" class="form-label">Session Name</label>
                <input type="text" class="form-control" id="session_name" name="session_name" required>
            </div>
            <div class="mb-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" required>
            </div>
            <div class="mb-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" required>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1">
                <label class="form-check-label" for="is_active">Is Active</label>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Create Session</button>
</form>
@endsection
