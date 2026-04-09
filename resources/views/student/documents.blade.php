@extends('student.layout')

@section('title', 'My Documents')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">My Documents</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Upload Document</h5>
            </div>
            <div class="card-body">
                <form id="documentUploadForm" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="document_type" class="form-label">Document Type</label>
                        <select class="form-control" id="document_type" name="document_type" required>
                            <option value="">Select Type</option>
                            <option value="birth_certificate">Birth Certificate</option>
                            <option value="passport">Passport Photo</option>
                            <option value="medical_report">Medical Report</option>
                            <option value="transcript">Previous Transcript</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="document" class="form-label">Document File</label>
                        <input type="file" class="form-control" id="document" name="document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                        <div class="form-text">Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG. Max size: 2MB</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload Document</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Document Guidelines</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li><strong>Birth Certificate:</strong> Required for new admissions</li>
                    <li><strong>Passport Photo:</strong> Recent passport-sized photo</li>
                    <li><strong>Medical Report:</strong> Recent medical examination report</li>
                    <li><strong>Previous Transcript:</strong> Academic records from previous school</li>
                    <li><strong>Other:</strong> Any additional documents as required</li>
                </ul>
                <div class="alert alert-info mt-3">
                    <strong>Note:</strong> All documents are securely stored and will be reviewed by the administration.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>My Uploaded Documents</h5>
            </div>
            <div class="card-body">
                <div id="documentsList">
                    <p class="text-muted">No documents uploaded yet.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('documentUploadForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;

    submitBtn.disabled = true;
    submitBtn.textContent = 'Uploading...';

    fetch('{{ route("student.upload.document") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Document uploaded successfully!');
            this.reset();
            // In a real application, you'd refresh the documents list
        } else {
            alert('Error: ' + (data.error || 'Upload failed'));
        }
    })
    .catch(error => {
        alert('Error uploading document. Please try again.');
        console.error('Upload error:', error);
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});
</script>
@endsection