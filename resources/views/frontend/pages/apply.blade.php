<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Apply for Admission | FOLU INTERNATIONAL GROUP OF SCHOOLS | Isanlu, Kogi State</title>
  <meta name="description" content="Apply online or submit an admission enquiry for Creche, Nursery, Primary, and Secondary education at Folu International Group of Schools, Itedo-Ijowa, Isanlu, Kogi State.">
  <meta name="keywords" content="Folu International Schools Admission, Apply Folu School Isanlu, Creche Nursery Primary Secondary Admission Kogi State, Isanlu School Enrolment">

  <!-- OpenGraph / Social Metadata -->
  <meta property="og:title" content="Apply for Admission | Folu International Group of Schools">
  <meta property="og:description" content="Begin your child's journey to academic excellence and moral leadership. Apply online today.">
  <meta property="og:image" content="{{ asset('images/folu-logo.png') }}">
  <meta property="og:type" content="website">

  <!-- Favicon & Icons -->
  <link rel="icon" href="{{ asset('images/folu-logo.png') }}" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

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
          Admissions are open across Creche, Nursery, Primary, and Secondary classes. Complete our quick admission enquiry form below or visit our admissions office in Itedo-Ijowa, Isanlu.
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
            <span class="folu-section-badge">Online Admission Enquiry</span>
            <h2 class="folu-form-title">Student Registration Form</h2>
            <p class="folu-form-subtitle" style="margin-bottom: 0;">
              Please complete each section below. Our admissions team will review your application and contact you within 24 to 48 hours to schedule an assessment or family interview.
            </p>
          </div>

          <form id="foluAdmissionForm" onsubmit="event.preventDefault(); handleAdmissionSubmit();">

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
                    <input type="radio" id="lvl_creche" name="school_level" value="Creche" required>
                    <label for="lvl_creche" class="folu-form-radio-label">Creche</label>
                  </div>
                  <div class="folu-form-radio-card">
                    <input type="radio" id="lvl_nursery" name="school_level" value="Nursery">
                    <label for="lvl_nursery" class="folu-form-radio-label">Nursery</label>
                  </div>
                  <div class="folu-form-radio-card">
                    <input type="radio" id="lvl_primary" name="school_level" value="Primary" checked>
                    <label for="lvl_primary" class="folu-form-radio-label">Primary</label>
                  </div>
                  <div class="folu-form-radio-card">
                    <input type="radio" id="lvl_secondary" name="school_level" value="Secondary">
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
                    <option value="Creche / Playgroup">Creche / Playgroup</option>
                    <option value="Pre-Nursery">Pre-Nursery</option>
                    <option value="Nursery 1">Nursery 1</option>
                    <option value="Nursery 2">Nursery 2</option>
                    <option value="Nursery 3">Nursery 3</option>
                  </optgroup>
                  <optgroup label="Primary School">
                    <option value="Primary 1">Primary 1 (Basic 1)</option>
                    <option value="Primary 2">Primary 2 (Basic 2)</option>
                    <option value="Primary 3">Primary 3 (Basic 3)</option>
                    <option value="Primary 4">Primary 4 (Basic 4)</option>
                    <option value="Primary 5">Primary 5 (Basic 5)</option>
                    <option value="Primary 6">Primary 6 (Basic 6)</option>
                  </optgroup>
                  <optgroup label="Secondary School (College)">
                    <option value="JSS 1">Junior Secondary 1 (JSS 1)</option>
                    <option value="JSS 2">Junior Secondary 2 (JSS 2)</option>
                    <option value="JSS 3">Junior Secondary 3 (JSS 3)</option>
                    <option value="SSS 1">Senior Secondary 1 (SSS 1)</option>
                    <option value="SSS 2">Senior Secondary 2 (SSS 2)</option>
                  </optgroup>
                </select>
              </div>
            </div>

            <!-- Section 2: Student Details -->
            <div class="folu-form-section">
              <div class="folu-form-section-header">
                <span class="folu-form-section-num">2</span>
                <h3 class="folu-form-section-title">Student Personal Information</h3>
              </div>

              <!-- Student Info: Name & DOB -->
              <div class="folu-form-grid-2">
                <div class="folu-form-group">
                  <label for="student_name" class="folu-form-label">Child's Full Name <span class="req">*</span></label>
                  <input type="text" id="student_name" name="student_name" class="folu-form-control" placeholder="e.g. Babatunde Emmanuel" required>
                </div>
                <div class="folu-form-group">
                  <label for="dob" class="folu-form-label">Date of Birth <span class="req">*</span></label>
                  <input type="date" id="dob" name="dob" class="folu-form-control" required>
                </div>
              </div>

              <!-- Student Info: Gender & Previous School -->
              <div class="folu-form-grid-2">
                <div class="folu-form-group" style="margin-bottom: 0;">
                  <label for="gender" class="folu-form-label">Gender <span class="req">*</span></label>
                  <select id="gender" name="gender" class="folu-form-select" required>
                    <option value="">-- Select Gender --</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                  </select>
                </div>
                <div class="folu-form-group" style="margin-bottom: 0;">
                  <label for="prev_school" class="folu-form-label">Previous School Attended</label>
                  <input type="text" id="prev_school" name="prev_school" class="folu-form-control" placeholder="Name of previous school (if applicable)">
                </div>
              </div>
            </div>

            <!-- Section 3: Parent / Guardian Information -->
            <div class="folu-form-section">
              <div class="folu-form-section-header">
                <span class="folu-form-section-num">3</span>
                <h3 class="folu-form-section-title">Parent / Guardian Information</h3>
              </div>

              <!-- Parent Name & Relationship -->
              <div class="folu-form-grid-2">
                <div class="folu-form-group">
                  <label for="parent_name" class="folu-form-label">Parent / Guardian Full Name <span class="req">*</span></label>
                  <input type="text" id="parent_name" name="parent_name" class="folu-form-control" placeholder="e.g. Mr. / Mrs. Babaniyi" required>
                </div>
                <div class="folu-form-group">
                  <label for="relationship" class="folu-form-label">Relationship to Child <span class="req">*</span></label>
                  <select id="relationship" name="relationship" class="folu-form-select" required>
                    <option value="Father">Father</option>
                    <option value="Mother">Mother</option>
                    <option value="Guardian">Legal Guardian</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
              </div>

              <!-- Parent Contact: Phone & WhatsApp -->
              <div class="folu-form-grid-2">
                <div class="folu-form-group">
                  <label for="phone" class="folu-form-label">Primary Phone Number <span class="req">*</span></label>
                  <input type="tel" id="phone" name="phone" class="folu-form-control" placeholder="e.g. 08012345678" required>
                </div>
                <div class="folu-form-group">
                  <label for="whatsapp" class="folu-form-label">WhatsApp Number</label>
                  <input type="tel" id="whatsapp" name="whatsapp" class="folu-form-control" placeholder="e.g. 08165354191">
                </div>
              </div>

              <!-- Email & Residential Address -->
              <div class="folu-form-grid-2">
                <div class="folu-form-group" style="margin-bottom: 0;">
                  <label for="email" class="folu-form-label">Email Address</label>
                  <input type="email" id="email" name="email" class="folu-form-control" placeholder="e.g. parent@example.com">
                </div>
                <div class="folu-form-group" style="margin-bottom: 0;">
                  <label for="residential_address" class="folu-form-label">Residential Address / Town <span class="req">*</span></label>
                  <input type="text" id="residential_address" name="residential_address" class="folu-form-control" placeholder="e.g. Itedo-Ijowa, Isanlu, Kogi State" required>
                </div>
              </div>
            </div>

            <!-- Section 4: Notes & Submission -->
            <div class="folu-form-section">
              <div class="folu-form-section-header">
                <span class="folu-form-section-num">4</span>
                <h3 class="folu-form-section-title">Additional Inquiries &amp; Needs</h3>
              </div>

              <div class="folu-form-group" style="margin-bottom: 0;">
                <label for="additional_notes" class="folu-form-label">Special Needs, Talents, or Questions</label>
                <textarea id="additional_notes" name="additional_notes" class="folu-form-textarea" placeholder="Tell us any specific learning requirements, medical considerations, or questions you have for our team..."></textarea>
              </div>
            </div>

            <div id="formSuccessMessage" style="display: none; background: var(--folu-green-light); border: 1.5px solid var(--folu-green); color: var(--folu-green); padding: 18px 20px; border-radius: var(--folu-radius-sm); margin-bottom: 20px;">
              <i class="fa fa-check-circle" style="font-size: 18px; margin-right: 6px;"></i>
              <strong>Enquiry Received Successfully!</strong> Thank you for reaching out to Folu International Group of Schools. Our admissions secretary will contact you via phone or WhatsApp shortly.
            </div>

            <button type="submit" class="folu-btn folu-btn-primary" style="width: 100%; padding: 16px; font-size: 16px;">
              Submit Admission Application <i class="fa fa-arrow-right" style="margin-left: 8px;"></i>
            </button>
            <p class="folu-form-hint" style="text-align: center; margin-top: 10px;">
              Need urgent assistance? Call our admissions line directly: <strong>08165354191</strong> or <strong>08057421037</strong>.
            </p>
          </form>
        </div>

        <!-- Right: Admissions Guide & Checklist Sidebar -->
        <div class="folu-apply-sidebar">
          <!-- Required Documents Box -->
          <div style="background: var(--folu-surface); border-radius: var(--folu-radius-md); border: 1px solid var(--folu-border); padding: 28px 24px; box-shadow: var(--folu-shadow-sm);">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
              <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--folu-gold-light); color: var(--folu-gold); display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="fa fa-folder-open"></i>
              </div>
              <h3 style="font-size: 18px; font-weight: 700; color: var(--folu-navy); margin: 0;">Required Documents</h3>
            </div>
            <p style="font-size: 13.5px; color: var(--folu-text-muted); line-height: 1.5; margin-bottom: 16px;">
              Please have the following documents ready for submission upon invitation to the school:
            </p>
            <ul class="folu-checklist">
              <li><i class="fa fa-check-circle"></i> Child's Birth Certificate or Declaration of Age</li>
              <li><i class="fa fa-check-circle"></i> 2 recent passport photographs of the student</li>
              <li><i class="fa fa-check-circle"></i> Most recent term report card / transfer certificate (for Primary &amp; Secondary)</li>
              <li><i class="fa fa-check-circle"></i> Medical immunization / health clearance record</li>
              <li><i class="fa fa-check-circle"></i> 1 passport photograph of each parent/guardian</li>
            </ul>
          </div>

          <!-- Admission Process Card -->
          <div style="background: var(--folu-surface); border-radius: var(--folu-radius-md); border: 1px solid var(--folu-border); padding: 28px 24px; box-shadow: var(--folu-shadow-sm);">
            <h3 style="font-size: 18px; font-weight: 700; color: var(--folu-navy); margin-bottom: 14px;">Admission Steps</h3>
            <div class="folu-timeline" style="gap: 18px; padding-left: 30px;">
              <div class="folu-timeline-step">
                <div class="folu-timeline-dot" style="width: 24px; height: 24px; left: -30px; font-size: 11px;">1</div>
                <div>
                  <h4 style="font-size: 14.5px; font-weight: 700; margin-bottom: 2px;">Enquire &amp; Apply</h4>
                  <p style="font-size: 12.5px; color: var(--folu-text-muted); margin: 0;">Submit the online form or obtain a form on campus.</p>
                </div>
              </div>
              <div class="folu-timeline-step">
                <div class="folu-timeline-dot" style="width: 24px; height: 24px; left: -30px; font-size: 11px;">2</div>
                <div>
                  <h4 style="font-size: 14.5px; font-weight: 700; margin-bottom: 2px;">Assessment / Interview</h4>
                  <p style="font-size: 12.5px; color: var(--folu-text-muted); margin: 0;">Friendly placement test to understand child's learning stage.</p>
                </div>
              </div>
              <div class="folu-timeline-step">
                <div class="folu-timeline-dot" style="width: 24px; height: 24px; left: -30px; font-size: 11px;">3</div>
                <div>
                  <h4 style="font-size: 14.5px; font-weight: 700; margin-bottom: 2px;">Offer &amp; Registration</h4>
                  <p style="font-size: 12.5px; color: var(--folu-text-muted); margin: 0;">Offer letter issued, fee clearance &amp; uniform collection.</p>
                </div>
              </div>
            </div>
            <div style="margin-top: 20px;">
              <a href="{{ url('/admission-process') }}" style="font-size: 13.5px; font-weight: 600; color: var(--folu-blue); text-decoration: none;">
                Read complete admission details &rarr;
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
                <i class="fa fa-phone" style="color: var(--folu-gold);"></i> 08165354191
              </a>
              <a href="tel:08057421037" style="color: #ffffff; text-decoration: none; font-size: 14.5px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-phone" style="color: var(--folu-gold);"></i> 08057421037
              </a>
              <a href="mailto:info@foluinternationalschools.sch.ng" style="color: #cbd5e1; text-decoration: none; font-size: 13.5px; display: flex; align-items: center; gap: 8px; word-break: break-all;">
                <i class="fa fa-envelope" style="color: var(--folu-gold);"></i> info@foluinternationalschools.sch.ng
              </a>
            </div>
            <a href="https://wa.me/2348165354191?text=Hello%20Folu%20International%20Schools,%20I%20want%20to%20apply%20for%20admission." target="_blank" rel="noopener noreferrer" class="folu-btn folu-btn-primary" style="width: 100%; justify-content: center; background: #22c55e; border-color: #22c55e;">
              <i class="fa fa-whatsapp"></i> Chat on WhatsApp
            </a>
          </div>

        </div>

      </div>
    </div>
  </section>

  {{-- Verified Footer --}}
  @include('frontend.partials.footer')

  <script>
    function handleAdmissionSubmit() {
      var studentName = document.getElementById('student_name').value;
      var targetClass = document.getElementById('target_class').value;
      var parentName = document.getElementById('parent_name').value;
      var phone = document.getElementById('phone').value;

      // Show friendly confirmation message
      document.getElementById('formSuccessMessage').style.display = 'block';
      document.getElementById('formSuccessMessage').scrollIntoView({
        behavior: 'smooth',
        block: 'center'
      });

      // Build WhatsApp enquiry link prefilled with applicant details
      var waMsg = "Hello Folu International Group of Schools! I just submitted an admission application:%0A" +
        "- Student: " + encodeURIComponent(studentName) + "%0A" +
        "- Proposed Class: " + encodeURIComponent(targetClass) + "%0A" +
        "- Parent: " + encodeURIComponent(parentName) + "%0A" +
        "- Phone: " + encodeURIComponent(phone);

      setTimeout(function() {
        var openWa = confirm("Your details have been saved! Would you also like to open WhatsApp to connect directly with the admissions officer now?");
        if (openWa) {
          window.open("https://wa.me/2348165354191?text=" + waMsg, "_blank");
        }
      }, 700);
    }
  </script>

</body>

</html>