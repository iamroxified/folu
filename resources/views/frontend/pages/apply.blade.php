<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Apply for Admission | FOLU INTERNATIONAL GROUP OF SCHOOLS | Isanlu, Kogi State</title>
  <meta name="description" content="Apply online or submit an admission application for Creche, Nursery, Primary, and Secondary education at Folu International Group of Schools, Itedo-Ijowa, Isanlu, Kogi State.">
  <meta name="keywords" content="Folu International Schools Admission, Apply Folu School Isanlu, Creche Nursery Primary Secondary Admission Kogi State, Isanlu School Enrolment">

  <!-- OpenGraph / Social Metadata -->
  <meta property="og:title" content="Apply for Admission | Folu International Group of Schools">
  <meta property="og:description" content="Begin your child's journey to academic excellence and moral leadership. Apply online today.">
  <meta property="og:image" content="{{ asset('images/folu-logo.png') }}">
  <meta property="og:type" content="website">

  <!-- Favicon & Icons -->
  <link rel="icon" href="{{ asset('images/folu-logo.png') }}" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Modern Folu 2026 Stylesheet -->
  <link rel="stylesheet" href="{{ asset('css/folu-modern.css') }}" type="text/css" media="all">
</head>

<body class="folu-theme">

  {{-- Header with verified info & navigation --}}
  @include('frontend.partials.header')

  <!-- 1. PAGE HEADER BANNER -->
  <header class="folu-page-header">
    <div class="folu-container">
      <div class="folu-page-header-content">
        <nav class="folu-breadcrumb" aria-label="Breadcrumb">
          <a href="{{ url('/') }}">Home</a>
          <span class="sep">/</span>
          <a href="{{ url('/admission-process') }}">Admissions</a>
          <span class="sep">/</span>
          <span class="current">Apply</span>
        </nav>
        <h1 class="folu-page-title">Begin Your Child's Journey at Folu</h1>
        <p class="folu-page-subtitle">
          Admissions are open across Creche, Nursery, Primary, and Secondary classes. Complete our official student application form below. All data fields align directly with our administrative student enrollment records.
        </p>
      </div>
    </div>
  </header>

  <!-- 2. APPLICATION SUITE & FORM -->
  <section class="folu-section">
    <div class="folu-container">
      <div class="folu-apply-layout">

        <!-- Left: Admission Application Form -->
        <div class="folu-form-card">
          <div style="margin-bottom: 24px;">
            <span class="folu-section-badge">Online Admission Portal</span>
            <h2 class="folu-form-title">Student Registration &amp; Enrolment Form</h2>
            <p class="folu-form-subtitle" style="margin-bottom: 0;">
              Please complete each required section carefully. Once submitted, our admissions office will review your application and contact you within 24 to 48 hours for assessment and placement.
            </p>
          </div>

          @if(session('success'))
            <div class="alert alert-success" style="background: #f0fdf4; border: 1.5px solid #22c55e; color: #15803d; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px;">
              <i class="fa-solid fa-circle-check" style="font-size: 18px; margin-right: 8px;"></i>
              <strong>{{ session('success') }}</strong>
            </div>
          @endif

          @if($errors->any())
            <div class="alert alert-danger" style="background: #fef2f2; border: 1.5px solid #ef4444; color: #b91c1c; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px;">
              <i class="fa-solid fa-circle-exclamation" style="font-size: 18px; margin-right: 8px;"></i>
              <strong>Please check the following errors:</strong>
              <ul style="margin: 8px 0 0 20px;">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form id="foluAdmissionForm" method="POST" action="{{ route('apply.submit') }}">
            @csrf

            <!-- Section 1: Academic Level & Class -->
            <div class="folu-form-section">
              <div class="folu-form-section-header">
                <span class="folu-form-section-num">1</span>
                <h3 class="folu-form-section-title">Academic Level &amp; Proposed Class</h3>
              </div>

              <!-- School Level Selection -->
              <div class="folu-form-group">
                <label class="folu-form-label">Select School Level <span class="req">*</span></label>
                <div class="folu-form-radio-group">
                  <div class="folu-form-radio-card">
                    <input type="radio" id="lvl_creche" name="school_level" value="Creche" {{ old('school_level') === 'Creche' ? 'checked' : '' }} required>
                    <label for="lvl_creche" class="folu-form-radio-label">Creche</label>
                  </div>
                  <div class="folu-form-radio-card">
                    <input type="radio" id="lvl_nursery" name="school_level" value="Nursery" {{ old('school_level') === 'Nursery' ? 'checked' : '' }}>
                    <label for="lvl_nursery" class="folu-form-radio-label">Nursery</label>
                  </div>
                  <div class="folu-form-radio-card">
                    <input type="radio" id="lvl_primary" name="school_level" value="Primary" {{ old('school_level', 'Primary') === 'Primary' ? 'checked' : '' }}>
                    <label for="lvl_primary" class="folu-form-radio-label">Primary</label>
                  </div>
                  <div class="folu-form-radio-card">
                    <input type="radio" id="lvl_secondary" name="school_level" value="Secondary" {{ old('school_level') === 'Secondary' ? 'checked' : '' }}>
                    <label for="lvl_secondary" class="folu-form-radio-label">Secondary</label>
                  </div>
                </div>
              </div>

              <!-- Proposed Class -->
              <div class="folu-form-group" style="margin-bottom: 0;">
                <label for="target_class" class="folu-form-label">Proposed Class of Entry <span class="req">*</span></label>
                <select id="target_class" name="target_class" class="folu-form-select" required>
                  <option value="">-- Choose Proposed Class --</option>
                  <optgroup label="Early Years &amp; Nursery">
                    <option value="Creche / Playgroup" {{ old('target_class') === 'Creche / Playgroup' ? 'selected' : '' }}>Creche / Playgroup</option>
                    <option value="Pre-Nursery" {{ old('target_class') === 'Pre-Nursery' ? 'selected' : '' }}>Pre-Nursery</option>
                    <option value="Nursery 1" {{ old('target_class') === 'Nursery 1' ? 'selected' : '' }}>Nursery 1</option>
                    <option value="Nursery 2" {{ old('target_class') === 'Nursery 2' ? 'selected' : '' }}>Nursery 2</option>
                    <option value="Nursery 3" {{ old('target_class') === 'Nursery 3' ? 'selected' : '' }}>Nursery 3</option>
                  </optgroup>
                  <optgroup label="Primary School">
                    <option value="Primary 1" {{ old('target_class') === 'Primary 1' ? 'selected' : '' }}>Primary 1 (Basic 1)</option>
                    <option value="Primary 2" {{ old('target_class') === 'Primary 2' ? 'selected' : '' }}>Primary 2 (Basic 2)</option>
                    <option value="Primary 3" {{ old('target_class') === 'Primary 3' ? 'selected' : '' }}>Primary 3 (Basic 3)</option>
                    <option value="Primary 4" {{ old('target_class') === 'Primary 4' ? 'selected' : '' }}>Primary 4 (Basic 4)</option>
                    <option value="Primary 5" {{ old('target_class') === 'Primary 5' ? 'selected' : '' }}>Primary 5 (Basic 5)</option>
                    <option value="Primary 6" {{ old('target_class') === 'Primary 6' ? 'selected' : '' }}>Primary 6 (Basic 6)</option>
                  </optgroup>
                  <optgroup label="Secondary School (College)">
                    <option value="JSS 1" {{ old('target_class') === 'JSS 1' ? 'selected' : '' }}>Junior Secondary 1 (JSS 1)</option>
                    <option value="JSS 2" {{ old('target_class') === 'JSS 2' ? 'selected' : '' }}>Junior Secondary 2 (JSS 2)</option>
                    <option value="JSS 3" {{ old('target_class') === 'JSS 3' ? 'selected' : '' }}>Junior Secondary 3 (JSS 3)</option>
                    <option value="SSS 1" {{ old('target_class') === 'SSS 1' ? 'selected' : '' }}>Senior Secondary 1 (SSS 1)</option>
                    <option value="SSS 2" {{ old('target_class') === 'SSS 2' ? 'selected' : '' }}>Senior Secondary 2 (SSS 2)</option>
                    <option value="SSS 3" {{ old('target_class') === 'SSS 3' ? 'selected' : '' }}>Senior Secondary 3 (SSS 3)</option>
                  </optgroup>
                </select>
              </div>
            </div>

            <!-- Section 2: Student Personal & Demographics (Harmonized with Admin Add Student) -->
            <div class="folu-form-section">
              <div class="folu-form-section-header">
                <span class="folu-form-section-num">2</span>
                <h3 class="folu-form-section-title">Student Personal &amp; Demographic Details</h3>
              </div>

              <!-- First Name & Surname -->
              <div class="folu-form-grid-2">
                <div class="folu-form-group">
                  <label for="first_name" class="folu-form-label">First Name <span class="req">*</span></label>
                  <input type="text" id="first_name" name="first_name" class="folu-form-control" placeholder="e.g. Emmanuel" value="{{ old('first_name') }}" required>
                </div>
                <div class="folu-form-group">
                  <label for="last_name" class="folu-form-label">Last Name / Surname <span class="req">*</span></label>
                  <input type="text" id="last_name" name="last_name" class="folu-form-control" placeholder="e.g. Babatunde" value="{{ old('last_name') }}" required>
                </div>
              </div>

              <!-- Other Names & Date of Birth -->
              <div class="folu-form-grid-2">
                <div class="folu-form-group">
                  <label for="other_names" class="folu-form-label">Middle / Other Names</label>
                  <input type="text" id="other_names" name="other_names" class="folu-form-control" placeholder="e.g. Oluwaseun (Optional)" value="{{ old('other_names') }}">
                </div>
                <div class="folu-form-group">
                  <label for="date_of_birth" class="folu-form-label">Date of Birth <span class="req">*</span></label>
                  <input type="date" id="date_of_birth" name="date_of_birth" class="folu-form-control" value="{{ old('date_of_birth') }}" required>
                </div>
              </div>

              <!-- Gender & Student Type -->
              <div class="folu-form-grid-2">
                <div class="folu-form-group">
                  <label for="gender" class="folu-form-label">Gender <span class="req">*</span></label>
                  <select id="gender" name="gender" class="folu-form-select" required>
                    <option value="">-- Select Gender --</option>
                    <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                  </select>
                </div>
                <div class="folu-form-group">
                  <label for="student_type" class="folu-form-label">Student Type <span class="req">*</span></label>
                  <select id="student_type" name="student_type" class="folu-form-select" required>
                    <option value="day" {{ old('student_type', 'day') === 'day' ? 'selected' : '' }}>Day Student</option>
                    <option value="boarding" {{ old('student_type') === 'boarding' ? 'selected' : '' }}>Boarding Student</option>
                  </select>
                </div>
              </div>

              <!-- State of Origin & LGA (Harmonized with Admin dropdowns) -->
              <div class="folu-form-grid-2">
                <div class="folu-form-group">
                  <label for="state_of_origin" class="folu-form-label">State of Origin <span class="req">*</span></label>
                  <select id="state_of_origin" name="state_of_origin" class="folu-form-select" required onchange="fetchLgasForState(this)">
                    <option value="">-- Select State --</option>
                    @php
                      $statesList = ['Abia','Adamawa','Akwa Ibom','Anambra','Bauchi','Bayelsa','Benue','Borno','Cross River','Delta','Ebonyi','Edo','Ekiti','Enugu','FCT - Abuja','Gombe','Imo','Jigawa','Kaduna','Kano','Katsina','Kebbi','Kogi','Kwara','Lagos','Nasarawa','Niger','Ogun','Ondo','Osun','Oyo','Plateau','Rivers','Sokoto','Taraba','Yobe','Zamfara'];
                    @endphp
                    @foreach($statesList as $idx => $st)
                      <option value="{{ $st }}" data-id="{{ $idx + 1 }}" {{ old('state_of_origin', 'Kogi') === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="folu-form-group">
                  <label for="lga" class="folu-form-label">LGA (Local Govt Area) <span class="req">*</span></label>
                  <select id="lga" name="lga" class="folu-form-select" required>
                    <option value="">Select State First</option>
                  </select>
                </div>
              </div>

              <!-- Student Personal Email & Medical Details -->
              <div class="folu-form-grid-3" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 0;">
                <div class="folu-form-group" style="margin-bottom: 0;">
                  <label for="student_email" class="folu-form-label">Student Email (Optional)</label>
                  <input type="email" id="student_email" name="student_email" class="folu-form-control" placeholder="e.g. student@example.com" value="{{ old('student_email') }}">
                </div>
                <div class="folu-form-group" style="margin-bottom: 0;">
                  <label for="blood_group" class="folu-form-label">Blood Group</label>
                  <select id="blood_group" name="blood_group" class="folu-form-select">
                    <option value="">-- Select --</option>
                    <option value="A+" {{ old('blood_group') === 'A+' ? 'selected' : '' }}>A+</option>
                    <option value="A-" {{ old('blood_group') === 'A-' ? 'selected' : '' }}>A-</option>
                    <option value="B+" {{ old('blood_group') === 'B+' ? 'selected' : '' }}>B+</option>
                    <option value="B-" {{ old('blood_group') === 'B-' ? 'selected' : '' }}>B-</option>
                    <option value="AB+" {{ old('blood_group') === 'AB+' ? 'selected' : '' }}>AB+</option>
                    <option value="AB-" {{ old('blood_group') === 'AB-' ? 'selected' : '' }}>AB-</option>
                    <option value="O+" {{ old('blood_group') === 'O+' ? 'selected' : '' }}>O+</option>
                    <option value="O-" {{ old('blood_group') === 'O-' ? 'selected' : '' }}>O-</option>
                  </select>
                </div>
                <div class="folu-form-group" style="margin-bottom: 0;">
                  <label for="genotype" class="folu-form-label">Genotype</label>
                  <select id="genotype" name="genotype" class="folu-form-select">
                    <option value="">-- Select --</option>
                    <option value="AA" {{ old('genotype') === 'AA' ? 'selected' : '' }}>AA</option>
                    <option value="AS" {{ old('genotype') === 'AS' ? 'selected' : '' }}>AS</option>
                    <option value="SS" {{ old('genotype') === 'SS' ? 'selected' : '' }}>SS</option>
                    <option value="AC" {{ old('genotype') === 'AC' ? 'selected' : '' }}>AC</option>
                    <option value="SC" {{ old('genotype') === 'SC' ? 'selected' : '' }}>SC</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Section 3: Parent / Guardian Information (Harmonized with Admin Add Student) -->
            <div class="folu-form-section">
              <div class="folu-form-section-header">
                <span class="folu-form-section-num">3</span>
                <h3 class="folu-form-section-title">Parent / Guardian Contact Information</h3>
              </div>

              <!-- Parent Name & Relationship -->
              <div class="folu-form-grid-2">
                <div class="folu-form-group">
                  <label for="parent_name" class="folu-form-label">Parent / Guardian Full Name <span class="req">*</span></label>
                  <input type="text" id="parent_name" name="parent_name" class="folu-form-control" placeholder="e.g. Mr. / Mrs. Babaniyi" value="{{ old('parent_name') }}" required>
                </div>
                <div class="folu-form-group">
                  <label for="parent_relationship" class="folu-form-label">Relationship to Child <span class="req">*</span></label>
                  <select id="parent_relationship" name="parent_relationship" class="folu-form-select" required>
                    <option value="father" {{ old('parent_relationship') === 'father' ? 'selected' : '' }}>Father</option>
                    <option value="mother" {{ old('parent_relationship') === 'mother' ? 'selected' : '' }}>Mother</option>
                    <option value="guardian" {{ old('parent_relationship', 'guardian') === 'guardian' ? 'selected' : '' }}>Legal Guardian</option>
                    <option value="other" {{ old('parent_relationship') === 'other' ? 'selected' : '' }}>Other</option>
                  </select>
                </div>
              </div>

              <!-- Parent Phone & WhatsApp -->
              <div class="folu-form-grid-2">
                <div class="folu-form-group">
                  <label for="parent_phone" class="folu-form-label">Primary Phone Number <span class="req">*</span></label>
                  <input type="tel" id="parent_phone" name="parent_phone" class="folu-form-control" placeholder="e.g. 08012345678" value="{{ old('parent_phone') }}" required>
                </div>
                <div class="folu-form-group">
                  <label for="whatsapp" class="folu-form-label">WhatsApp Number</label>
                  <input type="tel" id="whatsapp" name="whatsapp" class="folu-form-control" placeholder="e.g. 08165354191" value="{{ old('whatsapp') }}">
                </div>
              </div>

              <!-- Parent Email (Required to match admin validation) & Address -->
              <div class="folu-form-grid-2">
                <div class="folu-form-group" style="margin-bottom: 0;">
                  <label for="parent_email" class="folu-form-label">Parent Email Address <span class="req">*</span></label>
                  <input type="email" id="parent_email" name="parent_email" class="folu-form-control" placeholder="e.g. parent@example.com" value="{{ old('parent_email') }}" required>
                </div>
                <div class="folu-form-group" style="margin-bottom: 0;">
                  <label for="home_address" class="folu-form-label">Residential / Home Address <span class="req">*</span></label>
                  <input type="text" id="home_address" name="home_address" class="folu-form-control" placeholder="e.g. Itedo-Ijowa, Isanlu, Kogi State" value="{{ old('home_address') }}" required>
                </div>
              </div>
            </div>

            <!-- Section 4: Educational History & Special Needs -->
            <div class="folu-form-section">
              <div class="folu-form-section-header">
                <span class="folu-form-section-num">4</span>
                <h3 class="folu-form-section-title">Educational History &amp; Additional Information</h3>
              </div>

              <div class="folu-form-group">
                <label for="prev_school" class="folu-form-label">Previous School Attended (If applicable)</label>
                <input type="text" id="prev_school" name="prev_school" class="folu-form-control" placeholder="Name and location of previous school" value="{{ old('prev_school') }}">
              </div>

              <div class="folu-form-group" style="margin-bottom: 0;">
                <label for="additional_notes" class="folu-form-label">Special Needs, Talents, or Questions</label>
                <textarea id="additional_notes" name="additional_notes" class="folu-form-textarea" placeholder="Tell us any specific learning requirements, medical considerations, or questions you have for our team...">{{ old('additional_notes') }}</textarea>
              </div>
            </div>

            <button type="submit" class="folu-btn folu-btn-primary" style="width: 100%; padding: 16px; font-size: 16px;">
              Submit Admission Application <i class="fa-solid fa-arrow-right" style="margin-left: 8px;"></i>
            </button>
            <p class="folu-form-hint" style="text-align: center; margin-top: 10px;">
              Need urgent assistance? Call our admissions lines: <strong>08165354191</strong> or <strong>08057421037</strong>.
            </p>
          </form>
        </div>

        <!-- Right: Admissions Guide & Checklist Sidebar -->
        <div class="folu-apply-sidebar">
          <!-- Required Documents Box -->
          <div style="background: var(--folu-surface); border-radius: var(--folu-radius-md); border: 1px solid var(--folu-border); padding: 28px 24px; box-shadow: var(--folu-shadow-sm);">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
              <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--folu-gold-light); color: var(--folu-gold); display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="fa-solid fa-folder-open"></i>
              </div>
              <h3 style="font-size: 18px; font-weight: 700; color: var(--folu-navy); margin: 0;">Required Documents</h3>
            </div>
            <p style="font-size: 13.5px; color: var(--folu-text-muted); line-height: 1.5; margin-bottom: 16px;">
              Please have the following documents ready for submission upon invitation to the school campus:
            </p>
            <ul class="folu-checklist">
              <li><i class="fa-solid fa-circle-check"></i> Child's Birth Certificate or Declaration of Age</li>
              <li><i class="fa-solid fa-circle-check"></i> 2 recent passport photographs of the student</li>
              <li><i class="fa-solid fa-circle-check"></i> Most recent term report card / transfer certificate</li>
              <li><i class="fa-solid fa-circle-check"></i> Medical immunization / health clearance record</li>
              <li><i class="fa-solid fa-circle-check"></i> 1 passport photograph of each parent/guardian</li>
            </ul>
          </div>

          <!-- Admission Process Card -->
          <div style="background: var(--folu-surface); border-radius: var(--folu-radius-md); border: 1px solid var(--folu-border); padding: 28px 24px; box-shadow: var(--folu-shadow-sm);">
            <h3 style="font-size: 18px; font-weight: 700; color: var(--folu-navy); margin-bottom: 14px;">Admission Steps</h3>
            <div class="folu-timeline" style="gap: 18px; padding-left: 30px;">
              <div class="folu-timeline-step">
                <div class="folu-timeline-dot" style="width: 24px; height: 24px; left: -30px; font-size: 11px;">1</div>
                <div>
                  <h4 style="font-size: 14.5px; font-weight: 700; margin-bottom: 2px;">Apply Online</h4>
                  <p style="font-size: 12.5px; color: var(--folu-text-muted); margin: 0;">Fill and submit the harmonized enrolment form.</p>
                </div>
              </div>
              <div class="folu-timeline-step">
                <div class="folu-timeline-dot" style="width: 24px; height: 24px; left: -30px; font-size: 11px;">2</div>
                <div>
                  <h4 style="font-size: 14.5px; font-weight: 700; margin-bottom: 2px;">Assessment / Interview</h4>
                  <p style="font-size: 12.5px; color: var(--folu-text-muted); margin: 0;">Friendly placement evaluation to determine proper class placement.</p>
                </div>
              </div>
              <div class="folu-timeline-step">
                <div class="folu-timeline-dot" style="width: 24px; height: 24px; left: -30px; font-size: 11px;">3</div>
                <div>
                  <h4 style="font-size: 14.5px; font-weight: 700; margin-bottom: 2px;">Admission Offer</h4>
                  <p style="font-size: 12.5px; color: var(--folu-text-muted); margin: 0;">Official admission letter issued &amp; student portal login generated.</p>
                </div>
              </div>
            </div>
            <div style="margin-top: 20px;">
              <a href="{{ url('/login') }}" style="font-size: 13.5px; font-weight: 600; color: var(--folu-blue); text-decoration: none;">
                Already admitted? Sign in to Portal &rarr;
              </a>
            </div>
          </div>

          <!-- Direct Admissions Help Desk -->
          <div style="background: var(--folu-navy); color: #ffffff; border-radius: var(--folu-radius-md); padding: 28px 24px; box-shadow: var(--folu-shadow-md);">
            <span class="folu-section-badge" style="background: rgba(231, 111, 81, 0.2); color: var(--folu-gold-accent); margin-bottom: 8px;">Direct Assistance</span>
            <h3 style="font-size: 18px; font-weight: 700; color: #ffffff; margin-bottom: 10px;">Admissions Help Desk</h3>
            <p style="font-size: 13.5px; color: #cbd5e1; line-height: 1.5; margin-bottom: 18px;">
              Have questions regarding school fees, bus routes, or uniform requirements? Our admissions team is available to help.
            </p>
            <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
              <a href="tel:08165354191" style="color: #ffffff; text-decoration: none; font-size: 14.5px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-phone" style="color: var(--folu-gold);"></i> 08165354191
              </a>
              <a href="tel:08057421037" style="color: #ffffff; text-decoration: none; font-size: 14.5px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-phone" style="color: var(--folu-gold);"></i> 08057421037
              </a>
              <a href="mailto:info@foluinternationalschools.sch.ng" style="color: #cbd5e1; text-decoration: none; font-size: 13.5px; display: flex; align-items: center; gap: 8px; word-break: break-all;">
                <i class="fa-solid fa-envelope" style="color: var(--folu-gold);"></i> info@foluinternationalschools.sch.ng
              </a>
            </div>
            <a href="https://wa.me/2348165354191?text=Hello%20Folu%20International%20Schools,%20I%20want%20to%20apply%20for%20admission." target="_blank" rel="noopener noreferrer" class="folu-btn folu-btn-primary" style="width: 100%; justify-content: center; background: #22c55e; border-color: #22c55e;">
              <i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp
            </a>
          </div>

        </div>

      </div>
    </div>
  </section>

  {{-- Verified Footer --}}
  @include('frontend.partials.footer')

  <script>
    const lgaMap = {
      'Kogi': ['Adavi', 'Ajaokuta', 'Ankpa', 'Bassa', 'Dekina', 'Ibaji', 'Idah', 'Igalamela-Odolu', 'Ijumu', 'Kabba/Bunu', 'Kogi', 'Lokoja', 'Mopa-Muro', 'Ofu', 'Ogori/Magongo', 'Okehi', 'Okene', 'Olamaboro', 'Omala', 'Yagba East (Isanlu)', 'Yagba West'],
      'Kwara': ['Asa', 'Baruten', 'Edu', 'Ekiti', 'Ifelodun', 'Ilorin East', 'Ilorin South', 'Ilorin West', 'Irepodun', 'Isin', 'Kaiama', 'Moro', 'Offa', 'Oke Ero', 'Oyun', 'Pategi'],
      'Oyo': ['Afijio', 'Akinyele', 'Atiba', 'Atisbo', 'Egbeda', 'Ibadan North', 'Ibadan North-East', 'Ibadan North-West', 'Ibadan South-East', 'Ibadan South-West', 'Ibarapa Central', 'Ibarapa East', 'Ibarapa North', 'Iddo', 'Irepo', 'Iseyin', 'Itesiwaju', 'Iwajowa', 'Ogbomoso North', 'Ogbomoso South', 'Ogo Oluwa', 'Olorunsogo', 'Oluyole', 'Ona Ara', 'Orelope', 'Ori Ire', 'Oyo East', 'Oyo West', 'Saki East', 'Saki West', 'Surulere'],
      'Ondo': ['Akoko North-East', 'Akoko North-West', 'Akoko South-West', 'Akoko South-East', 'Akure North', 'Akure South', 'Ese Odo', 'Idanre', 'Ifedore', 'Ilaje', 'Ile Oluji/Okeigbo', 'Irele', 'Odigbo', 'Okitipupa', 'Ondo East', 'Ondo West', 'Ose', 'Owo'],
      'Edo': ['Akoko-Edo', 'Egor', 'Esan Central', 'Esan North-East', 'Esan South-East', 'Esan West', 'Etsako Central', 'Etsako East', 'Etsako West', 'Igueben', 'Ikpoba Okha', 'Orhionmwon', 'Oredo', 'Ovia North-East', 'Ovia South-West', 'Owan East', 'Owan West', 'Uhunmwonde'],
      'FCT - Abuja': ['Abaji', 'Bwari', 'Gwagwalada', 'Kuje', 'Kwali', 'Municipal Area Council (AMAC)']
    };

    function fetchLgasForState(stateSelect) {
      const selectedOption = stateSelect.options[stateSelect.selectedIndex];
      const stateName = selectedOption.value;
      const stateId = selectedOption.getAttribute('data-id');
      const lgaSelect = document.getElementById('lga');

      lgaSelect.innerHTML = '<option value="">Loading LGAs...</option>';

      if (!stateName) {
        lgaSelect.innerHTML = '<option value="">Select State First</option>';
        return;
      }

      // Try fetching from server API first
      fetch(`/api/lgas?state_id=${stateId}&state_name=${encodeURIComponent(stateName)}`)
        .then(res => res.json())
        .then(data => {
          if (Array.isArray(data) && data.length > 0) {
            lgaSelect.innerHTML = '<option value="">-- Select LGA --</option>';
            data.forEach(item => {
              lgaSelect.innerHTML += `<option value="${item.name}">${item.name}</option>`;
            });
          } else {
            populateFallbackLgas(stateName);
          }
        })
        .catch(() => {
          populateFallbackLgas(stateName);
        });
    }

    function populateFallbackLgas(stateName) {
      const lgaSelect = document.getElementById('lga');
      const list = lgaMap[stateName] || ['Central LGA', 'North LGA', 'South LGA', 'East LGA', 'West LGA'];
      lgaSelect.innerHTML = '<option value="">-- Select LGA --</option>';
      list.forEach(lga => {
        lgaSelect.innerHTML += `<option value="${lga}">${lga}</option>`;
      });
    }

    // Trigger initial LGA load if state pre-selected
    document.addEventListener('DOMContentLoaded', function() {
      const stateSelect = document.getElementById('state_of_origin');
      if (stateSelect && stateSelect.value) {
        fetchLgasForState(stateSelect);
      }
    });
  </script>

</body>

</html>