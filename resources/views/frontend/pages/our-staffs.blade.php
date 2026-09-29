<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Our Staff &amp; Faculty | FOLU INTERNATIONAL GROUP OF SCHOOLS | Isanlu, Kogi State</title>
  <meta name="description" content="Meet the qualified, dedicated, and caring educators and administrators of Folu International Group of Schools in Itedo-Ijowa, Isanlu, Kogi State.">
  <meta name="keywords" content="Folu International Schools Teachers, Educators in Isanlu, School Staff Kogi State">

  <!-- OpenGraph / Social Metadata -->
  <meta property="og:title" content="Our Staff &amp; Faculty | Folu International Group of Schools">
  <meta property="og:description" content="Passionate educators dedicated to nurturing leaders with Knowledge &amp; Discipline.">
  <meta property="og:image" content="{{ asset('images/staff.jpg') }}">
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
          <a href="{{ url('/about-us') }}">About Us</a>
          <span class="sep">/</span>
          <span class="current">Our Staff</span>
        </nav>
        <h1 class="folu-page-title">Dedicated, Qualified &amp; Caring Mentors</h1>
        <p class="folu-page-subtitle">
          Behind every confident graduate at Folu International Group of Schools is a committed team of teachers and caregivers who inspire curiosity, uphold high standards, and model godly character in Itedo-Ijowa, Isanlu.
        </p>
      </div>
    </div>
  </header>

  <!-- 2. EDITORIAL FACULTY OVERVIEW -->
  <section class="folu-section">
    <div class="folu-container">
      <div class="folu-editorial-grid">
        <div class="folu-editorial-text">
          <span class="folu-section-badge">Academic Faculty</span>
          <h2 class="folu-section-title" style="font-size: 34px;">Educators Committed to Individual Student Success</h2>
          <p style="font-size: 16px; color: var(--folu-text-body); line-height: 1.7; margin-bottom: 18px;">
            At <strong>Folu International Group of Schools</strong>, teaching is more than a profession &mdash; it is a sacred calling to shape the moral and intellectual destiny of children.
          </p>
          <p style="font-size: 15px; color: var(--folu-text-muted); line-height: 1.7; margin-bottom: 24px;">
            Our teachers undergo regular pedagogical workshops to integrate modern interactive methods with time-tested instructional rigour. From early childhood phonics to senior secondary laboratory experiments, our educators make learning accessible and exciting.
          </p>

          <ul class="folu-checklist" style="margin-bottom: 28px;">
            <li><i class="fa fa-check-circle"></i> <strong>Qualified Subject Specialists:</strong> Passionate subject teachers in Sciences, Commercials, and Arts.</li>
            <li><i class="fa fa-check-circle"></i> <strong>Moral Guardianship:</strong> Teachers model punctuality, integrity, and respect every day.</li>
            <li><i class="fa fa-check-circle"></i> <strong>Personalized Student Attention:</strong> Prompt identification of learning hurdles and tailored remedial support.</li>
          </ul>

          <div style="display: flex; gap: 14px; flex-wrap: wrap;">
            <a href="{{ url('/apply') }}" class="folu-btn folu-btn-primary">Apply for Admission</a>
            <a href="{{ url('/contact') }}" class="folu-btn folu-btn-secondary">Contact the School</a>
          </div>
        </div>

        <div class="folu-editorial-visual">
          <div class="folu-editorial-img-frame">
            <img src="{{ asset('images/staff.jpg') }}" alt="Academic and Administrative Staff of Folu International Group of Schools" loading="lazy">
          </div>
          <div class="folu-editorial-badge">
            <div class="folu-editorial-badge-number">Dedicated</div>
            <div class="folu-editorial-badge-label">Faculty of Folu International in Isanlu, Kogi State</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 3. SCHOOL PROPRIETORSHIP & LEADERSHIP -->
  <section class="folu-section folu-section-subtle">
    <div class="folu-container">
      <div class="folu-section-header">
        <span class="folu-section-badge">Leadership</span>
        <h2 class="folu-section-title">The Visionary Stewards</h2>
        <p class="folu-section-subtitle">
          Providing direction, values, and administrative excellence.
        </p>
      </div>

      <div class="folu-leadership-grid">
        <div class="folu-leadership-card">
          <div class="folu-leadership-avatar">
            <span>SB</span>
          </div>
          <div class="folu-leadership-info">
            <h3 class="folu-leadership-title">Rev. Dr. Samuel Babaniyi</h3>
            <div class="folu-leadership-role">Proprietor</div>
            <p class="folu-leadership-bio">
              Guiding the institution with a lifelong dedication to academic excellence, Christian ethics, and purposeful leadership development in Kogi State.
            </p>
          </div>
        </div>

        <div class="folu-leadership-card">
          <div class="folu-leadership-avatar" style="border-color: var(--folu-blue); background: var(--folu-navy-dark);">
            <span>MB</span>
          </div>
          <div class="folu-leadership-info">
            <h3 class="folu-leadership-title">Rev. Mrs. Mofoluwake Babaniyi</h3>
            <div class="folu-leadership-role">Proprietress</div>
            <p class="folu-leadership-bio">
              Devoted to the welfare, pastoral care, moral discipline, and foundational academic growth of every student across Creche, Nursery, Primary, and Secondary classes.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 4. FACULTY DEPARTMENTS -->
  <section class="folu-section">
    <div class="folu-container">
      <div class="folu-section-header">
        <span class="folu-section-badge">Departments</span>
        <h2 class="folu-section-title">Our Instructional Departments</h2>
        <p class="folu-section-subtitle">
          Organized to deliver specialized care and curriculum mastery across each educational phase.
        </p>
      </div>

      <div class="folu-values-grid">
        <div class="folu-value-card">
          <div class="folu-value-icon"><i class="fa fa-child"></i></div>
          <h3 class="folu-value-title">Early Years &amp; Nursery Caregivers</h3>
          <p class="folu-value-desc">
            Maternal, patient, and specially trained caregivers focused on language stimulation, phonics, basic motor development, and emotional safety.
          </p>
        </div>

        <div class="folu-value-card">
          <div class="folu-value-icon" style="background: var(--folu-gold-light); color: var(--folu-gold);"><i class="fa fa-book"></i></div>
          <h3 class="folu-value-title">Primary School Form Masters</h3>
          <p class="folu-value-desc">
            Experienced elementary school teachers who build numeracy, reading fluency, handwriting neatness, and foundational study discipline.
          </p>
        </div>

        <div class="folu-value-card">
          <div class="folu-value-icon" style="background: var(--folu-green-light); color: var(--folu-green);"><i class="fa fa-graduation-cap"></i></div>
          <h3 class="folu-value-title">Secondary College Subject Masters</h3>
          <p class="folu-value-desc">
            Specialist teachers across Sciences, Commercial studies, and Arts who guide students through BECE, WAEC, NECO, and JAMB UTME preparation.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- 5. CTA -->
  <section class="folu-section folu-section-subtle" style="padding-top: 10px;">
    <div class="folu-container">
      <div class="folu-cta-card">
        <div class="folu-cta-content">
          <span class="folu-section-badge" style="background: rgba(217, 119, 6, 0.2); color: var(--folu-gold-accent);">Join Our Community</span>
          <h2 class="folu-cta-title">Entrust Your Child to Dedicated Educators</h2>
          <p class="folu-cta-text">
            Schedule a visit to meet our teachers or apply for admission across Creche, Nursery, Primary, and Secondary school.
          </p>
        </div>
        <div class="folu-cta-buttons">
          <a href="{{ url('/apply') }}" class="folu-btn folu-btn-primary">Apply for Admission</a>
          <a href="{{ url('/contact') }}" class="folu-btn folu-btn-outline" style="border-color: rgba(255, 255, 255, 0.4); color: #ffffff;">Contact Us</a>
        </div>
      </div>
    </div>
  </section>

  {{-- Verified Footer --}}
  @include('frontend.partials.footer')

</body>
</html>
