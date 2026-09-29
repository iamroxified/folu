<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>About Us | FOLU INTERNATIONAL GROUP OF SCHOOLS | Isanlu, Kogi State</title>
  <meta name="description" content="Discover the story, vision, leadership, and educational philosophy of Folu International Group of Schools in Itedo-Ijowa, Isanlu, Kogi State. Nurturing leaders with Knowledge &amp; Discipline.">
  <meta name="keywords" content="About Folu International Schools, Isanlu School, Rev Dr Samuel Babaniyi, Rev Mrs Mofoluwake Babaniyi, Knowledge and Discipline, Kogi State School">

  <!-- OpenGraph / Social Metadata -->
  <meta property="og:title" content="About Us | Folu International Group of Schools">
  <meta property="og:description" content="Knowledge &amp; Discipline: Nurturing tomorrow's leaders through academic excellence and godly character in Isanlu, Kogi State.">
  <meta property="og:image" content="<?php echo e(asset('images/folu-logo.png')); ?>">
  <meta property="og:type" content="website">

  <!-- Favicon & Icons -->
  <link rel="icon" href="<?php echo e(asset('images/folu-logo.png')); ?>" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  
  <!-- Modern Folu 2026 Stylesheet -->
  <link rel="stylesheet" href="<?php echo e(asset('css/folu-modern.css')); ?>" type="text/css" media="all">
</head>

