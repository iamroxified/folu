<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Admission Process | FOLU INTERNATIONAL GROUP OF SCHOOLS | Isanlu, Kogi State</title>
  <meta name="description" content="Discover the 5-step admission process for Creche, Nursery, Primary, and Secondary school at Folu International Group of Schools, Itedo-Ijowa, Isanlu, Kogi State.">
  <meta name="keywords" content="Folu International Schools Admission Process, Isanlu School Enrolment, Creche Nursery Primary Secondary Entrance Isanlu">

  <!-- OpenGraph / Social Metadata -->
  <meta property="og:title" content="Admission Process | Folu International Group of Schools">
  <meta property="og:description" content="Step-by-step admission roadmap for joining Folu International Group of Schools in Isanlu, Kogi State.">
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
          <span class="current">Admission Process</span>
        </nav>
        <h1 class="folu-page-title">A Clear, Supportive Admission Roadmap</h1>
        <p class="folu-page-subtitle">
          We welcome families into our learning community through a transparent, warm, and structured process designed to identify each child's individual strengths and place them in the optimal class.
        </p>
      </div>
    </div>
  </header>

  <!-- 2. THE 5-STEP ADMISSION ROADMAP -->
  <section class="folu-section">
    <div class="folu-container">
      <div class="folu-section-header">
        <span class="folu-section-badge">Step-by-Step</span>
        <h2 class="folu-section-title">5 Steps to Becoming a Folu Student</h2>
        <p class="folu-section-subtitle">
          From initial enquiry to your child's first day in uniform, we walk alongside your family every step of the way.
        </p>
      </div>

      <div class="folu-timeline" style="max-width: 860px; margin: 0 auto;">
        
        <!-- Step 1 -->
        <div class="folu-timeline-step">
          <div class="folu-timeline-dot">1</div>
          <div class="folu-timeline-card">
            <span class="folu-section-badge" style="margin-bottom: 8px;">Step 1</span>
            <h3 class="folu-timeline-title">Enquiry &amp; Form Submission</h3>
            <p class="folu-timeline-desc">
              Parents can submit our online application form on this website or obtain an application pack directly from the Admissions Office at our campus in <strong>Itedo-Ijowa, Isanlu</strong>. You are welcome to tour the classrooms, library, and play facilities during office hours.
            </p>
            <div style="margin-top: 14px;">
              <a href="{{ url('/apply') }}" class="folu-btn folu-btn-primary" style="padding: 8px 18px; font-size: 13.5px;">
                Fill Online Application
              </a>
            </div>
          </div>
        </div>

        <!-- Step 2 -->
        <div class="folu-timeline-step">
          <div class="folu-timeline-dot">2</div>
          <div class="folu-timeline-card">
            <span class="folu-section-badge" style="margin-bottom: 8px;">Step 2</span>
            <h3 class="folu-timeline-title">Assessment &amp; Diagnostic Evaluation</h3>
            <p class="folu-timeline-desc">
              Prospective students participate in an age-appropriate assessment:
            </p>
            <ul style="margin: 10px 0 0 0; padding-left: 20px; font-size: 14px; color: var(--folu-text-body); line-height: 1.6;">
              <li><strong>Creche &amp; Nursery:</strong> Friendly developmental, sensory, and verbal observation in a relaxed playroom setting.</li>
              <li><strong>Primary School:</strong> Basic diagnostic evaluation in English reading, writing, and Mathematics fundamentals.</li>
              <li><strong>Secondary School:</strong> Entrance evaluation covering English language, Mathematics, and General Paper.</li>
            </ul>
          </div>
        </div>

        <!-- Step 3 -->
        <div class="folu-timeline-step">
          <div class="folu-timeline-dot">3</div>
          <div class="folu-timeline-card">
            <span class="folu-section-badge" style="margin-bottom: 8px;">Step 3</span>
            <h3 class="folu-timeline-title">Family Interaction &amp; Academic Review</h3>
            <p class="folu-timeline-desc">
              We hold a brief, cordial interaction with parents or guardians to review the assessment findings, understand the child's unique gifts or health considerations, and ensure mutual alignment with the school's core motto of <strong>Knowledge &amp; Discipline</strong>.
            </p>
          </div>
        </div>

        <!-- Step 4 -->
        <div class="folu-timeline-step">
          <div class="folu-timeline-dot">4</div>
          <div class="folu-timeline-card">
            <span class="folu-section-badge" style="margin-bottom: 8px;">Step 4</span>
            <h3 class="folu-timeline-title">Offer of Admission &amp; Fee Settlement</h3>
            <p class="folu-timeline-desc">
              Successful applicants receive an official <strong>Offer of Provisional Admission</strong> with full fee breakdown, list of textbooks, and registration forms. Securing the child's seat requires acceptance and settlement of the prescribed tuition/registration fees.
            </p>
          </div>
        </div>

        <!-- Step 5 -->
        <div class="folu-timeline-step">
          <div class="folu-timeline-dot">5</div>
          <div class="folu-timeline-card">
            <span class="folu-section-badge" style="margin-bottom: 8px;">Step 5</span>
            <h3 class="folu-timeline-title">Uniform Collection &amp; Resumption Orientation</h3>
            <p class="folu-timeline-desc">
              Parents collect the official Folu school uniform, sportswear, badges, and learning materials. On resumption day, learners are welcomed by their class teachers and paired with student buddies to help them settle in comfortably.
            </p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- 3. REQUIREMENTS BY SCHOOL LEVEL -->
  <section class="folu-section folu-section-subtle">
    <div class="folu-container">
      <div class="folu-section-header">
        <span class="folu-section-badge">Entry Standards</span>
        <h2 class="folu-section-title">Requirements Across Educational Levels</h2>
        <p class="folu-section-subtitle">
          Age guidance and prerequisites for our four distinct educational stages.
        </p>
      </div>

      <div class="folu-pillars-grid">
        <!-- Creche -->
        <div class="folu-pillar-card">
          <div class="folu-pillar-icon"><i class="fa fa-child"></i></div>
          <h3 class="folu-pillar-title">Creche (Ages 3m - 2y)</h3>
          <p class="folu-pillar-desc">
            Early nurturing, sensory stimulation, and toddler motor skill development in a hygienic, loving environment.
          </p>
          <ul style="font-size: 13px; color: var(--folu-text-muted); margin-top: 12px; padding-left: 16px; line-height: 1.5;">
            <li>Birth Certificate</li>
            <li>Immunization record</li>
            <li>Parent passport photo</li>
          </ul>
        </div>

        <!-- Nursery -->
        <div class="folu-pillar-card">
          <div class="folu-pillar-icon" style="background: var(--folu-gold-light); color: var(--folu-gold);"><i class="fa fa-pencil"></i></div>
          <h3 class="folu-pillar-title">Nursery (Ages 3 - 5y)</h3>
          <p class="folu-pillar-desc">
            Phonics, early numeracy, social interaction, and foundational independence through play and guided learning.
          </p>
          <ul style="font-size: 13px; color: var(--folu-text-muted); margin-top: 12px; padding-left: 16px; line-height: 1.5;">
            <li>Pre-nursery to Nursery 3</li>
            <li>Basic verbal readiness</li>
            <li>2 passport photographs</li>
          </ul>
        </div>

        <!-- Primary -->
        <div class="folu-pillar-card">
          <div class="folu-pillar-icon" style="background: var(--folu-green-light); color: var(--folu-green);"><i class="fa fa-book"></i></div>
          <h3 class="folu-pillar-title">Primary (Ages 6 - 11y)</h3>
          <p class="folu-pillar-desc">
            Core academics in English, Mathematics, Basic Science, ICT, Social Studies, and character modeling (Basic 1 - 6).
          </p>
          <ul style="font-size: 13px; color: var(--folu-text-muted); margin-top: 12px; padding-left: 16px; line-height: 1.5;">
            <li>Written placement test</li>
            <li>Previous term report card</li>
            <li>Transfer certificate if transferring</li>
          </ul>
        </div>

        <!-- Secondary -->
        <div class="folu-pillar-card">
          <div class="folu-pillar-icon" style="background: #ede9fe; color: #7c3aed;"><i class="fa fa-university"></i></div>
          <h3 class="folu-pillar-title">Secondary (College)</h3>
          <p class="folu-pillar-desc">
            Junior Secondary (JSS 1-3) &amp; Senior Secondary (SSS 1-3) with science, arts, and commercial specializations.
          </p>
          <ul style="font-size: 13px; color: var(--folu-text-muted); margin-top: 12px; padding-left: 16px; line-height: 1.5;">
            <li>Primary school leaving testimonial</li>
            <li>Secondary entrance examination</li>
            <li>Academic transcript/report</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- 4. ADMISSIONS FAQ -->
  <section class="folu-section">
    <div class="folu-container">
      <div class="folu-section-header">
        <span class="folu-section-badge">Got Questions?</span>
        <h2 class="folu-section-title">Frequently Asked Questions</h2>
        <p class="folu-section-subtitle">
          Clear answers to common questions asked by prospective parents.
        </p>
      </div>

      <div class="folu-faq-list" style="max-width: 820px; margin: 0 auto;">
        
        <div class="folu-faq-card">
          <h4 class="folu-faq-question">
            <span>When does admission open at Folu International Group of Schools?</span>
            <i class="fa fa-angle-down" style="color: var(--folu-gold);"></i>
          </h4>
          <p class="folu-faq-answer">
            Main admissions open during the 3rd term (May - August) for resumption in September (First Term). However, transfer admissions into select classes are accommodated throughout the academic year subject to seat availability.
          </p>
        </div>

        <div class="folu-faq-card">
          <h4 class="folu-faq-question">
            <span>Where is the school located and how can I visit?</span>
            <i class="fa fa-angle-down" style="color: var(--folu-gold);"></i>
          </h4>
          <p class="folu-faq-answer">
            Our campus is located at <strong>P.O. Box 37, Itedo-Ijowa, Isanlu, Kogi State, Nigeria</strong>. Visitors and parents are welcome Monday through Friday between 7:30 AM and 4:00 PM.
          </p>
        </div>

        <div class="folu-faq-card">
          <h4 class="folu-faq-question">
            <span>Are entrance assessment tests mandatory for new students?</span>
            <i class="fa fa-angle-down" style="color: var(--folu-gold);"></i>
          </h4>
          <p class="folu-faq-answer">
            Yes, diagnostic assessments are conducted for Primary and Secondary applicants. The assessment is not meant to intimidate, but rather to evaluate the child's academic baseline so our teachers can provide tailored support.
          </p>
        </div>

        <div class="folu-faq-card">
          <h4 class="folu-faq-question">
            <span>How do I contact the admissions office directly?</span>
            <i class="fa fa-angle-down" style="color: var(--folu-gold);"></i>
          </h4>
          <p class="folu-faq-answer">
            You can call our admissions help desk at <strong>08165354191</strong> or <strong>08057421037</strong>, email <strong>info@foluinternationalschools.sch.ng</strong>, or chat with an admissions officer directly on WhatsApp.
          </p>
        </div>

      </div>

      <!-- Quick Action Buttons -->
      <div style="text-align: center; margin-top: 44px; display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
        <a href="{{ url('/apply') }}" class="folu-btn folu-btn-primary" style="padding: 16px 36px; font-size: 15px;">
          Start Your Application Online
        </a>
        <a href="{{ url('/admission-policy') }}" class="folu-btn folu-btn-secondary" style="padding: 16px 28px; font-size: 15px;">
          View Admission Policy
        </a>
      </div>

    </div>
  </section>

  {{-- Verified Footer --}}
  @include('frontend.partials.footer')

</body>
</html>
