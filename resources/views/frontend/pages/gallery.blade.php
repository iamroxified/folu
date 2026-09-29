<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>School Gallery | FOLU INTERNATIONAL GROUP OF SCHOOLS | Isanlu, Kogi State</title>
  <meta name="description" content="View authentic photographs of life at Folu International Group of Schools: morning assemblies, graduations, academic milestones, inter-house sports, and excursions.">
  <meta name="keywords" content="Folu International Schools Gallery, Isanlu School Photos, Students Assembly Isanlu, Graduation Photos Kogi State">

  <!-- OpenGraph / Social Metadata -->
  <meta property="og:title" content="School Gallery | Folu International Group of Schools">
  <meta property="og:description" content="Life at Folu in pictures: academics, sports, graduation milestones, and excursions in Isanlu, Kogi State.">
  <meta property="og:image" content="{{ asset('images/assembly.jpg') }}">
  <meta property="og:type" content="website">

  <!-- Favicon & Icons -->
  <link rel="icon" href="{{ asset('images/folu-logo.png') }}" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

  <!-- Modern Folu 2026 Stylesheet -->
  <link rel="stylesheet" href="{{ asset('css/folu-modern.css') }}" type="text/css" media="all">

  <style>
    /* Lightbox Modal */
    .folu-lightbox-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(8, 18, 38, 0.95);
      z-index: 99999;
      justify-content: center;
      align-items: center;
      padding: 20px;
      box-sizing: border-box;
      backdrop-filter: blur(8px);
    }

    .folu-lightbox-content {
      max-width: 900px;
      width: 100%;
      background: var(--folu-navy-dark);
      border-radius: var(--folu-radius-md);
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, 0.15);
      position: relative;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    .folu-lightbox-img {
      width: 100%;
      max-height: 70vh;
      object-fit: contain;
      display: block;
      background: #000;
    }

    .folu-lightbox-footer {
      padding: 16px 24px;
      color: #fff;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .folu-lightbox-close {
      position: absolute;
      top: 14px;
      right: 18px;
      background: rgba(0, 0, 0, 0.6);
      color: #fff;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      cursor: pointer;
      border: 1px solid rgba(255, 255, 255, 0.3);
      transition: var(--folu-transition);
    }

    .folu-lightbox-close:hover {
      background: #dc2626;
    }
  </style>
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
          <span class="current">Gallery</span>
        </nav>
        <h1 class="folu-page-title">Life at Folu in Pictures</h1>
        <p class="folu-page-subtitle">
          Authentic moments capturing student life, morning assemblies, academic milestones, vibrant sports competitions, and educational excursions in Itedo-Ijowa, Isanlu.
        </p>
      </div>
    </div>
  </header>

  <!-- 2. GALLERY SECTION WITH CATEGORY FILTER -->
  <section class="folu-section">
    <div class="folu-container">

      <!-- Filter Tabs -->
      <div class="folu-gallery-filters">
        <button class="folu-filter-btn active" onclick="filterGallery('all', this)">All Moments</button>
        <button class="folu-filter-btn" onclick="filterGallery('academics', this)">Academics &amp; Assemblies</button>
        <button class="folu-filter-btn" onclick="filterGallery('milestones', this)">Graduations &amp; Milestones</button>
        <button class="folu-filter-btn" onclick="filterGallery('sports', this)">Sports &amp; Fitness</button>
        <button class="folu-filter-btn" onclick="filterGallery('faculty', this)">Staff &amp; Excursions</button>
      </div>

      <!-- Gallery Grid -->
      <div class="folu-gallery-grid" id="foluGalleryGrid">

        <!-- 1. Assembly -->
        <div class="folu-gallery-item" data-category="academics" onclick="openLightbox('{{ asset('images/assembly.jpg') }}', 'Morning Devotional &amp; School Assembly', 'Academics &amp; Discipline')">
          <img src="{{ asset('images/assembly.jpg') }}" alt="Morning Assembly at Folu International Group of Schools" class="folu-gallery-img" loading="lazy">
          <div class="folu-gallery-overlay">
            <span class="folu-gallery-category">Academics &amp; Discipline</span>
            <h4 class="folu-gallery-caption">Morning Assembly Order</h4>
          </div>
        </div>

        <!-- 2. Primary Graduation 1 -->
        <div class="folu-gallery-item" data-category="milestones" onclick="openLightbox('{{ asset('images/prigrad.jpg') }}', 'Primary School Graduation Ceremony', 'Milestones &amp; Honors')">
          <img src="{{ asset('images/prigrad.jpg') }}" alt="Primary School Graduation at Folu International Group of Schools" class="folu-gallery-img" loading="lazy">
          <div class="folu-gallery-overlay">
            <span class="folu-gallery-category">Milestones &amp; Honors</span>
            <h4 class="folu-gallery-caption">Primary School Graduation</h4>
          </div>
        </div>

        <!-- 3. Primary Graduation 2 -->
        <div class="folu-gallery-item" data-category="milestones" onclick="openLightbox('{{ asset('images/prigrad2.jpg') }}', 'Academic Prize-Giving &amp; Student Accolades', 'Milestones &amp; Honors')">
          <img src="{{ asset('images/prigrad2.jpg') }}" alt="Academic Milestones at Folu International Group of Schools" class="folu-gallery-img" loading="lazy">
          <div class="folu-gallery-overlay">
            <span class="folu-gallery-category">Milestones &amp; Honors</span>
            <h4 class="folu-gallery-caption">Academic Prize-Giving</h4>
          </div>
        </div>

        <!-- 4. JSS 3 Graduation -->
        <div class="folu-gallery-item" data-category="milestones" onclick="openLightbox('{{ asset('images/j3grad.jpg') }}', 'Junior Secondary JSS 3 Transition Milestone', 'Secondary College')">
          <img src="{{ asset('images/j3grad.jpg') }}" alt="Junior Secondary Graduating Class at Folu International Group of Schools" class="folu-gallery-img" loading="lazy">
          <div class="folu-gallery-overlay">
            <span class="folu-gallery-category">Secondary College</span>
            <h4 class="folu-gallery-caption">JSS 3 Graduation Milestone</h4>
          </div>
        </div>

        <!-- 5. Nursery Graduation -->
        <div class="folu-gallery-item" data-category="milestones" onclick="openLightbox('{{ asset('images/nurgrad.jpg') }}', 'Early Years &amp; Nursery Milestone Celebration', 'Early Childhood')">
          <img src="{{ asset('images/nurgrad.jpg') }}" alt="Nursery School Milestone at Folu International Group of Schools" class="folu-gallery-img" loading="lazy">
          <div class="folu-gallery-overlay">
            <span class="folu-gallery-category">Early Childhood</span>
            <h4 class="folu-gallery-caption">Nursery Milestone Celebration</h4>
          </div>
        </div>

        <!-- 6. Student Sports -->
        <div class="folu-gallery-item" data-category="sports" onclick="openLightbox('{{ asset('images/studentsport.jpg') }}', 'Annual Inter-House Sports &amp; Track Events', 'Athletics &amp; Wellness')">
          <img src="{{ asset('images/studentsport.jpg') }}" alt="Student Sports &amp; Athletic Day at Folu International Group of Schools" class="folu-gallery-img" loading="lazy">
          <div class="folu-gallery-overlay">
            <span class="folu-gallery-category">Athletics &amp; Wellness</span>
            <h4 class="folu-gallery-caption">Inter-House Sports Day</h4>
          </div>
        </div>

        <!-- 7. Staff Sports -->
        <div class="folu-gallery-item" data-category="sports" onclick="openLightbox('{{ asset('images/staffsport.jpg') }}', 'Staff &amp; Faculty Sports &amp; Team Bonding', 'Community')">
          <img src="{{ asset('images/staffsport.jpg') }}" alt="Staff Sports and Team Building at Folu International Group of Schools" class="folu-gallery-img" loading="lazy">
          <div class="folu-gallery-overlay">
            <span class="folu-gallery-category">Community</span>
            <h4 class="folu-gallery-caption">Staff &amp; Faculty Sports</h4>
          </div>
        </div>

        <!-- 8. Staff Faculty -->
        <div class="folu-gallery-item" data-category="faculty" onclick="openLightbox('{{ asset('images/staff.jpg') }}', 'The Academic &amp; Administrative Faculty of Folu International', 'Educators')">
          <img src="{{ asset('images/staff.jpg') }}" alt="Academic and Support Staff of Folu International Group of Schools" class="folu-gallery-img" loading="lazy">
          <div class="folu-gallery-overlay">
            <span class="folu-gallery-category">Educators</span>
            <h4 class="folu-gallery-caption">Our Dedicated Faculty</h4>
          </div>
        </div>

        <!-- 9. Excursion -->
        <div class="folu-gallery-item" data-category="faculty" onclick="openLightbox('{{ asset('images/excorsion.jpg') }}', 'Educational Excursions &amp; Out-of-Classroom Exploration', 'Field Learning')">
          <img src="{{ asset('images/excorsion.jpg') }}" alt="Educational Excursion by Folu International Group of Schools" class="folu-gallery-img" loading="lazy">
          <div class="folu-gallery-overlay">
            <span class="folu-gallery-category">Field Learning</span>
            <h4 class="folu-gallery-caption">Educational Excursion</h4>
          </div>
        </div>

      </div>

    </div>
  </section>

  <!-- 3. CTA BANNER -->
  <section class="folu-section folu-section-subtle" style="padding-top: 10px;">
    <div class="folu-container">
      <div class="folu-cta-card">
        <div class="folu-cta-content">
          <span class="folu-section-badge" style="background: rgba(231, 111, 81, 0.2); color: var(--folu-peach-accent);">Be Part of the Story</span>
          <h2 class="folu-cta-title" style="color:#fff">Give Your Child Memories That Build Leadership</h2>
          <p class="folu-cta-text">
            Every day at Folu International Group of Schools offers a meaningful opportunity to learn, discover, compete, create, lead, and grow.
          </p>
        </div>
        <div class="folu-cta-buttons">
          <a href="{{ url('/apply') }}" class="folu-btn folu-btn-peach">Apply for Admission</a>
          <a href="{{ url('/contact') }}" class="folu-btn folu-btn-white">Contact Campus</a>
        </div>
      </div>
    </div>
  </section>

  <!-- 4. LIGHTBOX MODAL -->
  <div class="folu-lightbox-modal" id="foluLightbox" onclick="closeLightbox(event)">
    <div class="folu-lightbox-content" onclick="event.stopPropagation();">
      <span class="folu-lightbox-close" onclick="closeLightbox()">&times;</span>
      <img src="" alt="" id="lightboxImg" class="folu-lightbox-img">
      <div class="folu-lightbox-footer">
        <div>
          <h4 id="lightboxTitle" style="margin: 0 0 4px 0; font-size: 17px; font-weight: 700; color: #fff;"></h4>
          <span id="lightboxCategory" style="font-size: 13px; color: var(--folu-gold-accent); font-weight: 600;"></span>
        </div>
      </div>
    </div>
  </div>

  {{-- Verified Footer --}}
  @include('frontend.partials.footer')

  <script>
    function filterGallery(category, btn) {
      // Toggle active class on filter buttons
      var buttons = document.querySelectorAll('.folu-filter-btn');
      buttons.forEach(function(b) {
        b.classList.remove('active');
      });
      btn.classList.add('active');

      // Filter gallery cards
      var items = document.querySelectorAll('.folu-gallery-item');
      items.forEach(function(item) {
        if (category === 'all' || item.getAttribute('data-category') === category) {
          item.style.display = 'block';
        } else {
          item.style.display = 'none';
        }
      });
    }

    function openLightbox(src, title, category) {
      document.getElementById('lightboxImg').src = src;
      document.getElementById('lightboxTitle').textContent = title;
      document.getElementById('lightboxCategory').textContent = category;
      document.getElementById('foluLightbox').style.display = 'flex';
      document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
      document.getElementById('foluLightbox').style.display = 'none';
      document.body.style.overflow = 'auto';
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeLightbox();
      }
    });
  </script>

</body>

</html>