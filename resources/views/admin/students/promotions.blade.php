@extends('admin.layout')

@section('title', 'Student Promotions')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">Bulk Student Promotions</div>
            <div class="card-body">
                <p class="text-muted">Use this module at the end of a term or session to automatically advance students to the next term/class. New Intake (NI) students will be automatically re-categorized as Old Students (OS) upon promotion.</p>
                <form action="{{ route('admin.students.promotions.process') }}" method="POST">
                    @csrf
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5>From:</h5>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Select Current Class</label>
                            <select name="from_class_id" class="form-control" required>
                                <option value="">-- Select Class --</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Promote To:</h5>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Select Target Class</label>
                            <select name="to_class_id" class="form-control" required>
                                <option value="">-- Select Class --</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Select Target Session</label>
                            <select name="to_session_id" class="form-control" required>
                                <option value="">-- Select Session --</option>
                                @foreach($sessions as $session)
                                    <option value="{{ $session->id }}">{{ $session->session_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Select Target Term</label>
                            <select name="to_term_id" class="form-control" required>
                                <option value="">-- Select Term --</option>
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}">{{ $term->term_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success mt-3" onclick="return confirm('Are you sure you want to bulk promote these students? This action cannot be easily undone.')">Process Promotions</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
