<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Academics Overview | FOLU INTERNATIONAL GROUP OF SCHOOLS | Isanlu, Kogi State</title>
  <meta name="description" content="Explore our academic philosophy, national curriculum excellence, and progressive educational pathways from Creche through Secondary at Folu International Group of Schools.">
  <meta name="keywords" content="Academics Folu International Schools, Nigerian National Curriculum, Isanlu Schools, Creche Nursery Primary Secondary Kogi State">

  <!-- OpenGraph / Social Metadata -->
  <meta property="og:title" content="Academics Overview | Folu International Group of Schools">
  <meta property="og:description" content="Nurturing academic distinction and disciplined leadership across 4 foundational schools in Isanlu, Kogi State.">
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
          <span class="current">Academics</span>
        </nav>
        <h1 class="folu-page-title">Inspiring Academic Distinction &amp; Intellectual Rigour</h1>
        <p class="folu-page-subtitle">
          At Folu International Group of Schools, learning is designed to ignite curiosity, instill disciplined study habits, and prepare students for superior performance in state, national, and international examinations.
        </p>
      </div>
    </div>
  </header>

  <!-- 2. ACADEMIC PHILOSOPHY (EDITORIAL SECTION) -->
  <section class="folu-section">
    <div class="folu-container">
      <div class="folu-editorial-grid">
        <div class="folu-editorial-text">
          <span class="folu-section-badge">Academic Philosophy</span>
          <h2 class="folu-section-title" style="font-size: 34px;">Beyond Rote Learning: Developing Thinkers and Innovators</h2>
          <p style="font-size: 16px; color: var(--folu-text-body); line-height: 1.7; margin-bottom: 18px;">
            Our academic structure follows the enriched <strong>Nigerian National Curriculum (NERDC)</strong>, enhanced with interactive teaching methods, modern STEM practicals, digital literacy, and expressive arts.
          </p>
          <p style="font-size: 15px; color: var(--folu-text-muted); line-height: 1.7; margin-bottom: 24px;">
            We believe that true education does not simply transfer textbook notes into exercise books. It cultivates the student's capacity to analyze problems, communicate persuasively, and apply classroom theory to real-world community challenges.
          </p>

          <div class="folu-values-grid" style="grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 28px;">
            <div style="background: var(--folu-surface-subtle); padding: 18px; border-radius: var(--folu-radius-sm); border-left: 3px solid var(--folu-blue);">
              <h4 style="font-size: 15px; color: var(--folu-navy); margin-bottom: 4px;">Small Class Sizes</h4>
              <p style="font-size: 13px; color: var(--folu-text-muted); margin: 0;">Ensuring personalized attention and prompt feedback for every learner.</p>
            </div>
            <div style="background: var(--folu-surface-subtle); padding: 18px; border-radius: var(--folu-radius-sm); border-left: 3px solid var(--folu-gold);">
              <h4 style="font-size: 15px; color: var(--folu-navy); margin-bottom: 4px;">Continuous Evaluation</h4>
              <p style="font-size: 13px; color: var(--folu-text-muted); margin: 0;">Regular diagnostic tests and termly assessments tracking individual progress.</p>
            </div>
          </div>

          <div style="display: flex; gap: 14px; flex-wrap: wrap;">
            <a href="{{ url('/apply') }}" class="folu-btn folu-btn-primary">Apply for Admission</a>
            <a href="{{ url('/contact') }}" class="folu-btn folu-btn-secondary">Speak With a Teacher</a>
          </div>
        </div>

        <div class="folu-editorial-visual">
          <div class="folu-editorial-img-frame">
            <img src="{{ asset('images/staff.jpg') }}" alt="Academic Faculty at Folu International Group of Schools" loading="lazy">
          </div>
          <div class="folu-editorial-badge">
            <div class="folu-editorial-badge-number">Dedicated</div>
            <div class="folu-editorial-badge-label">Experienced, caring subject teachers committed to student success</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 3. OUR FOUR EDUCATIONAL STAGES -->
  <section class="folu-section folu-section-subtle">
    <div class="folu-container">
      <div class="folu-section-header">
        <span class="folu-section-badge">Educational Pathway</span>
        <h2 class="folu-section-title">The Four Schools of Folu International</h2>
        <p class="folu-section-subtitle">
          A seamless academic journey providing continuous guidance from infancy through secondary graduation.
        </p>
      </div>

      <div class="folu-journey-grid">
        
        <!-- Creche -->
        <div class="folu-journey-card">
          <div class="folu-journey-img-wrap">
            <img src="{{ asset('images/nurgrad.jpg') }}" alt="Creche &amp; Early Years - Folu International" class="folu-journey-img" loading="lazy">
            <span class="folu-journey-badge">Early Years</span>
          </div>
          <div class="folu-journey-body">
            <h3 class="folu-journey-title">Creche &amp; Playgroup</h3>
            <p class="folu-journey-desc">
              Safe, tender, and stimulating care for infants and toddlers. Focuses on motor skills, sensory exploration, and emotional security.
            </p>
            <div class="folu-journey-footer">
              <a href="{{ url('/creche') }}" class="folu-journey-link">Explore Creche &rarr;</a>
            </div>
          </div>
        </div>

        <!-- Nursery -->
        <div class="folu-journey-card">
          <div class="folu-journey-img-wrap">
            <img src="{{ asset('images/nurgrad.jpg') }}" alt="Nursery School - Folu International" class="folu-journey-img" loading="lazy">
            <span class="folu-journey-badge" style="background: var(--folu-gold);">Nursery 1 - 3</span>
          </div>
          <div class="folu-journey-body">
            <h3 class="folu-journey-title">Nursery School</h3>
            <p class="folu-journey-desc">
              Building early reading, phonics, number sense, and social confidence through joyful, structured activity and peer interaction.
            </p>
            <div class="folu-journey-footer">
              <a href="{{ url('/creche') }}" class="folu-journey-link">Explore Nursery &rarr;</a>
            </div>
          </div>
        </div>

        <!-- Primary -->
        <div class="folu-journey-card">
          <div class="folu-journey-img-wrap">
            <img src="{{ asset('images/prigrad.jpg') }}" alt="Primary School - Folu International" class="folu-journey-img" loading="lazy">
            <span class="folu-journey-badge" style="background: var(--folu-green);">Basic 1 - 6</span>
          </div>
          <div class="folu-journey-body">
            <h3 class="folu-journey-title">Primary School</h3>
            <p class="folu-journey-desc">
              Solid foundation in English, Mathematics, Sciences, ICT, and civic responsibility. Instilling disciplined study and active participation.
            </p>
            <div class="folu-journey-footer">
              <a href="{{ url('/primary') }}" class="folu-journey-link">Explore Primary &rarr;</a>
            </div>
          </div>
        </div>

        <!-- Secondary -->
        <div class="folu-journey-card">
          <div class="folu-journey-img-wrap">
            <img src="{{ asset('images/j3grad.jpg') }}" alt="Secondary School College - Folu International" class="folu-journey-img" loading="lazy">
            <span class="folu-journey-badge" style="background: #7c3aed;">JSS 1 - SSS 3</span>
          </div>
          <div class="folu-journey-body">
            <h3 class="folu-journey-title">Secondary School (College)</h3>
            <p class="folu-journey-desc">
              Rigorous preparation for WAEC, NECO, and JAMB UTME. Dedicated Science, Arts, and Commercial streams shaping tomorrow's professionals.
            </p>
            <div class="folu-journey-footer">
              <a href="{{ url('/secondary') }}" class="folu-journey-link">Explore Secondary &rarr;</a>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- 4. ENRICHED CO-CURRICULAR & ACADEMIC COMPETITIONS -->
  <section class="folu-section">
    <div class="folu-container">
      <div class="folu-section-header">
        <span class="folu-section-badge">Enrichment</span>
        <h2 class="folu-section-title">Co-Curricular &amp; Academic Competitions</h2>
        <p class="folu-section-subtitle">
          Academics at Folu International extends beyond textbooks to foster real-life mastery, debate, and discovery.
        </p>
      </div>

      <div class="folu-values-grid">
        <div class="folu-value-card">
          <div class="folu-value-icon"><i class="fa fa-microphone"></i></div>
          <h3 class="folu-value-title">Debates &amp; Public Speaking</h3>
          <p class="folu-value-desc">
            Equipping students to articulate logical arguments, express viewpoints respectfully, and build stage poise in front of audiences.
          </p>
        </div>

        <div class="folu-value-card">
          <div class="folu-value-icon" style="background: var(--folu-gold-light); color: var(--folu-gold);"><i class="fa fa-calculator"></i></div>
          <h3 class="folu-value-title">Mathematics &amp; Spelling Bees</h3>
          <p class="folu-value-desc">
            Inter-class and inter-school academic contests that stimulate intellectual sharpness, speed, vocabulary, and quantitative agility.
          </p>
        </div>

        <div class="folu-value-card">
          <div class="folu-value-icon" style="background: var(--folu-green-light); color: var(--folu-green);"><i class="fa fa-flask"></i></div>
          <h3 class="folu-value-title">Science &amp; Practical Exploration</h3>
          <p class="folu-value-desc">
            Hands-on science experiments, biological specimens observation, and practical mathematics linking theories to visible proof.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- 5. CTA -->
  <section class="folu-section" style="padding-top: 10px;">
    <div class="folu-container">
      <div class="folu-cta-card">
        <div class="folu-cta-content">
          <span class="folu-section-badge" style="background: rgba(217, 119, 6, 0.2); color: var(--folu-gold-accent);">Enrollment Open</span>
          <h2 class="folu-cta-title">Ready to Experience Academic Excellence?</h2>
          <p class="folu-cta-text">
            Give your child an enduring academic edge grounded in moral discipline. Apply online or schedule a tour of our classrooms in Itedo-Ijowa, Isanlu.
          </p>
        </div>
        <div class="folu-cta-buttons">
          <a href="{{ url('/apply') }}" class="folu-btn folu-btn-primary">Apply Now</a>
          <a href="{{ url('/contact') }}" class="folu-btn folu-btn-outline" style="border-color: rgba(255, 255, 255, 0.4); color: #ffffff;">Contact Campus</a>
        </div>
      </div>
    </div>
  </section>

  {{-- Verified Footer --}}
  @include('frontend.partials.footer')

</body>
</html>
