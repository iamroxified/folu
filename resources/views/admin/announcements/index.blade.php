@extends('admin.layout')

@section('title', 'Announcements')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Create Announcement</div>
            <div class="card-body">
                <form action="{{ route('admin.announcements.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Audience</label>
                        <select name="audience" class="form-control" required>
                            <option value="all">All</option>
                            <option value="students">Students</option>
                            <option value="teachers">Teachers</option>
                            <option value="admins">Admins</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Session (Optional)</label>
                        <select name="academic_session_id" class="form-control">
                            <option value="">All Sessions</option>
                            @foreach($sessions as $session)
                                <option value="{{ $session->id }}">{{ $session->session_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Body</label>
                        <textarea name="body" class="form-control" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Publish</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Published Announcements</div>
            <div class="card-body">
                @if($announcements->isEmpty())
                    <p>No announcements found.</p>
                @else
                    @foreach($announcements as $ann)
                        <div class="border rounded p-3 mb-3">
                            <h5>{{ $ann->title }} <span class="badge bg-secondary">{{ ucfirst($ann->audience) }}</span></h5>
                            <p class="mb-1">{{ $ann->body }}</p>
                            <small class="text-muted">Published: {{ $ann->published_at->format('M d, Y h:i A') }}</small>
                        </div>
                    @endforeach
                    {{ $announcements->links() }}
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
