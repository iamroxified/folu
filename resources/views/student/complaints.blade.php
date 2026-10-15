@extends('student.layout')

@section('title', 'Support & Complaints - ' . ($student->first_name ?? 'Student'))

@section('content')
<!-- Page Header & Breadcrumbs -->
<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Helpdesk & Support Inquiries</h3>
        <ul class="breadcrumbs ps-0 mb-0 d-flex align-items-center text-muted" style="list-style: none; font-size: 13px;">
            <li class="nav-home"><a href="{{ route('student.dashboard') }}" class="text-muted"><i class="fas fa-home"></i></a></li>
            <li class="separator mx-2"><i class="fas fa-chevron-right text-muted" style="font-size: 10px;"></i></li>
            <li class="nav-item text-dark fw-semibold">Student Complaints & Helpdesk</li>
        </ul>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Submit Complaint Form -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                <div class="rounded-circle text-white p-2 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #720922, #8e1532); width: 34px; height: 34px;">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <h5 class="card-title fw-bold mb-0 text-dark" style="font-size: 16px;">Submit New Inquiry or Complaint</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('student.complaints.submit') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="complaint_type" class="form-label fw-semibold text-dark small">Complaint Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="complaint_type" name="complaint_type" required>
                                <option value="">Select Category</option>
                                <option value="academic">Academic & Curriculum Issue</option>
                                <option value="fee">School Fees & Billing Inquiry</option>
                                <option value="facility">Classroom & School Facility</option>
                                <option value="staff">Teacher / Staff Assistance</option>
                                <option value="examination">Examination & Result Discrepancy</option>
                                <option value="other">Other Inquiry</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="subject" class="form-label fw-semibold text-dark small">Subject / Brief Summary <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject" name="subject" required placeholder="e.g. Issue with Term 1 Math score" />
                        </div>

                        <div class="col-12">
                            <label for="message" class="form-label fw-semibold text-dark small">Detailed Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="5" required placeholder="Provide clear information including relevant subjects, dates, or specific concerns to help us resolve this promptly..."></textarea>
                        </div>

                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary btn-round px-4 shadow-sm">
                                <i class="fas fa-paper-plane me-1"></i> Submit to Administration
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Support Guidelines Card -->
        <div class="card shadow-sm border-0 rounded-4 bg-light">
            <div class="card-body p-4">
                <h6 class="fw-bold text-dark mb-2">
                    <i class="fas fa-info-circle text-primary me-2"></i> Important Information & Resolution Process
                </h6>
                <ul class="text-muted small ps-3 mb-0" style="line-height: 1.8;">
                    <li>Inquiries are securely dispatched directly to the school principal and academic coordinators.</li>
                    <li>All submissions are handled with strict student-administrator confidentiality.</li>
                    <li>Standard resolution turnaround time is <strong>1 to 2 school days</strong>.</li>
                    <li>For immediate emergency or medical situations, please notify the school clinic or principal's office directly.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Right Column: Previous Complaints & Status -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-warning-subtle text-warning p-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                        <i class="fas fa-history"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-0 text-dark" style="font-size: 16px;">My Submitted Tickets</h5>
                </div>
                <span class="badge bg-light text-dark border">{{ $complaints->count() }} Total</span>
            </div>

            <div class="card-body p-3">
                @forelse($complaints as $c)
                    @php
                        $stBadge = match($c->status) {
                            'resolved' => 'bg-success',
                            'in_review', 'under_review' => 'bg-warning text-dark',
                            default => 'bg-secondary'
                        };
                    @endphp
                    <div class="p-3 mb-3 rounded-3 border bg-light transition-all">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge {{ $stBadge }} px-2 py-1" style="font-size: 10px;">
                                {{ ucfirst(str_replace('_', ' ', $c->status)) }}
                            </span>
                            <span class="text-muted small">
                                <i class="fas fa-clock me-1"></i> {{ $c->created_at->format('d/m/Y') }}
                            </span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">{{ $c->subject }}</h6>
                        <span class="badge bg-white text-muted border px-2 py-0 mb-2" style="font-size: 10px;">
                            {{ ucfirst($c->complaint_type) }}
                        </span>
                        <p class="text-muted small mb-2" style="font-size: 12px; line-height: 1.5;">
                            {{ Str::limit($c->message, 120) }}
                        </p>

                        @if(!empty($c->admin_response))
                            <div class="p-2 rounded bg-success-subtle border border-success-subtle mt-2">
                                <span class="fw-bold text-success d-block small mb-1">
                                    <i class="fas fa-reply me-1"></i> Admin Response:
                                </span>
                                <span class="text-dark small" style="font-size: 12px;">{{ $c->admin_response }}</span>
                            </div>
                        @else
                            <div class="text-muted small fst-italic" style="font-size: 11px;">
                                <i class="fas fa-hourglass-half me-1"></i> Under administrative review...
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-4">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-2 bg-light">
                            <i class="fas fa-check text-muted fa-lg"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">No Tickets Filed</h6>
                        <p class="text-muted small mb-0">You do not have any pending or previous support tickets.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection