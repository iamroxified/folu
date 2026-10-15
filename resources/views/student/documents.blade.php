@extends('student.layout')

@section('title', 'My Documents - ' . ($student->first_name ?? 'Student'))

@section('content')
<!-- Page Header & Breadcrumbs -->
<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Document Repository</h3>
        <ul class="breadcrumbs ps-0 mb-0 d-flex align-items-center text-muted" style="list-style: none; font-size: 13px;">
            <li class="nav-home"><a href="{{ route('student.dashboard') }}" class="text-muted"><i class="fas fa-home"></i></a></li>
            <li class="separator mx-2"><i class="fas fa-chevron-right text-muted" style="font-size: 10px;"></i></li>
            <li class="nav-item text-dark fw-semibold">Academic & Admission Documents</li>
        </ul>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Upload Form & Guidelines -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                <div class="rounded-circle text-white p-2 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #720922, #8e1532); width: 34px; height: 34px;">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <h5 class="card-title fw-bold mb-0 text-dark" style="font-size: 16px;">Upload Student Document</h5>
            </div>
            <div class="card-body p-4">
                <div id="uploadAlert" class="alert d-none mb-3"></div>

                <form id="documentUploadForm" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="document_type" class="form-label fw-semibold text-dark small">Document Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="document_type" name="document_type" required>
                            <option value="">Select Category</option>
                            <option value="birth_certificate">Birth Certificate / Age Declaration</option>
                            <option value="passport">Recent Passport Photograph</option>
                            <option value="medical_report">Medical / Fitness Certificate</option>
                            <option value="transcript">Previous School Transcript / Report Sheet</option>
                            <option value="immunization">Immunization & Vaccine Record</option>
                            <option value="other">Other Official Document</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="document" class="form-label fw-semibold text-dark small">Select File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="document" name="document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required />
                        <div class="form-text text-muted" style="font-size: 11px;">
                            Accepted file extensions: PDF, DOCX, JPG, PNG. Maximum allowed file size: 4MB.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label fw-semibold text-dark small">Optional Notes / Remarks</label>
                        <input type="text" class="form-control" id="notes" name="notes" placeholder="e.g. Original issued by General Hospital" />
                    </div>

                    <button type="submit" id="submitUploadBtn" class="btn btn-primary btn-round w-100 shadow-sm py-2">
                        <i class="fas fa-upload me-1"></i> Upload to Secure Archive
                    </button>
                </form>
            </div>
        </div>

        <!-- Document Guidelines Card -->
        <div class="card shadow-sm border-0 rounded-4 bg-light">
            <div class="card-body p-4">
                <h6 class="fw-bold text-dark mb-2" style="font-size: 13px;">
                    <i class="fas fa-info-circle text-primary me-2"></i> Document Verification Requirements
                </h6>
                <ul class="text-muted small ps-3 mb-0" style="line-height: 1.8;">
                    <li>Ensure scanned copies are clear, legible, and uncorrupted.</li>
                    <li>Files are verified by the registry prior to admission file completion.</li>
                    <li>All records remain encrypted and confidential within the school SIS.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Right Column: Document Archive List -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-0 text-dark" style="font-size: 16px;">Archived Student Documents</h5>
                </div>
                <span class="badge bg-light text-dark border">{{ $documents->count() }} Files</span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Document Type</th>
                                <th>File Name</th>
                                <th>Uploaded Date</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($documents as $doc)
                                @php
                                    $docLabel = match($doc->document_type) {
                                        'birth_certificate' => 'Birth Certificate',
                                        'passport' => 'Passport Photo',
                                        'medical_report' => 'Medical Report',
                                        'transcript' => 'Academic Transcript',
                                        'immunization' => 'Immunization Record',
                                        default => ucfirst(str_replace('_', ' ', $doc->document_type))
                                    };
                                    $iconClass = match(strtolower(pathinfo($doc->file_name ?? $doc->file_path, PATHINFO_EXTENSION))) {
                                        'pdf' => 'fa-file-pdf text-danger',
                                        'doc', 'docx' => 'fa-file-word text-primary',
                                        'jpg', 'jpeg', 'png' => 'fa-file-image text-success',
                                        default => 'fa-file-alt text-secondary'
                                    };
                                    $fileUrl = !empty($doc->file_path) ? '/storage/' . ltrim($doc->file_path, '/') : '#';
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas {{ $iconClass }} fa-lg"></i>
                                            <div>
                                                <span class="fw-bold text-dark d-block small">{{ $docLabel }}</span>
                                                @if(!empty($doc->notes))
                                                    <span class="text-muted" style="font-size: 11px;">{{ $doc->notes }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block small text-dark" style="max-width: 180px;">
                                            {{ $doc->file_name ?: basename($doc->file_path) }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        {{ !empty($doc->upload_date) ? \Carbon\Carbon::parse($doc->upload_date)->format('d/m/Y') : ($doc->created_at ? \Carbon\Carbon::parse($doc->created_at)->format('d/m/Y') : 'N/A') }}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ $fileUrl }}" target="_blank" class="btn btn-outline-primary btn-sm btn-round px-3">
                                            <i class="fas fa-eye me-1"></i> View File
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-2 bg-light">
                                            <i class="fas fa-folder-plus text-muted fa-lg"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">No Documents Uploaded</h6>
                                        <p class="text-muted small mb-0">Use the form on the left to upload your academic credentials and certificates.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('documentUploadForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = document.getElementById('submitUploadBtn');
    const alertBox = document.getElementById('uploadAlert');
    const originalText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Uploading document...';
    alertBox.className = 'alert d-none mb-3';

    fetch('{{ route("student.upload.document") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;

        if (data.success) {
            alertBox.className = 'alert alert-success mb-3';
            alertBox.innerHTML = '<i class="fas fa-check-circle me-1"></i> ' + (data.message || 'Document uploaded successfully!');
            document.getElementById('documentUploadForm').reset();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            alertBox.className = 'alert alert-danger mb-3';
            alertBox.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> ' + (data.error || 'Failed to upload document. Please check the file and try again.');
        }
    })
    .catch(err => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        alertBox.className = 'alert alert-danger mb-3';
        alertBox.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> Network error occurred during upload. Please try again.';
    });
});
</script>
@endsection