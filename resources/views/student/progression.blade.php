@extends('student.layout')

@section('title', 'Academic Progression')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">Academic Progression Status</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Current Term Performance</h5>
            </div>
            <div class="card-body">
                @if($grades->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Score (%)</th>
                                    <th>Grade</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($grades as $grade)
                                    <tr>
                                        <td>{{ $grade->subject->subject_name }}</td>
                                        <td>{{ $grade->score }}</td>
                                        <td>
                                            <span class="badge bg-{{ $grade->grade == 'A' ? 'success' : ($grade->grade == 'B' ? 'primary' : ($grade->grade == 'C' ? 'warning' : ($grade->grade == 'D' ? 'danger' : 'secondary'))) }}">
                                                {{ $grade->grade }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $grade->score >= 50 ? 'success' : 'danger' }}">
                                                {{ $grade->score >= 50 ? 'Pass' : 'Fail' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <h6>Term Summary</h6>
                        <p><strong>Average Score:</strong> {{ number_format($averageGrade, 2) }}%</p>
                        <p><strong>Overall Grade:</strong>
                            <span class="badge bg-{{ $averageGrade >= 90 ? 'success' : ($averageGrade >= 80 ? 'primary' : ($averageGrade >= 70 ? 'warning' : ($averageGrade >= 60 ? 'info' : 'danger'))) }}">
                                {{ $averageGrade >= 90 ? 'A' : ($averageGrade >= 80 ? 'B' : ($averageGrade >= 70 ? 'C' : ($averageGrade >= 60 ? 'D' : 'F'))) }}
                            </span>
                        </p>
                    </div>
                @else
                    <div class="alert alert-info">
                        <h6>No grades available for current term</h6>
                        <p>Your academic results will appear here once they are published by your teachers.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Progression Status</h5>
            </div>
            <div class="card-body text-center">
                @if($canProgress)
                    <div class="alert alert-success">
                        <h6>✅ Eligible for Progression</h6>
                        <p>Congratulations! You meet the requirements to progress to the next class.</p>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <h6>⚠️ Not Eligible for Progression</h6>
                        <p>You need to improve your performance to progress to the next class.</p>
                    </div>
                @endif

                <div class="mt-3">
                    <h6>Progression Criteria</h6>
                    <ul class="text-start">
                        <li>Minimum average score: 50%</li>
                        <li>No failing grades in core subjects</li>
                        <li>Complete all required assignments</li>
                        <li>Good attendance record</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5>Next Steps</h5>
            </div>
            <div class="card-body">
                @if($canProgress)
                    <p>Your progression to the next class will be processed by the administration.</p>
                    <p><strong>Next Class:</strong> {{ $student->currentClass ? $student->currentClass->class_name + 1 : 'TBD' }}</p>
                @else
                    <ul>
                        <li>Focus on improving weak subjects</li>
                        <li>Seek help from teachers</li>
                        <li>Complete pending assignments</li>
                        <li>Improve attendance</li>
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection