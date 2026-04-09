@extends('admin.layout')

@section('title', 'School Settings')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">School Settings</div>
            <div class="card-body">
                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">School Name</label>
                        <input type="text" name="school_name" class="form-control" value="{{ old('school_name', $settings->school_name ?? '') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">School Email</label>
                        <input type="email" name="school_email" class="form-control" value="{{ old('school_email', $settings->school_email ?? '') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">School Phone</label>
                        <input type="text" name="school_phone" class="form-control" value="{{ old('school_phone', $settings->school_phone ?? '') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">School Address</label>
                        <textarea name="school_address" class="form-control" rows="3">{{ old('school_address', $settings->school_address ?? '') }}</textarea>
                    </div>
                    <hr>
                    <h5 class="mb-3">Academic Settings</h5>
                    <div class="mb-3">
                        <label class="form-label">Active Session</label>
                        <select name="active_session_id" class="form-control">
                            <option value="">-- Select Active Session --</option>
                            @foreach($sessions as $session)
                                <option value="{{ $session->id }}" {{ (isset($activeSession) && $activeSession->id == $session->id) ? 'selected' : '' }}>
                                    {{ $session->session_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Active Term</label>
                        <select name="active_term_id" class="form-control">
                            <option value="">-- Select Active Term --</option>
                            @foreach($terms as $term)
                                <option value="{{ $term->id }}" {{ (isset($activeTerm) && $activeTerm->id == $term->id) ? 'selected' : '' }}>
                                    {{ $term->term_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Save Settings</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
