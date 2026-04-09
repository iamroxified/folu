@extends('student.layout')

@section('title', 'My Complaints')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">My Complaints</h1>
    </div>
</div>

@if($complaints->count() > 0)
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Previous Complaints</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($complaints as $complaint)
                                <tr>
                                    <td>{{ $complaint->created_at->format('d/m/Y') }}</td>
                                    <td>{{ ucfirst($complaint->complaint_type) }}</td>
                                    <td>{{ $complaint->subject }}</td>
                                    <td>
                                        <span class="badge bg-{{ $complaint->status == 'resolved' ? 'success' : ($complaint->status == 'in_review' ? 'warning' : ($complaint->status == 'pending' ? 'secondary' : 'danger')) }}">
                                            {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewComplaint({{ $complaint->id }})">View</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h5>Submit New Complaint</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('student.complaints.submit') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="complaint_type" class="form-label">Complaint Type</label>
                        <select class="form-control" id="complaint_type" name="complaint_type" required>
                            <option value="">Select Type</option>
                            <option value="academic">Academic Issue</option>
                            <option value="fee">Fee Related</option>
                            <option value="facility">School Facility</option>
                            <option value="staff">Staff Related</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" required placeholder="Brief description of the issue">
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Detailed Description</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required placeholder="Please provide detailed information about your complaint"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Complaint</button>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5>Important Notes</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li>Please provide as much detail as possible to help us resolve your issue quickly.</li>
                    <li>All complaints are treated confidentially and will be reviewed by the appropriate department.</li>
                    <li>You will receive a response within 2-3 business days.</li>
                    <li>For urgent matters, please contact the school administration directly.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Complaint Detail Modal -->
<div class="modal fade" id="complaintModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Complaint Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="complaintDetails">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewComplaint(complaintId) {
    // In a real application, this would fetch complaint details via AJAX
    // For now, we'll just show an alert
    alert('Complaint details view would be implemented here. Complaint ID: ' + complaintId);
}
</script>
@endsection