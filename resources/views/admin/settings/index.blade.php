<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>School & System Settings - Admin Panel</title>
    @include('admin.partials.links')
</head>
<body>
    <div class="wrapper">
        @include('admin.partials.sidebar')

        <div class="main-panel">
            @include('admin.partials.header')
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pb-3">
                        <div>
                            <h2 class="text-dark pb-1 fw-bold"><i class="fas fa-cogs me-2 text-primary"></i>School & System Settings</h2>
                            <p class="text-muted mb-0">Configure basic preferences, branding, contact details, and site options.</p>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <!-- School Profile & Branding -->
                            <div class="col-md-8">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-primary text-white">
                                        <div class="card-title text-white fw-bold mb-0">
                                            <i class="fas fa-university me-2"></i> School Details & Branding
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label fw-bold">School Name <span class="text-danger">*</span></label>
                                                <input type="text" name="school_name" class="form-control" value="{{ old('school_name', $settings->school_name ?? '') }}" required placeholder="e.g. Excellence International Academy">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">School Email</label>
                                                <input type="email" name="school_email" class="form-control" value="{{ old('school_email', $settings->school_email ?? '') }}" placeholder="info@school.edu">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">School Phone Number</label>
                                                <input type="text" name="school_phone" class="form-control" value="{{ old('school_phone', $settings->school_phone ?? '') }}" placeholder="+234 800 000 0000">
                                            </div>

                                            <div class="col-md-12">
                                                <label class="form-label fw-bold">School Motto / Tagline</label>
                                                <input type="text" name="school_motto" class="form-control" value="{{ old('school_motto', $settings->school_motto ?? '') }}" placeholder="e.g. Knowledge, Integrity and Excellence">
                                            </div>

                                            <div class="col-md-12">
                                                <label class="form-label fw-bold">School Physical Address</label>
                                                <textarea name="school_address" class="form-control" rows="3" placeholder="Enter full address">{{ old('school_address', $settings->school_address ?? '') }}</textarea>
                                            </div>

                                            <div class="col-md-12">
                                                <label class="form-label fw-bold">Principal / Headmaster Name</label>
                                                <input type="text" name="principal_name" class="form-control" value="{{ old('principal_name', $settings->additional_settings['principal_name'] ?? '') }}" placeholder="Dr. Jane Doe">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Announcement / Portal Notice -->
                                <div class="card shadow-sm mt-3">
                                    <div class="card-header bg-dark text-white">
                                        <div class="card-title text-white fw-bold mb-0">
                                            <i class="fas fa-bullhorn me-2"></i> Site Preference & Portal Announcement
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Global Portal Banner / Notice</label>
                                            <textarea name="portal_notice" class="form-control" rows="3" placeholder="Message displayed to staff and students on their portal dashboard...">{{ old('portal_notice', $settings->additional_settings['portal_notice'] ?? '') }}</textarea>
                                            <small class="text-muted">This announcement will be displayed across student and staff dashboards.</small>
                                        </div>

                                        <div class="form-check form-switch mt-3">
                                            <input class="form-check-input" type="checkbox" id="admissions_open" name="admissions_open" value="1" {{ !empty($settings->additional_settings['admissions_open']) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="admissions_open">
                                                Enable Online Admissions / Public Registration
                                            </label>
                                            <div class="text-muted small">Allow prospective students to fill application forms online.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Logo & Localization Sidebar -->
                            <div class="col-md-4">
                                <!-- Logo Card -->
                                <div class="card shadow-sm">
                                    <div class="card-header bg-secondary text-white">
                                        <div class="card-title text-white fw-bold mb-0">
                                            <i class="fas fa-image me-2"></i> School Logo
                                        </div>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            @if(!empty($settings->school_logo))
                                                <img src="{{ asset('storage/' . $settings->school_logo) }}" alt="School Logo" class="img-thumbnail rounded shadow-sm" style="max-height: 140px; object-fit: contain;">
                                            @else
                                                <img src="/admin/assets/img/folu_logo.png" alt="Default Logo" class="img-thumbnail rounded shadow-sm" style="max-height: 140px; object-fit: contain;">
                                            @endif
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label fw-bold">Upload New Logo</label>
                                            <input type="file" name="school_logo" class="form-control" accept="image/*">
                                            <small class="text-muted d-block mt-1">Recommended format: PNG/JPG (Max 2MB)</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Regional Settings -->
                                <div class="card shadow-sm mt-3">
                                    <div class="card-header bg-info text-white">
                                        <div class="card-title text-white fw-bold mb-0">
                                            <i class="fas fa-globe me-2"></i> Localization Settings
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">System Currency <span class="text-danger">*</span></label>
                                            <select name="currency" class="form-select" required>
                                                <option value="NGN" {{ old('currency', $settings->currency ?? 'NGN') === 'NGN' ? 'selected' : '' }}>Nigerian Naira (₦ - NGN)</option>
                                                <option value="USD" {{ old('currency', $settings->currency ?? '') === 'USD' ? 'selected' : '' }}>US Dollar ($ - USD)</option>
                                                <option value="GBP" {{ old('currency', $settings->currency ?? '') === 'GBP' ? 'selected' : '' }}>British Pound (£ - GBP)</option>
                                                <option value="EUR" {{ old('currency', $settings->currency ?? '') === 'EUR' ? 'selected' : '' }}>Euro (€ - EUR)</option>
                                                <option value="GHS" {{ old('currency', $settings->currency ?? '') === 'GHS' ? 'selected' : '' }}>Ghanaian Cedi (₵ - GHS)</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Timezone <span class="text-danger">*</span></label>
                                            <select name="timezone" class="form-select" required>
                                                <option value="Africa/Lagos" {{ old('timezone', $settings->timezone ?? 'Africa/Lagos') === 'Africa/Lagos' ? 'selected' : '' }}>Africa/Lagos (GMT+1)</option>
                                                <option value="Africa/Accra" {{ old('timezone', $settings->timezone ?? '') === 'Africa/Accra' ? 'selected' : '' }}>Africa/Accra (GMT+0)</option>
                                                <option value="UTC" {{ old('timezone', $settings->timezone ?? '') === 'UTC' ? 'selected' : '' }}>UTC (GMT+0)</option>
                                                <option value="Europe/London" {{ old('timezone', $settings->timezone ?? '') === 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                                                <option value="America/New_York" {{ old('timezone', $settings->timezone ?? '') === 'America/New_York' ? 'selected' : '' }}>America/New York</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Save Button Card -->
                                <div class="card shadow-sm mt-3">
                                    <div class="card-body">
                                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">
                                            <i class="fas fa-save me-2"></i> Save Settings
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
            @include('admin.partials.footer')
        </div>
    </div>
</body>
</html>
