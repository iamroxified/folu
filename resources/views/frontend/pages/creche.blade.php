<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Creche &amp; Nursery School | FOLU INTERNATIONAL GROUP OF SCHOOLS | Isanlu, Kogi State</title>
  <meta name="description" content="Discover early years care, toddler stimulation, and foundational nursery education at Folu International Group of Schools in Itedo-Ijowa, Isanlu, Kogi State.">
  <meta name="keywords" content="Creche Isanlu, Nursery School Isanlu, Early Childhood Education Kogi State, Folu International Nursery">

  <!-- OpenGraph / Social Metadata -->
  <meta property="og:title" content="Creche &amp; Nursery School | Folu International Group of Schools">
  <meta property="og:description" content="Loving care, early sensory discovery, and joyful foundational learning in a safe, hygienic environment.">
  <meta property="og:image" content="{{ asset('images/nurgrad.jpg') }}">
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
          <span class="current">Creche &amp; Nursery</span>
        </nav>
        <h1 class="folu-page-title">Gentle Care &amp; Early Joyful Learning</h1>
        <p class="folu-page-subtitle">
          From infants taking their very first steps to energetic preschoolers developing phonics and number recognition, our Early Years section provides a tender, stimulating, and secure environment in Itedo-Ijowa, Isanlu.
        </p>
      </div>
    </div>
  </header>

  <!-- 2. EDITORIAL OVERVIEW -->
  <section class="folu-section">
    <div class="folu-container">
      <div class="folu-editorial-grid">
        <div class="folu-editorial-text">
          <span class="folu-section-badge">Early Years Foundation</span>
          <h2 class="folu-section-title" style="font-size: 34px;">Where Every Child's Academic Journey Begins with Love</h2>
          <p style="font-size: 16px; color: var(--folu-text-body); line-height: 1.7; margin-bottom: 18px;">
            The earliest years of childhood are the most critical window for cognitive, emotional, and social development. At <strong>Folu International Group of Schools</strong>, our Creche and Nursery classes are specifically designed to spark wonder, encourage speech, and build self-confidence.
          </p>
          <p style="font-size: 15px; color: var(--folu-text-muted); line-height: 1.7; margin-bottom: 24px;">
            Under the watchful eyes of dedicated, compassionate early-childhood caregivers, children learn to navigate friendships, express their feelings constructively, and fall in love with reading through songs, rhymes, and hands-on toys.
          </p>

          <ul class="folu-checklist" style="margin-bottom: 28px;">
            <li><i class="fa fa-check-circle"></i> <strong>Hygienic &amp; Child-Safe Environment:</strong> Clean, airy, padded play spaces and nap areas.</li>
            <li><i class="fa fa-check-circle"></i> <strong>Jolly Phonics &amp; Early Literacy:</strong> Systematic sound blending establishing confident readers early.</li>
            <li><i class="fa fa-check-circle"></i> <strong>Loving, Attentive Caregivers:</strong> Warm, maternal attention ensuring every toddler feels cherished and safe.</li>
            <li><i class="fa fa-check-circle"></i> <strong>Socialization &amp; Manners:</strong> Polite greeting, sharing, and respectful interaction modeled daily.</li>
          </ul>

          <div style="display: flex; gap: 14px; flex-wrap: wrap;">
            <a href="{{ url('/apply') }}" class="folu-btn folu-btn-primary">Enrol in Creche / Nursery</a>
            <a href="{{ url('/contact') }}" class="folu-btn folu-btn-secondary">Tour the Early Years Facility</a>
          </div>
        </div>

        <div class="folu-editorial-visual">
          <div class="folu-editorial-img-frame">
            <img src="{{ asset('images/nurgrad.jpg') }}" alt="Nursery Graduation &amp; Milestones - Folu International Group of Schools" loading="lazy">
          </div>
          <div class="folu-editorial-badge">
            <div class="folu-editorial-badge-number">Ages 3m &ndash; 5y</div>
            <div class="folu-editorial-badge-label">Creche, Playgroup, Pre-Nursery &amp; Nursery 1 - 3</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 3. LEARNING STAGES IN EARLY YEARS -->
  <section class="folu-section folu-section-subtle">
    <div class="folu-container">
      <div class="folu-section-header">
        <span class="folu-section-badge">Developmental Stages</span>
        <h2 class="folu-section-title">Nurturing Every Step of Growth</h2>
        <p class="folu-section-subtitle">
          Tailored daily schedules designed to support fine motor skills, cognitive curiosity, and early discipline.
        </p>
      </div>

      <div class="folu-values-grid">
        <div class="folu-value-card">
          <div class="folu-value-icon"><i class="fa fa-heart"></i></div>
          <h3 class="folu-value-title">Creche &amp; Playgroup (3m &ndash; 2y)</h3>
          <p class="folu-value-desc">
            Attentive diapering, feeding assistance, tummy time, sensory soft toys, nursery rhymes, and supervised naps in a tranquil setting.
          </p>
        </div>

        <div class="folu-value-card">
          <div class="folu-value-icon" style="background: var(--folu-gold-light); color: var(--folu-gold);"><i class="fa fa-cubes"></i></div>
          <h3 class="folu-value-title">Pre-Nursery (Ages 2 &ndash; 3)</h3>
          <p class="folu-value-desc">
            Vocabulary enrichment, color and shape identification, fine motor grip with crayons and playdough, and active song circles.
          </p>
        </div>

        <div class="folu-value-card">
          <div class="folu-value-icon" style="background: var(--folu-green-light); color: var(--folu-green);"><i class="fa fa-book"></i></div>
          <h3 class="folu-value-title">Nursery 1 &ndash; 3 (Ages 3 &ndash; 5)</h3>
          <p class="folu-value-desc">
            Structured phonics instruction, letter tracing, number bonds 1-100, basic handwriting, science curiosity, and smooth preparation for Primary 1.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- 4. CTA BANNER -->
  <section class="folu-section" style="padding-top: 10px;">
    <div class="folu-container">
      <div class="folu-cta-card">
        <div class="folu-cta-content">
          <span class="folu-section-badge" style="background: rgba(217, 119, 6, 0.2); color: var(--folu-gold-accent);">Early Years Enrollment</span>
          <h2 class="folu-cta-title">Give Your Toddler the Best Headstart</h2>
          <p class="folu-cta-text">
            Join the happy family of parents who trust Folu International Group of Schools for their children's foundational years in Itedo-Ijowa, Isanlu.
          </p>
        </div>
        <div class="folu-cta-buttons">
          <a href="{{ url('/apply') }}" class="folu-btn folu-btn-primary">Apply Online</a>
          <a href="https://wa.me/2348165354191?text=Hello%20Folu%20International%20Schools,%20I%20want%20to%20enquire%20about%20Creche%20and%20Nursery%20admission." target="_blank" rel="noopener noreferrer" class="folu-btn folu-btn-outline" style="border-color: #22c55e; color: #22c55e;">
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
