@extends('teacher.layout')

@section('title', 'Attendance Management')

@section('content')
<h2>Attendance Management</h2>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Mark Attendance</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('teacher.attendance.mark') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="class_id" class="form-label">Select Class</label>
                        <select class="form-control" id="class_id" name="class_id" required>
                            <option value="">Choose Class</option>
                            @foreach($assignedClasses as $class)
                                <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="session_id" value="{{ $activeSession ? $activeSession->id : '' }}">
                    <input type="hidden" name="term_id" value="{{ $activeTerm ? $activeTerm->id : '' }}">
                    <div class="mb-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary" id="loadStudents">Load Students</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>View Attendance</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('teacher.attendance.view') }}" method="GET">
                    <div class="mb-3">
                        <label for="view_class_id" class="form-label">Select Class</label>
                        <select class="form-control" id="view_class_id" name="class_id" required>
                            <option value="">Choose Class</option>
                            @foreach($assignedClasses as $class)
                                <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="session_id" value="{{ $activeSession ? $activeSession->id : '' }}">
                    <input type="hidden" name="term_id" value="{{ $activeTerm ? $activeTerm->id : '' }}">
                    <div class="mb-3">
                        <label for="view_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="view_date" name="date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <button type="submit" class="btn btn-info">View Attendance</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('loadStudents').addEventListener('click', function(e) {
    e.preventDefault();
    const classId = document.getElementById('class_id').value;
    const sessionId = document.querySelector('input[name="session_id"]').value;
    const termId = document.querySelector('input[name="term_id"]').value;
    const date = document.getElementById('date').value;

    if (!classId || !sessionId || !termId || !date) {
        alert('Please fill all fields');
        return;
    }

    // Redirect to view attendance page to mark
    window.location.href = `{{ route('teacher.attendance.view') }}?class_id=${classId}&session_id=${sessionId}&term_id=${termId}&date=${date}&mark=1`;
});
</script>
@endsection
