@extends('teacher.layout')

@section('title', 'View/Enter Results')

@section('content')
<h2>Results for Class {{ $request->class_id }} - Subject {{ $request->subject_id }}</h2>

@if(request('enter'))
    <form action="{{ route('teacher.results.enter') }}" method="POST">
        @csrf
        <input type="hidden" name="class_id" value="{{ $request->class_id }}">
        <input type="hidden" name="subject_id" value="{{ $request->subject_id }}">
        <input type="hidden" name="session_id" value="{{ $request->session_id }}">
        <input type="hidden" name="term_id" value="{{ $request->term_id }}">
@endif

<table class="table table-striped">
    <thead>
        <tr>
            <th>Student Number</th>
            <th>Name</th>
            <th>CA1</th>
            <th>CA2</th>
            <th>Exam</th>
            <th>Total</th>
            <th>Grade</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $student)
        <tr>
            <td>{{ $student->student_number }}</td>
            <td>{{ $student->first_name }} {{ $student->last_name }}</td>
            @if(request('enter'))
                <td><input type="number" name="grades[{{ $loop->index }}][ca1]" class="form-control" min="0" max="100" value="{{ optional($grades[$student->id])->ca1_score ?? '' }}"></td>
                <td><input type="number" name="grades[{{ $loop->index }}][ca2]" class="form-control" min="0" max="100" value="{{ optional($grades[$student->id])->ca2_score ?? '' }}"></td>
                <td><input type="number" name="grades[{{ $loop->index }}][exam]" class="form-control" min="0" max="100" value="{{ optional($grades[$student->id])->exam_score ?? '' }}"></td>
                <input type="hidden" name="grades[{{ $loop->index }}][student_id]" value="{{ $student->id }}">
            @else
                <td>{{ optional($grades[$student->id])->ca1_score ?? '-' }}</td>
                <td>{{ optional($grades[$student->id])->ca2_score ?? '-' }}</td>
                <td>{{ optional($grades[$student->id])->exam_score ?? '-' }}</td>
            @endif
            <td>{{ $grades[$student->id]->total_score ?? '-' }}</td>
            <td>{{ $grades[$student->id]->grade ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

@if(request('enter'))
    <button type="submit" class="btn btn-primary">Save Results</button>
    </form>
@endif

<a href="{{ route('teacher.results') }}" class="btn btn-secondary">Back</a>
@endsection
