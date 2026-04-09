@extends('teacher.layout')

@section('title', 'View/Mark Attendance')

@section('content')
<h2>Attendance for {{ $request->date }} - Class {{ $request->class_id }}</h2>

@if(request('mark'))
    <form action="{{ route('teacher.attendance.mark') }}" method="POST">
        @csrf
        <input type="hidden" name="class_id" value="{{ $request->class_id }}">
        <input type="hidden" name="session_id" value="{{ $request->session_id }}">
        <input type="hidden" name="term_id" value="{{ $request->term_id }}">
        <input type="hidden" name="date" value="{{ $request->date }}">
@endif

<table class="table table-striped">
    <thead>
        <tr>
            <th>Student Number</th>
            <th>Name</th>
            <th>Status</th>
            @if(request('mark'))
                <th>Reason (if absent)</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach($students as $student)
        <tr>
            <td>{{ $student->student_number }}</td>
            <td>{{ $student->first_name }} {{ $student->last_name }}</td>
            <td>
                @if(request('mark'))
                    <select name="attendance[{{ $loop->index }}][status]" class="form-control">
                        <option value="present" {{ optional($attendance[$student->id])->status == 'present' ? 'selected' : '' }}>Present</option>
                        <option value="absent" {{ optional($attendance[$student->id])->status == 'absent' ? 'selected' : '' }}>Absent</option>
                    </select>
                    <input type="hidden" name="attendance[{{ $loop->index }}][student_id]" value="{{ $student->id }}">
                @else
                    <span class="badge bg-{{ optional($attendance[$student->id])->status == 'present' ? 'success' : 'danger' }}">
                        {{ ucfirst(optional($attendance[$student->id])->status ?? 'Not Marked') }}
                    </span>
                @endif
            </td>
            @if(request('mark'))
                <td>
                    <input type="text" name="attendance[{{ $loop->index }}][reason]" class="form-control"
                           value="{{ optional($attendance[$student->id])->reason ?? '' }}" placeholder="Reason for absence">
                </td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>

@if(request('mark'))
    <button type="submit" class="btn btn-primary">Save Attendance</button>
    </form>
@endif

<a href="{{ route('teacher.attendance') }}" class="btn btn-secondary">Back</a>
@endsection
