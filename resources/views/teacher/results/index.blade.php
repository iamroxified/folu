@extends('teacher.layout')

@section('title', 'Results Management')

@section('content')
<h2>Results Management</h2>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Enter Results</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('teacher.results.enter') }}" method="POST">
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
                    <div class="mb-3">
                        <label for="subject_id" class="form-label">Select Subject</label>
                        <select class="form-control" id="subject_id" name="subject_id" required>
                            <option value="">Choose Subject</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="session_id" value="{{ $activeSession ? $activeSession->id : '' }}">
                    <input type="hidden" name="term_id" value="{{ $activeTerm ? $activeTerm->id : '' }}">
                    <button type="submit" class="btn btn-primary" id="loadStudents">Load Students</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>View Results</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('teacher.results.view') }}" method="GET">
                    <div class="mb-3">
                        <label for="view_class_id" class="form-label">Select Class</label>
                        <select class="form-control" id="view_class_id" name="class_id" required>
                            <option value="">Choose Class</option>
                            @foreach($assignedClasses as $class)
                                <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="view_subject_id" class="form-label">Select Subject</label>
                        <select class="form-control" id="view_subject_id" name="subject_id" required>
                            <option value="">Choose Subject</option>
                            <!-- TODO: Load subjects -->
                        </select>
                    </div>
                    <input type="hidden" name="session_id" value="{{ $activeSession ? $activeSession->id : '' }}">
                    <input type="hidden" name="term_id" value="{{ $activeTerm ? $activeTerm->id : '' }}">
                    <button type="submit" class="btn btn-info">View Results</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('loadStudents').addEventListener('click', function(e) {
    e.preventDefault();
    const classId = document.getElementById('class_id').value;
    const subjectId = document.getElementById('subject_id').value;
    const sessionId = document.querySelector('input[name="session_id"]').value;
    const termId = document.querySelector('input[name="term_id"]').value;

    if (!classId || !subjectId || !sessionId || !termId) {
        alert('Please fill all fields');
        return;
    }

    // Redirect to view results page to enter
    window.location.href = `{{ route('teacher.results.view') }}?class_id=${classId}&subject_id=${subjectId}&session_id=${sessionId}&term_id=${termId}&enter=1`;
});
</script>
@endsection
