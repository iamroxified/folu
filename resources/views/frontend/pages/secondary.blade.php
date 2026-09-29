<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Secondary School (College: JSS 1 - SSS 3) | FOLU INTERNATIONAL GROUP OF SCHOOLS | Isanlu, Kogi State</title>
  <meta name="description" content="Discover our College curriculum covering JSS 1 - SSS 3 across Science, Arts, and Commercial streams at Folu International Group of Schools, Itedo-Ijowa, Isanlu.">
  <meta name="keywords" content="Secondary School Isanlu, College Kogi State, WAEC NECO School Isanlu, JSS SSS Folu International Schools">

  <!-- OpenGraph / Social Metadata -->
  <meta property="og:title" content="Secondary School (College) | Folu International Group of Schools">
  <meta property="og:description" content="Rigorous preparation for WAEC, NECO, and university advancement anchored on Knowledge &amp; Discipline.">
  <meta property="og:image" content="{{ asset('images/j3grad.jpg') }}">
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
          <a href="{{ url('/overview-academics') }}">Academics</a>
          <span class="sep">/</span>
          <span class="current">Secondary School</span>
        </nav>
        <h1 class="folu-page-title">Preparing Visionary Leaders for Higher Education &amp; Life</h1>
        <p class="folu-page-subtitle">
          Our Secondary School (JSS 1 &ndash; SSS 3) blends intellectual depth, rigorous examination readiness, moral rectitude, and executive leadership development in Itedo-Ijowa, Isanlu.
        </p>
      </div>
    </div>
  </header>

  <!-- 2. EDITORIAL OVERVIEW -->
  <section class="folu-section">
    <div class="folu-container">
      <div class="folu-editorial-grid">
        <div class="folu-editorial-text">
          <span class="folu-section-badge">College Level (JSS 1 &ndash; SSS 3)</span>
          <h2 class="folu-section-title" style="font-size: 34px;">Academic Depth, National Examination Mastery, and Uncompromising Character</h2>
          <p style="font-size: 16px; color: var(--folu-text-body); line-height: 1.7; margin-bottom: 18px;">
            At <strong>Folu International Group of Schools</strong>, our secondary division prepares young scholars to excel in competitive tertiary admissions and become morally dependable leaders in Nigerian society.
          </p>
          <p style="font-size: 15px; color: var(--folu-text-muted); line-height: 1.7; margin-bottom: 24px;">
            Under the supervision of experienced subject masters, students are challenged through intensive coursework, laboratory practicals, independent research, debates, and leadership positions that test their maturity and resolve.
          </p>

          <ul class="folu-checklist" style="margin-bottom: 28px;">
            <li><i class="fa fa-check-circle"></i> <strong>Junior Secondary (JSS 1 &ndash; 3):</strong> Comprehensive foundation leading to the Basic Education Certificate Examination (BECE).</li>
            <li><i class="fa fa-check-circle"></i> <strong>Senior Secondary (SSS 1 &ndash; 3):</strong> Focused specialization across Science, Commercial, and Arts faculties preparing for WAEC, NECO &amp; JAMB UTME.</li>
            <li><i class="fa fa-check-circle"></i> <strong>Science &amp; ICT Practicals:</strong> Hands-on biology, chemistry, physics, and computer laboratory sessions.</li>
            <li><i class="fa fa-check-circle"></i> <strong>Prefectship &amp; Mentorship:</strong> Developing organizational skill, public speaking, and peer responsibility.</li>
          </ul>

          <div style="display: flex; gap: 14px; flex-wrap: wrap;">
            <a href="{{ url('/apply') }}" class="folu-btn folu-btn-primary">Apply for Secondary School</a>
            <a href="{{ url('/contact') }}" class="folu-btn folu-btn-secondary">Contact Admissions Desk</a>
          </div>
        </div>

        <div class="folu-editorial-visual">
          <div class="folu-editorial-img-frame">
            <img src="{{ asset('images/j3grad.jpg') }}" alt="Junior Secondary Graduating Students - Folu International Group of Schools" loading="lazy">
          </div>
          <div class="folu-editorial-badge">
            <div class="folu-editorial-badge-number">JSS &amp; SSS</div>
            <div class="folu-editorial-badge-label">Excellence in WAEC, NECO &amp; JAMB preparation in Isanlu</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 3. SENIOR SECONDARY ACADEMIC STREAMS -->
  <section class="folu-section folu-section-subtle">
    <div class="folu-container">
      <div class="folu-section-header">
        <span class="folu-section-badge">Faculties &amp; Pathways</span>
        <h2 class="folu-section-title">Specialized Academic Streams (SSS 1 &ndash; SSS 3)</h2>
        <p class="folu-section-subtitle">
          Guiding students toward their chosen university careers through focused subject combinations.
        </p>
      </div>

      <div class="folu-values-grid">
        <!-- Science -->
        <div class="folu-value-card">
          <div class="folu-value-icon"><i class="fa fa-flask"></i></div>
          <h3 class="folu-value-title">Science &amp; Technology</h3>
          <p class="folu-value-desc">
            For aspiring doctors, engineers, pharmacists, and technologists.
          </p>
          <div style="margin-top: 14px; font-size: 13.5px; color: var(--folu-text-body); line-height: 1.6;">
            <strong>Core Subjects:</strong> Mathematics, English, Physics, Chemistry, Biology, Further Mathematics, Agricultural Science, Computer Studies.
          </div>
        </div>

        <!-- Commercial -->
        <div class="folu-value-card">
          <div class="folu-value-icon" style="background: var(--folu-gold-light); color: var(--folu-gold);"><i class="fa fa-line-chart"></i></div>
          <h3 class="folu-value-title">Commercial &amp; Management</h3>
          <p class="folu-value-desc">
            For future accountants, bankers, economists, entrepreneurs, and managers.
          </p>
          <div style="margin-top: 14px; font-size: 13.5px; color: var(--folu-text-body); line-height: 1.6;">
            <strong>Core Subjects:</strong> Mathematics, English, Economics, Financial Accounting, Commerce, Government, Marketing, Civic Education.
          </div>
        </div>

        <!-- Arts -->
        <div class="folu-value-card">
          <div class="folu-value-icon" style="background: var(--folu-green-light); color: var(--folu-green);"><i class="fa fa-gavel"></i></div>
          <h3 class="folu-value-title">Arts &amp; Humanities</h3>
          <p class="folu-value-desc">
            For future lawyers, diplomats, writers, journalists, and public administrators.
          </p>
          <div style="margin-top: 14px; font-size: 13.5px; color: var(--folu-text-body); line-height: 1.6;">
            <strong>Core Subjects:</strong> English Language, Literature in English, Government, Christian Religious Studies, History, Civic Education, Yoruba.
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 4. MORNING ASSEMBLY & DISCIPLINARY CULTURE -->
  <section class="folu-section">
    <div class="folu-container">
      <div class="folu-editorial-grid reverse">
        <div class="folu-editorial-visual">
          <div class="folu-editorial-img-frame">
            <img src="{{ asset('images/assembly.jpg') }}" alt="Morning Assembly &amp; Discipline Culture at Folu International Group of Schools" loading="lazy">
          </div>
        </div>
        <div class="folu-editorial-text">
          <span class="folu-section-badge">Character Formation</span>
          <h3 style="font-size: 28px; font-weight: 800; color: var(--folu-navy); margin-bottom: 14px;">The Furnace of Discipline &amp; Self-Mastery</h3>
          <p style="font-size: 15.5px; color: var(--folu-text-body); line-height: 1.65; margin-bottom: 16px;">
            At Folu International Group of Schools, secondary education is where character is solidified. Through daily morning devotionals, orderly assemblies, strict punctuality enforcement, and personal accountability, our teenagers learn that success in life begins with self-discipline.
          </p>
          <p style="font-size: 14.5px; color: var(--folu-text-muted); line-height: 1.65; margin-bottom: 22px;">
            Our students take pride in their uniforms, conduct themselves with decorum, and build friendships rooted in positive values and healthy academic competition.
          </p>
          <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="{{ url('/admission-policy') }}" class="folu-btn folu-btn-outline" style="border-color: var(--folu-navy); color: var(--folu-navy);">Read Our Disciplinary Charter</a>
            <a href="{{ url('/apply') }}" class="folu-btn folu-btn-primary">Apply for JSS / SSS</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 5. CTA BANNER -->
  <section class="folu-section" style="padding-top: 10px;">
    <div class="folu-container">
      <div class="folu-cta-card">
        <div class="folu-cta-content">
          <span class="folu-section-badge" style="background: rgba(217, 119, 6, 0.2); color: var(--folu-gold-accent);">College Admissions</span>
          <h2 class="folu-cta-title">Position Your Teenager for Academic Excellence</h2>
          <p class="folu-cta-text">
            Join the ranks of successful students at Folu International Group of Schools. Applications are invited for JSS 1, transfer students into JSS 2, and SSS 1.
          </p>
        </div>
        <div class="folu-cta-buttons">
          <a href="{{ url('/apply') }}" class="folu-btn folu-btn-primary">Apply Now</a>
          <a href="https://wa.me/2348165354191?text=Hello%20Folu%20International%20Schools,%20I%20want%20to%20enquire%20about%20Secondary%20School%20admission." target="_blank" rel="noopener noreferrer" class="folu-btn folu-btn-outline" style="border-color: #22c55e; color: #22c55e;">
            <i class="fa fa-whatsapp"></i> Chat on WhatsApp
          </a>
        </div>
      </div>
    </div>
  </section>

  {{-- Verified Footer --}}
  @include('frontend.partials.footer')

</body>
</html>
