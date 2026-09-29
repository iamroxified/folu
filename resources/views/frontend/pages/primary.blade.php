<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Primary School (Basic 1 - 6) | FOLU INTERNATIONAL GROUP OF SCHOOLS | Isanlu, Kogi State</title>
  <meta name="description" content="Discover our vibrant Primary School curriculum, academic distinction, moral development, and holistic student growth at Folu International Group of Schools, Isanlu.">
  <meta name="keywords" content="Primary School Isanlu, Basic Education Kogi State, Best Primary School Isanlu, Folu International Primary School">

  <!-- OpenGraph / Social Metadata -->
  <meta property="og:title" content="Primary School (Basic 1 - 6) | Folu International Group of Schools">
  <meta property="og:description" content="Nurturing curiosity, strong academic foundations, and disciplined leadership in Itedo-Ijowa, Isanlu.">
  <meta property="og:image" content="{{ asset('images/prigrad.jpg') }}">
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
          <span class="current">Primary School</span>
        </nav>
        <h1 class="folu-page-title">Foundations of Intellectual Rigour &amp; Moral Character</h1>
        <p class="folu-page-subtitle">
          In our Primary School (Basic 1 &ndash; 6), pupils transition from foundational literacy into sharp quantitative thinking, scientific enquiry, and active community participation in Itedo-Ijowa, Isanlu.
        </p>
      </div>
    </div>
  </header>

  <!-- 2. EDITORIAL OVERVIEW WITH AUTHENTIC PHOTOGRAPHY -->
  <section class="folu-section">
    <div class="folu-container">
      <div class="folu-editorial-grid">
        <div class="folu-editorial-text">
          <span class="folu-section-badge">Primary School (Basic 1 &ndash; 6)</span>
          <h2 class="folu-section-title" style="font-size: 34px;">Empowering Young Minds with Knowledge &amp; Discipline</h2>
          <p style="font-size: 16px; color: var(--folu-text-body); line-height: 1.7; margin-bottom: 18px;">
            At <strong>Folu International Group of Schools</strong>, primary education represents the formative bridge between early childhood discovery and the advanced academic requirements of secondary school.
          </p>
          <p style="font-size: 15px; color: var(--folu-text-muted); line-height: 1.7; margin-bottom: 24px;">
            Our teachers foster an atmosphere where every child's questions are celebrated, homework is undertaken with diligence, and personal integrity is woven into every subject &mdash; from solving mathematical equations to writing imaginative essays.
          </p>

          <ul class="folu-checklist" style="margin-bottom: 28px;">
            <li><i class="fa fa-check-circle"></i> <strong>Rigorous Literacy &amp; Comprehension:</strong> Guided reading, creative writing, and grammatical mastery.</li>
            <li><i class="fa fa-check-circle"></i> <strong>Quantitative Mastery:</strong> Deep conceptual understanding of numbers, mental math, and problem-solving.</li>
            <li><i class="fa fa-check-circle"></i> <strong>Basic Science &amp; Technology:</strong> Hands-on experiments and computer literacy lessons.</li>
            <li><i class="fa fa-check-circle"></i> <strong>Character Modeling &amp; Civics:</strong> Instilling respect, honesty, leadership, and national pride.</li>
          </ul>

          <div style="display: flex; gap: 14px; flex-wrap: wrap;">
            <a href="{{ url('/apply') }}" class="folu-btn folu-btn-primary">Apply for Primary Admission</a>
            <a href="{{ url('/contact') }}" class="folu-btn folu-btn-secondary">Visit Our Campus</a>
          </div>
        </div>

        <div class="folu-editorial-visual">
          <div class="folu-editorial-img-frame">
            <img src="{{ asset('images/prigrad.jpg') }}" alt="Primary School Graduation and Milestones - Folu International Group of Schools" loading="lazy">
          </div>
          <div class="folu-editorial-badge">
            <div class="folu-editorial-badge-number">Basic 1 &ndash; 6</div>
            <div class="folu-editorial-badge-label">Excellence, character, and holistic development in Isanlu</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 3. PRIMARY CURRICULUM SUBJECT AREAS -->
  <section class="folu-section folu-section-subtle">
    <div class="folu-container">
      <div class="folu-section-header">
        <span class="folu-section-badge">Curriculum Framework</span>
        <h2 class="folu-section-title">Enriched Primary Subjects &amp; Practical Skills</h2>
        <p class="folu-section-subtitle">
          Following the approved Nigerian National Curriculum with practical enrichment.
        </p>
      </div>

      <div class="folu-pillars-grid">
        <div class="folu-pillar-card">
          <div class="folu-pillar-icon"><i class="fa fa-book"></i></div>
          <h3 class="folu-pillar-title">English Language &amp; Phonics</h3>
          <p class="folu-pillar-desc">
            Reading comprehension, spelling, grammar, creative writing, and public speaking ensuring eloquence and high expression.
          </p>
        </div>

        <div class="folu-pillar-card">
          <div class="folu-pillar-icon" style="background: var(--folu-gold-light); color: var(--folu-gold);"><i class="fa fa-calculator"></i></div>
          <h3 class="folu-pillar-title">Mathematics &amp; Reasoning</h3>
          <p class="folu-pillar-desc">
            Arithmetic, geometry, quantitative reasoning, word problems, and speed calculations that sharpen logical thinking.
          </p>
        </div>

        <div class="folu-pillar-card">
          <div class="folu-pillar-icon" style="background: var(--folu-green-light); color: var(--folu-green);"><i class="fa fa-flask"></i></div>
          <h3 class="folu-pillar-title">Basic Science &amp; Technology</h3>
          <p class="folu-pillar-desc">
            Living things, health and nutrition, environmental conservation, simple machines, and foundational computer operations.
          </p>
        </div>

        <div class="folu-pillar-card">
          <div class="folu-pillar-icon" style="background: #ede9fe; color: #7c3aed;"><i class="fa fa-globe"></i></div>
          <h3 class="folu-pillar-title">Social Studies &amp; Cultural Arts</h3>
          <p class="folu-pillar-desc">
            Civic education, history, community living, fine arts, music, and indigenous culture developing proud Nigerian citizens.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- 4. SECOND EDITORIAL BLOCK: EXTRA-CURRICULAR & MILESTONES -->
  <section class="folu-section">
    <div class="folu-container">
      <div class="folu-editorial-grid reverse">
        <div class="folu-editorial-visual">
          <div class="folu-editorial-img-frame">
            <img src="{{ asset('images/prigrad2.jpg') }}" alt="Primary School Academic Milestones at Folu International Group of Schools" loading="lazy">
          </div>
        </div>
        <div class="folu-editorial-text">
          <span class="folu-section-badge">Student Life &amp; Milestones</span>
          <h3 style="font-size: 28px; font-weight: 800; color: var(--folu-navy); margin-bottom: 14px;">Celebrating Every Milestone of Learning</h3>
          <p style="font-size: 15.5px; color: var(--folu-text-body); line-height: 1.65; margin-bottom: 16px;">
            At Folu International Group of Schools, we believe that academic progress should be accompanied by joyous celebrations of growth, discipline, and achievement.
          </p>
          <p style="font-size: 14.5px; color: var(--folu-text-muted); line-height: 1.65; margin-bottom: 22px;">
            From inter-house sports competitions to end-of-year prize-giving days and graduation ceremonies, our pupils learn the value of sportsmanship, graceful winning, and perseverance.
          </p>
          <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="{{ url('/gallery') }}" class="folu-btn folu-btn-outline" style="border-color: var(--folu-navy); color: var(--folu-navy);">View School Gallery</a>
            <a href="{{ url('/apply') }}" class="folu-btn folu-btn-primary">Apply Now</a>
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
          <span class="folu-section-badge" style="background: rgba(217, 119, 6, 0.2); color: var(--folu-gold-accent);">Primary School Enrolment</span>
          <h2 class="folu-cta-title">Prepare Your Child for Long-Term Academic Success</h2>
          <p class="folu-cta-text">
            Enroll your child in a primary school where knowledge is deep, discipline is cherished, and every learner matters.
          </p>
        </div>
        <div class="folu-cta-buttons">
          <a href="{{ url('/apply') }}" class="folu-btn folu-btn-primary">Enrol Today</a>
          <a href="https://wa.me/2348165354191?text=Hello%20Folu%20International%20Schools,%20I%20want%20to%20enquire%20about%20Primary%20School%20admission." target="_blank" rel="noopener noreferrer" class="folu-btn folu-btn-outline" style="border-color: #22c55e; color: #22c55e;">
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