<body class="folu-theme">

  
  <?php echo $__env->make('frontend.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <!-- 1. PAGE HEADER BANNER -->
  <header class="folu-page-header">
    <div class="folu-container">
      <div class="folu-page-header-content">
        <nav class="folu-breadcrumb" aria-label="Breadcrumb">
          <a href="<?php echo e(url('/')); ?>">Home</a>
          <span class="sep">/</span>
          <span class="current">About Us</span>
        </nav>
        <h1 class="folu-page-title">Rooted in Knowledge. Guided by Discipline.</h1>
        <p class="folu-page-subtitle">
          Established in Itedo-Ijowa, Isanlu, Kogi State, Folu International Group of Schools provides a comprehensive academic home where children grow from foundational early care to confident secondary school graduates.
        </p>
      </div>
    </div>
  </header>

  <!-- 2. WHO WE ARE & OUR STORY (EDITORIAL LAYOUT) -->
  <section class="folu-section">
    <div class="folu-container">
      <div class="folu-editorial-grid">
        
        <div class="folu-editorial-text">
          <span class="folu-section-badge">Who We Are</span>
          <h2 class="folu-section-title" style="font-size: 34px;">A Community Dedicated to Raising Purposeful Leaders</h2>
          
          <p style="font-size: 16px; color: var(--folu-text-body); line-height: 1.7; margin-bottom: 20px;">
            <strong>Folu International Group of Schools</strong> was founded on a steadfast conviction: that every child is endowed with boundless divine potential that requires purposeful teaching, moral guardianship, and a disciplined atmosphere to blossom into leadership.
          </p>
          
          <p style="font-size: 15px; color: var(--folu-text-muted); line-height: 1.7; margin-bottom: 24px;">
            Located in the serene and conducive community of <strong>Itedo-Ijowa, Isanlu, Kogi State</strong>, our campus provides an oasis of focused study away from city distractions. Here, modern learning methods harmonize seamlessly with time-tested values of diligence, integrity, and mutual respect.
          </p>

          <div style="background: var(--folu-blue-soft); border-left: 4px solid var(--folu-blue); padding: 18px 22px; border-radius: var(--folu-radius-sm); margin-bottom: 28px;">
            <h4 style="color: var(--folu-navy); margin-bottom: 6px; font-size: 16px;">Our Guiding Motto: Knowledge &amp; Discipline</h4>
            <p style="font-size: 14px; color: var(--folu-text-body); margin: 0; line-height: 1.55;">
              We believe that knowledge without discipline is hazardous, and discipline without knowledge is incomplete. Together, they form the bedrock of enduring personal achievement and service to humanity.
            </p>
          </div>

          <div style="display: flex; gap: 14px; flex-wrap: wrap;">
            <a href="<?php echo e(url('/apply')); ?>" class="folu-btn folu-btn-primary">Apply for Admission</a>
            <a href="<?php echo e(url('/contact')); ?>" class="folu-btn folu-btn-secondary">Visit Our Campus</a>
          </div>
        </div>

        <div class="folu-editorial-visual">
          <div class="folu-editorial-img-frame">
            <img src="<?php echo e(asset('images/assembly.jpg')); ?>" alt="Students at Morning Assembly - Folu International Group of Schools" loading="lazy">
          </div>
          <div class="folu-editorial-badge">
            <div class="folu-editorial-badge-number">4 Levels</div>
            <div class="folu-editorial-badge-label">Creche, Nursery, Primary &amp; Secondary in Isanlu, Kogi State</div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- 3. VISION, MISSION & VALUES -->
  <section class="folu-section folu-section-subtle">
    <div class="folu-container">
      <div class="folu-section-header">
        <span class="folu-section-badge">Guiding Purpose</span>
        <h2 class="folu-section-title">Vision, Mission &amp; Core Values</h2>
        <p class="folu-section-subtitle">
          Our daily operations and academic milestones are anchored on clear institutional goals and godly principles.
        </p>
      </div>

      <div class="folu-values-grid">
        <!-- Vision -->
        <div class="folu-value-card">
          <div class="folu-value-icon">
            <i class="fa fa-eye"></i>
          </div>
          <h3 class="folu-value-title">Our Vision</h3>
          <p class="folu-value-desc">
            To be a benchmark educational institution in Nigeria, renowned for nurturing disciplined, morally upright, intellectually vibrant, and visionary leaders who transform their communities and the world.
          </p>
        </div>

        <!-- Mission -->
        <div class="folu-value-card">
          <div class="folu-value-icon" style="background: var(--folu-gold-light); color: var(--folu-gold);">
            <i class="fa fa-bullseye"></i>
          </div>
          <h3 class="folu-value-title">Our Mission</h3>
          <p class="folu-value-desc">
            To provide high-quality, comprehensive education combining rigorous academics, moral uprightness, modern practical skills, and character formation in a safe and supportive learning environment.
          </p>
        </div>

        <!-- Motto Anchor -->
        <div class="folu-value-card">
          <div class="folu-value-icon" style="background: var(--folu-green-light); color: var(--folu-green);">
            <i class="fa fa-shield"></i>
          </div>
          <h3 class="folu-value-title">Knowledge &amp; Discipline</h3>
          <p class="folu-value-desc">
            Instilling a deep love for learning alongside self-mastery, respect for authority, punctuality, and personal accountability across every stage of the student journey.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- 4. LEADERSHIP & FOUNDERS SPOTLIGHT -->
  <section class="folu-section">
    <div class="folu-container">
      <div class="folu-section-header">
        <span class="folu-section-badge">School Leadership</span>
        <h2 class="folu-section-title">Guided by Experienced Stewards</h2>
        <p class="folu-section-subtitle">
          Our school flourishes under the visionary, devoted leadership of our Proprietor and Proprietress.
        </p>
      </div>

      <div class="folu-leadership-grid">
        <!-- Proprietor -->
        <div class="folu-leadership-card">
          <div class="folu-leadership-avatar">
            <span>SB</span>
          </div>
          <div class="folu-leadership-info">
            <h3 class="folu-leadership-title">Rev. Dr. Samuel Babaniyi</h3>
            <div class="folu-leadership-role">Proprietor</div>
            <p class="folu-leadership-bio">
              A passionate educationist and community leader whose life commitment is the empowerment of young minds through quality education, Christian virtues, and disciplined mentorship. Under his stewardship, Folu International continues to expand its academic frontiers.
            </p>
          </div>
        </div>

        <!-- Proprietress -->
        <div class="folu-leadership-card">
          <div class="folu-leadership-avatar" style="border-color: var(--folu-blue); background: var(--folu-navy-dark);">
            <span>MB</span>
          </div>
          <div class="folu-leadership-info">
            <h3 class="folu-leadership-title">Rev. Mrs. Mofoluwake Babaniyi</h3>
            <div class="folu-leadership-role">Proprietress</div>
            <p class="folu-leadership-bio">
              A devoted mother, educator, and administrator dedicated to the holistic well-being, moral health, and academic nurturing of every child entrusted to Folu International Group of Schools. Her warmth and high standards shape our daily campus culture.
            </p>
          </div>
        </div>
      </div>

      <!-- Staff & Teachers Banner -->
      <div style="margin-top: 50px; background: var(--folu-surface); border-radius: var(--folu-radius-lg); border: 1px solid var(--folu-border); padding: 40px 36px; box-shadow: var(--folu-shadow-sm);">
        <div class="folu-editorial-grid reverse">
          <div class="folu-editorial-visual">
            <div class="folu-editorial-img-frame">
              <img src="<?php echo e(asset('images/staff.jpg')); ?>" alt="Academic and Support Staff of Folu International Group of Schools" loading="lazy">
            </div>
          </div>
          <div class="folu-editorial-text">
            <span class="folu-section-badge">Our Educators</span>
            <h3 style="font-size: 26px; font-weight: 800; color: var(--folu-navy); margin-bottom: 12px;">Dedicated, Qualified &amp; Caring Teachers</h3>
            <p style="font-size: 15px; color: var(--folu-text-body); line-height: 1.65; margin-bottom: 16px;">
              Behind our students' steady progress is a committed faculty of seasoned teachers who take personal interest in each learner's comprehension, emotional welfare, and moral development.
            </p>
            <p style="font-size: 14.5px; color: var(--folu-text-muted); line-height: 1.6; margin-bottom: 22px;">
              Our teachers undergo regular pedagogical training, fostering an encouraging classroom where curiosity is rewarded, disciplined study habits are formed, and academic rigor is upheld.
            </p>
            <a href="<?php echo e(url('/overview-academics')); ?>" class="folu-btn folu-btn-outline" style="border-color: var(--folu-navy); color: var(--folu-navy);">Explore Our Academics</a>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- 5. THE FOUR PILLARS OF EXCELLENCE -->
  <section class="folu-section folu-section-subtle">
    <div class="folu-container">
      <div class="folu-section-header">
        <span class="folu-section-badge">Educational Philosophy</span>
        <h2 class="folu-section-title">The Four Pillars of the Folu Experience</h2>
        <p class="folu-section-subtitle">
          How we shape well-rounded, balanced young Nigerians ready for the opportunities and challenges of tomorrow.
        </p>
      </div>

      <div class="folu-pillars-grid">
        <div class="folu-pillar-card">
          <div class="folu-pillar-icon"><i class="fa fa-graduation-cap"></i></div>
          <h3 class="folu-pillar-title">1. Academic Distinction</h3>
          <p class="folu-pillar-desc">
            A comprehensive curriculum delivering strong foundations in literacy, quantitative reasoning, sciences, and humanities, preparing students thoroughly for national examinations.
          </p>
        </div>

        <div class="folu-pillar-card">
          <div class="folu-pillar-icon" style="background: var(--folu-gold-light); color: var(--folu-gold);"><i class="fa fa-balance-scale"></i></div>
          <h3 class="folu-pillar-title">2. Moral Character</h3>
          <p class="folu-pillar-desc">
            Values-based instruction that emphasizes honesty, self-discipline, respect for elders, diligence, and accountability in personal conduct and academic work.
          </p>
        </div>

        <div class="folu-pillar-card">
          <div class="folu-pillar-icon" style="background: var(--folu-green-light); color: var(--folu-green);"><i class="fa fa-users"></i></div>
          <h3 class="folu-pillar-title">3. Leadership &amp; Voice</h3>
          <p class="folu-pillar-desc">
            Prefectship opportunities, public speaking, debates, spelling competitions, and student assemblies where children learn to articulate their thoughts with confidence.
          </p>
        </div>

        <div class="folu-pillar-card">
          <div class="folu-pillar-icon" style="background: #ede9fe; color: #7c3aed;"><i class="fa fa-trophy"></i></div>
          <h3 class="folu-pillar-title">4. Wholesome Growth</h3>
          <p class="folu-pillar-desc">
            Vibrant inter-house sports, educational excursions, cultural celebrations, creative arts, and clubs that uncover latent talents and build lasting camaraderie.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- 6. HIGH CONVERSION CTA BANNER -->
  <section class="folu-section" style="padding-top: 20px;">
    <div class="folu-container">
      <div class="folu-cta-card">
        <div class="folu-cta-content">
          <span class="folu-section-badge" style="background: rgba(217, 119, 6, 0.2); color: var(--folu-gold-accent);">Join Our Family</span>
          <h2 class="folu-cta-title">Give Your Child the Gift of Knowledge &amp; Discipline</h2>
          <p class="folu-cta-text">
            Admissions are open for Creche, Nursery, Primary, and Secondary classes. Speak with our admissions team today or schedule a personal visit to our campus in Itedo-Ijowa, Isanlu.
          </p>
        </div>
        <div class="folu-cta-buttons">
          <a href="<?php echo e(url('/apply')); ?>" class="folu-btn folu-btn-primary" style="padding: 16px 32px; font-size: 15px;">
            Apply for Admission
          </a>
          <a href="https://wa.me/2348165354191?text=Hello%20Folu%20International%20Schools,%20I%20would%20like%20to%20enquire%20about%20admissions." target="_blank" rel="noopener noreferrer" class="folu-btn folu-btn-outline" style="border-color: #22c55e; color: #22c55e;">
            <i class="fa fa-whatsapp"></i> Chat on WhatsApp
          </a>
        </div>
      </div>
    </div>
  </section>

  
  <?php echo $__env->make('frontend.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</body>
</html>
<?php /**PATH C:\laragon\www\folu\resources\views/frontend/pages/about-us.blade.php ENDPATH**/ ?>