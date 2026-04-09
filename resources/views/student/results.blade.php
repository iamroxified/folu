@extends('student.layout')

@section('title', 'My Results')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">My Academic Results</h1>
    </div>
</div>

@if($grades->count() > 0)
    @foreach($grades as $sessionId => $terms)
        @php
            $session = \App\Models\AcademicSession::find($sessionId);
        @endphp
        <div class="card mb-4">
            <div class="card-header">
                <h5>{{ $session ? $session->session_name : 'Session ' . $sessionId }}</h5>
            </div>
            <div class="card-body">
                @foreach($terms as $termId => $termGrades)
                    @php
                        $term = \App\Models\Term::find($termId);
                    @endphp
                    <h6 class="mt-3">{{ $term ? $term->term_name : 'Term ' . $termId }}</h6>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Score (%)</th>
                                    <th>Grade</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($termGrades as $grade)
                                    <tr>
                                        <td>{{ $grade->subject->subject_name }}</td>
                                        <td>{{ $grade->score }}</td>
                                        <td>
                                            <span class="badge bg-{{ $grade->grade == 'A' ? 'success' : ($grade->grade == 'B' ? 'primary' : ($grade->grade == 'C' ? 'warning' : ($grade->grade == 'D' ? 'danger' : 'secondary'))) }}">
                                                {{ $grade->grade }}
                                            </span>
                                        </td>
                                        <td>{{ $grade->remarks ?: 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @php
                        $average = $termGrades->avg('score');
                        $overallGrade = $average >= 90 ? 'A' : ($average >= 80 ? 'B' : ($average >= 70 ? 'C' : ($average >= 60 ? 'D' : 'F')));
                    @endphp
                    <div class="mt-3">
                        <strong>Term Average: {{ number_format($average, 2) }}% (Grade: {{ $overallGrade }})</strong>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@else
    <div class="card">
        <div class="card-body text-center">
            <h5>No Results Available</h5>
            <p class="text-muted">Your academic results will appear here once they are published by your teachers.</p>
        </div>
    </div>
@endif
@endsection