@extends('admin.layout')

@section('title', 'Edit Student')

@section('content')
<h2>Edit Student</h2>

<form action="{{ route('admin.students.update', $student) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name *</label>
                <input type="text" class="form-control" id="first_name" name="first_name" value="{{ $student->first_name }}" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name *</label>
                <input type="text" class="form-control" id="last_name" name="last_name" value="{{ $student->last_name }}" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email *</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ $student->email }}" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" value="{{ $student->phone }}">
            </div>
            <div class="mb-3">
                <label for="date_of_birth" class="form-label">Date of Birth *</label>
                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="{{ $student->date_of_birth->format('Y-m-d') }}" required>
            </div>
            <div class="mb-3">
                <label for="gender" class="form-label">Gender *</label>
                <select class="form-control" id="gender" name="gender" required>
                    <option value="male" {{ $student->gender == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ $student->gender == 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ $student->gender == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3">{{ $student->address }}</textarea>
            </div>
            <div class="mb-3">
                <label for="admission_status" class="form-label">Admission Status *</label>
                <select class="form-control" id="admission_status" name="admission_status" required>
                    <option value="pending" {{ $student->admission_status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="admitted" {{ $student->admission_status == 'admitted' ? 'selected' : '' }}>Admitted</option>
                    <option value="withdrawn" {{ $student->admission_status == 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Category *</label>
                <select class="form-control" id="category" name="category" required>
                    <option value="NI" {{ $student->category == 'NI' ? 'selected' : '' }}>New Intake (NI)</option>
                    <option value="OS" {{ $student->category == 'OS' ? 'selected' : '' }}>Old Student (OS)</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="passport" class="form-label">Passport Photo</label>
                <input type="file" class="form-control" id="passport" name="passport" accept="image/*">
                @if($student->passport)
                    <small class="form-text text-muted">Leave empty to keep current photo</small>
                @endif
            </div>
            <div class="mb-3">
                <label for="current_class_id" class="form-label">Current Class</label>
                <select class="form-control" id="current_class_id" name="current_class_id">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $student->current_class_id == $class->id ? 'selected' : '' }}>{{ $class->class_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="current_session_id" class="form-label">Current Session</label>
                <select class="form-control" id="current_session_id" name="current_session_id">
                    <option value="">Select Session</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}" {{ $student->current_session_id == $session->id ? 'selected' : '' }}>{{ $session->session_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="current_term_id" class="form-label">Current Term</label>
                <select class="form-control" id="current_term_id" name="current_term_id">
                    <option value="">Select Term</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ $student->current_term_id == $term->id ? 'selected' : '' }}>{{ $term->term_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Update Student</button>
    <a href="{{ route('admin.students.show', $student) }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
