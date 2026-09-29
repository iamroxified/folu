<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>FOLU INTERNATIONAL GROUP OF SCHOOLS | Knowledge &amp; Discipline | Isanlu, Kogi State</title>
  <meta name="description" content="Welcome to Folu International Group of Schools, Itedo-Ijowa, Isanlu, Kogi State. Creche, Nursery, Primary, and Secondary Education grounded in academic excellence, moral discipline, and leadership.">
  <meta name="keywords" content="Folu International Schools, Schools in Isanlu, Private School Kogi State, Nursery Primary Secondary School Isanlu, Knowledge and Discipline">

  <!-- OpenGraph / Social Metadata -->
  <meta property="og:title" content="Folu International Group of Schools | Knowledge &amp; Discipline">
  <meta property="og:description" content="Nurturing confident, disciplined, and capable leaders in Isanlu, Kogi State. Creche, Nursery, Primary, and Secondary.">
  <meta property="og:image" content="<?php echo e(asset('images/folu-logo.png')); ?>">
  <meta property="og:type" content="website">

  <!-- Fonts & Icons -->
  <link rel="icon" href="<?php echo e(asset('images/folu-logo.png')); ?>" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  
  <!-- Modern Folu 2026 Stylesheet -->
  <link rel="stylesheet" href="<?php echo e(asset('css/folu-modern.css')); ?>" type="text/css" media="all">
</head>

<body class="folu-theme">

  
  <?php echo $__env->make('frontend.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <!-- 1. HERO SECTION -->
  <section class="folu-hero">
    <div class="folu-container">
      <div class="folu-hero-grid">
        
        <!-- Left: Headline & Positioning Statement -->
        <div class="folu-hero-content">
          <div class="folu-hero-lead-pill">
            <span class="dot"></span>
            <span>Itedo-Ijowa, Isanlu, Kogi State &bull; Creche to Secondary</span>
          </div>
          
          <h1 class="folu-hero-title">
            KNOWLEDGE THAT BUILDS.
            <span class="folu-gold-text">DISCIPLINE THAT LEADS.</span>
          </h1>

          <p class="folu-hero-description">
            At Folu International Group of Schools, we nurture confident, disciplined and capable young people through rigorous academic instruction, moral character development, and meaningful discovery.
          </p>

          <div class="folu-hero-actions">
            <a href="<?php echo e(url('/apply')); ?>" class="folu-btn folu-btn-gold">
              <i class="fa fa-graduation-cap"></i>
              <span>Apply for Admission</span>
            </a>
            <a href="#why-folu" class="folu-btn folu-btn-outline" style="color: #ffffff !important; border-color: rgba(255, 255, 255, 0.35);">
              <i class="fa fa-compass"></i>
              <span>Explore Folu</span>
            </a>
          </div>

          <!-- Trust Badges -->
          <div class="folu-hero-badges">
            <div class="folu-hero-badge-item">
              <div class="folu-hero-badge-icon"><i class="fa fa-graduation-cap"></i></div>
              <div class="folu-hero-badge-text">Creche to Secondary</div>
            </div>
            <div class="folu-hero-badge-item">
              <div class="folu-hero-badge-icon"><i class="fa fa-shield"></i></div>
              <div class="folu-hero-badge-text">Character &amp; Discipline</div>
            </div>
            <div class="folu-hero-badge-item">
              <div class="folu-hero-badge-icon"><i class="fa fa-trophy"></i></div>
              <div class="folu-hero-badge-text">Academic Excellence</div>
            </div>
          </div>
        </div>

        <!-- Right: Authentic Visual Composition -->
        <div class="folu-hero-visual">
          <div class="folu-hero-image-card">
            <img src="<?php echo e(asset('images/assembly.jpg')); ?>" alt="Folu International School Students Assembly">
          </div>
          
          <!-- Floating Admission Notice Badge -->
          <div class="folu-hero-floating-badge">
            <i class="fa fa-id-card-o"></i>
            <div>
              <h4 class="folu-floating-badge-title">Admissions in Progress</h4>
              <p class="folu-floating-badge-sub">Creche &bull; Nursery &bull; Primary &bull; Secondary</p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- 2. QUICK ACTIONS BAR -->
  <section class="folu-quick-actions-bar">
    <div class="folu-container">
      <div class="folu-quick-actions-grid">
        
        <a href="<?php echo e(url('/apply')); ?>" class="folu-quick-card">
          <div>
            <div class="folu-quick-card-icon"><i class="fa fa-pencil-square-o"></i></div>
            <h3 class="folu-quick-card-title">Admissions</h3>
            <p class="folu-quick-card-desc">Begin your child's enrollment journey online or enquire about admission.</p>
          </div>
          <span class="folu-quick-card-link">Apply Now <i class="fa fa-arrow-right"></i></span>
        </a>

        <a href="<?php echo e(url('/overview-academics')); ?>" class="folu-quick-card">
          <div>
            <div class="folu-quick-card-icon"><i class="fa fa-book"></i></div>
            <h3 class="folu-quick-card-title">Our Schools</h3>
            <p class="folu-quick-card-desc">Creche, Nursery, Primary, and Secondary education under one standard.</p>
          </div>
          <span class="folu-quick-card-link">Explore Academics <i class="fa fa-arrow-right"></i></span>
        </a>

        <a href="<?php echo e(url('/gallery')); ?>" class="folu-quick-card">
          <div>
            <div class="folu-quick-card-icon"><i class="fa fa-futbol-o"></i></div>
            <h3 class="folu-quick-card-title">Student Life</h3>
            <p class="folu-quick-card-desc">Sports, competitions, cultural day, leadership, and creative development.</p>
          </div>
          <span class="folu-quick-card-link">Discover Student Life <i class="fa fa-arrow-right"></i></span>
        </a>

        <a href="<?php echo e(url('/contact')); ?>" class="folu-quick-card">
          <div>
            <div class="folu-quick-card-icon"><i class="fa fa-phone"></i></div>
            <h3 class="folu-quick-card-title">Contact &amp; Visit</h3>
            <p class="folu-quick-card-desc">Call, WhatsApp, or visit our campus in Itedo-Ijowa, Isanlu.</p>
          </div>
          <span class="folu-quick-card-link">Get in Touch <i class="fa fa-arrow-right"></i></span>
        </a>

      </div>
    </div>
  </section>

  <!-- 3. "WHY FOLU" SECTION -->
  <section class="folu-section" id="why-folu">
    <div class="folu-container">
      
      <div class="folu-section-header text-center">
        <span class="folu-badge-pill">
          <i class="fa fa-check-circle"></i> Why Choose Folu
        </span>
        <h2 class="folu-section-title">A Foundation Built for Life</h2>
        <p class="folu-section-subtitle">
          Folu International Schools is not just a place where children attend classes. It is a nurturing community where children learn, discover, compete, create, lead, and grow.
        </p>
      </div>

      <div class="folu-pillars-grid">
        
        <div class="folu-pillar-card">
          <div class="folu-pillar-icon"><i class="fa fa-graduation-cap"></i></div>
          <h3 class="folu-pillar-title">Academic Excellence</h3>
          <p class="folu-pillar-text">
            Structured learning and a deep foundation in literacy, numeracy, sciences, and critical reasoning designed for sustained academic success.
          </p>
        </div>

        <div class="folu-pillar-card">
          <div class="folu-pillar-icon"><i class="fa fa-shield"></i></div>
          <h3 class="folu-pillar-title">Character &amp; Discipline</h3>
          <p class="folu-pillar-text">
            Living by our motto "Knowledge &amp; Discipline," we cultivate responsible, respectful, and morally grounded young people who stand out with integrity.
          </p>
        </div>

        <div class="folu-pillar-card">
          <div class="folu-pillar-icon"><i class="fa fa-lightbulb-o"></i></div>
          <h3 class="folu-pillar-title">Student Development</h3>
          <p class="folu-pillar-text">
            Every child is encouraged to build self-confidence, articulate thoughts clearly, communicate effectively, and practice servant leadership.
          </p>
        </div>

        <div class="folu-pillar-card">
          <div class="folu-pillar-icon"><i class="fa fa-trophy"></i></div>
          <h3 class="folu-pillar-title">Beyond the Classroom</h3>
          <p class="folu-pillar-text">
            Enriching opportunities for students to participate in debates, mathematics competitions, sports, cultural exhibitions, and talent discovery.
          </p>
        </div>

      </div>

    </div>
  </section>

  <!-- 4. OUR SCHOOL JOURNEY -->
  <section class="folu-section folu-section-subtle" id="journey">
    <div class="folu-container">
      
      <div class="folu-section-header text-center">
        <span class="folu-badge-pill">
          <i class="fa fa-road"></i> Educational Pathways
        </span>
        <h2 class="folu-section-title">Our School Journey</h2>
        <p class="folu-section-subtitle">
          Guiding your child through distinct, progressive stages of learning, discovery, and character building from early years to secondary school graduation.
        </p>
      </div>

      <div class="folu-journey-grid">
        
        <!-- Creche -->
        <div class="folu-journey-card">
          <div class="folu-journey-image-wrap">
            <span class="folu-journey-tag">Foundation</span>
            <img src="<?php echo e(asset('images/nurgrad.jpg')); ?>" alt="Folu Creche">
          </div>
          <div class="folu-journey-content">
            <h3 class="folu-journey-title">Creche &amp; Daycare</h3>
            <p class="folu-journey-desc">
              Warm, hygienic, and attentive early care focused on gentle discovery, sensory development, and joyful beginnings.
            </p>
            <a href="<?php echo e(url('/creche')); ?>" class="folu-btn folu-btn-outline" style="font-size: 13px; padding: 8px 16px;">
              Explore Creche <i class="fa fa-arrow-right"></i>
            </a>
          </div>
        </div>

        <!-- Nursery -->
        <div class="folu-journey-card">
          <div class="folu-journey-image-wrap">
            <span class="folu-journey-tag">Early Learning</span>
            <img src="<?php echo e(asset('images/nurgrad.jpg')); ?>" alt="Folu Nursery School">
          </div>
          <div class="folu-journey-content">
            <h3 class="folu-journey-title">Nursery School</h3>
            <p class="folu-journey-desc">
              Building early phonics, reading, numeracy, social interaction, confidence, and creative self-expression in a stimulating setting.
            </p>
            <a href="<?php echo e(url('/creche#nursery')); ?>" class="folu-btn folu-btn-outline" style="font-size: 13px; padding: 8px 16px;">
              Explore Nursery <i class="fa fa-arrow-right"></i>
            </a>
          </div>
        </div>

        <!-- Primary -->
        <div class="folu-journey-card">
          <div class="folu-journey-image-wrap">
            <span class="folu-journey-tag">Core Years</span>
            <img src="<?php echo e(asset('images/prigrad.jpg')); ?>" alt="Folu Primary School">
          </div>
          <div class="folu-journey-content">
            <h3 class="folu-journey-title">Primary School</h3>
            <p class="folu-journey-desc">
              Developing disciplined study habits, academic confidence, inquiry, quantitative reasoning, and wholesome character.
            </p>
            <a href="<?php echo e(url('/primary')); ?>" class="folu-btn folu-btn-outline" style="font-size: 13px; padding: 8px 16px;">
              Explore Primary <i class="fa fa-arrow-right"></i>
            </a>
          </div>
        </div>

        <!-- Secondary -->
        <div class="folu-journey-card">
          <div class="folu-journey-image-wrap">
            <span class="folu-journey-tag">College</span>
            <img src="<?php echo e(asset('images/j3grad.jpg')); ?>" alt="Folu Secondary College">
          </div>
          <div class="folu-journey-content">
            <h3 class="folu-journey-title">Secondary College</h3>
            <p class="folu-journey-desc">
              Comprehensive junior and senior secondary education preparing students for external examinations, leadership, and tertiary advancement.
            </p>
            <a href="<?php echo e(url('/secondary')); ?>" class="folu-btn folu-btn-outline" style="font-size: 13px; padding: 8px 16px;">
              Explore Secondary <i class="fa fa-arrow-right"></i>
            </a>
          </div>
        </div>

      </div>

    </div>
  </section>

  <!-- 5. "LEARNING THAT COMES ALIVE" -->
  <section class="folu-section" id="learning-alive">
    <div class="folu-container">
      
      <div class="folu-section-header text-center">
        <span class="folu-badge-pill">
          <i class="fa fa-star"></i> Interactive Learning
        </span>
        <h2 class="folu-section-title">Learning That Comes Alive</h2>
        <p class="folu-section-subtitle">
          We believe learning is most effective when it sparks curiosity, demands participation, and develops the whole child.
        </p>
      </div>

      <div class="folu-alive-grid">
        
        <div class="folu-alive-card">
          <div class="folu-alive-num">01</div>
          <div>
            <h3 class="folu-alive-title">Learn</h3>
            <p class="folu-alive-desc">Attentive classroom instruction with experienced teachers fostering conceptual mastery and clear thinking.</p>
          </div>
        </div>

        <div class="folu-alive-card">
          <div class="folu-alive-num">02</div>
          <div>
            <h3 class="folu-alive-title">Explore</h3>
            <p class="folu-alive-desc">Hands-on practical experiments, scientific inquiry, and educational discovery that ignite natural curiosity.</p>
          </div>
        </div>

        <div class="folu-alive-card">
          <div class="folu-alive-num">03</div>
          <div>
            <h3 class="folu-alive-title">Create</h3>
            <p class="folu-alive-desc">Encouraging expressive artwork, creative writing, innovative projects, and creative problem-solving.</p>
          </div>
        </div>

        <div class="folu-alive-card">
          <div class="folu-alive-num">04</div>
          <div>
            <h3 class="folu-alive-title">Compete</h3>
            <p class="folu-alive-desc">Spelling bees, mathematics challenges, debate competitions, and healthy inter-house contests.</p>
          </div>
        </div>

        <div class="folu-alive-card">
          <div class="folu-alive-num">05</div>
          <div>
            <h3 class="folu-alive-title">Lead</h3>
            <p class="folu-alive-desc">Student leadership bodies, prefect responsibilities, class coordination, and public speaking opportunities.</p>
          </div>
        </div>

        <div class="folu-alive-card">
          <div class="folu-alive-num">06</div>
          <div>
            <h3 class="folu-alive-title">Grow</h3>
            <p class="folu-alive-desc">Instilling moral discipline, personal responsibility, spiritual grounding, and mutual respect among peers.</p>
          </div>
        </div>

      </div>

    </div>
  </section>

  <!-- 6. STUDENT LIFE & ACTIVITIES SHOWCASE -->
  <section class="folu-section folu-section-subtle" id="activities">
    <div class="folu-container">
      
      <div class="folu-section-header text-center">
        <span class="folu-badge-pill">
          <i class="fa fa-users"></i> Experience &amp; Community
        </span>
        <h2 class="folu-section-title">Discover. Participate. Excel.</h2>
        <p class="folu-section-subtitle">
          A rich co-curricular calendar where every student finds camaraderie, develops talents, and creates memorable school experiences.
        </p>
      </div>

      <div class="folu-activities-grid">
        
        <div class="folu-activity-card">
          <img src="<?php echo e(asset('images/studentsport.jpg')); ?>" alt="Student Sports and Athletics">
          <div class="folu-activity-overlay">
            <span class="folu-activity-badge">Athletics</span>
            <h3 class="folu-activity-title">Sports &amp; Fitness</h3>
            <p class="folu-activity-desc">Track and field, football, teamwork, physical health, and vibrant inter-house competition.</p>
          </div>
        </div>

        <div class="folu-activity-card">
          <img src="<?php echo e(asset('images/prigrad2.jpg')); ?>" alt="Graduation and Milestones">
          <div class="folu-activity-overlay">
            <span class="folu-activity-badge">Milestones</span>
            <h3 class="folu-activity-title">Speech &amp; Prize-Giving</h3>
            <p class="folu-activity-desc">Celebrating academic achievements, character awards, and graduation milestones.</p>
          </div>
        </div>

        <div class="folu-activity-card">
          <img src="<?php echo e(asset('images/excorsion.jpg')); ?>" alt="Educational Excursion">
          <div class="folu-activity-overlay">
            <span class="folu-activity-badge">Discovery</span>
            <h3 class="folu-activity-title">Educational Excursions</h3>
            <p class="folu-activity-desc">Broadening students' horizons through organized field visits and community learning.</p>
          </div>
        </div>

      </div>

      <div style="text-align: center; margin-top: 40px;">
        <a href="<?php echo e(url('/gallery')); ?>" class="folu-btn folu-btn-primary">
          <i class="fa fa-camera"></i>
          <span>View School Gallery</span>
        </a>
      </div>

    </div>
  </section>

  <!-- 7. ADMISSION ROADMAP SECTION -->
  <section class="folu-section" id="admission-steps">
    <div class="folu-container">
      
      <div class="folu-section-header text-center">
        <span class="folu-badge-pill">
          <i class="fa fa-id-card"></i> Admission Process
        </span>
        <h2 class="folu-section-title">Your Step-by-Step Admission Journey</h2>
        <p class="folu-section-subtitle">
          We make joining the Folu family clear and welcoming for both parents and prospective pupils.
        </p>
      </div>

      <div class="folu-roadmap-grid">
        
        <div class="folu-roadmap-card">
          <span class="folu-roadmap-step">Step 01</span>
          <h3 class="folu-roadmap-title">Enquire &amp; Connect</h3>
          <p class="folu-roadmap-desc">
            Reach out via our website, direct WhatsApp, or telephone to learn about class availability, fees, and requirements.
          </p>
        </div>

        <div class="folu-roadmap-card">
          <span class="folu-roadmap-step">Step 02</span>
          <h3 class="folu-roadmap-title">Submit Application</h3>
          <p class="folu-roadmap-desc">
            Complete the official application form online or collect an admission package directly from our school registry.
          </p>
        </div>

        <div class="folu-roadmap-card">
          <span class="folu-roadmap-step">Step 03</span>
          <h3 class="folu-roadmap-title">Placement Assessment</h3>
          <p class="folu-roadmap-desc">
            A friendly, age-appropriate assessment to understand your child's current learning strengths and ideal class placement.
          </p>
        </div>

        <div class="folu-roadmap-card">
          <span class="folu-roadmap-step">Step 04</span>
          <h3 class="folu-roadmap-title">Enrollment &amp; Welcome</h3>
          <p class="folu-roadmap-desc">
            Receive your formal letter of admission, complete registration, and be warmly welcomed into the Folu school community.
          </p>
        </div>

      </div>

      <div style="text-align: center; margin-top: 40px;">
        <a href="<?php echo e(url('/apply')); ?>" class="folu-btn folu-btn-gold" style="font-size: 16px; padding: 14px 28px;">
          <i class="fa fa-file-text-o"></i>
          <span>Start Online Application</span>
        </a>
      </div>

    </div>
  </section>

  <!-- 8. CTA BANNER CARD -->
  <section class="folu-section-sm" style="padding-bottom: 88px;">
    <div class="folu-container">
      <div class="folu-cta-card">
        <div class="folu-cta-content">
          <span class="folu-badge-pill folu-badge-pill-dark" style="margin-bottom: 12px;">
            <i class="fa fa-graduation-cap"></i> Join Our School Community
          </span>
          <h2 class="folu-cta-title">Give Your Child an Education Grounded in Knowledge &amp; Discipline</h2>
          <p class="folu-cta-text">
            Admissions are currently welcoming new pupils and students across Creche, Nursery, Primary, and Secondary College. Visit our campus in Itedo-Ijowa, Isanlu, or apply online today.
          </p>
        </div>
        <div class="folu-cta-buttons">
          <a href="<?php echo e(url('/apply')); ?>" class="folu-btn folu-btn-gold">
            <i class="fa fa-pencil"></i>
            <span>Apply Now</span>
          </a>
          <a href="https://wa.me/2348165354191?text=Hello%20Folu%20Schools%2C%20I%20would%20like%20to%20enquire%20about%20admission" target="_blank" rel="noopener noreferrer" class="folu-btn folu-btn-white">
            <i class="fa fa-whatsapp text-success"></i>
            <span>Chat on WhatsApp</span>
          </a>
        </div>
      </div>
    </div>
  </section>

  
  <?php echo $__env->make('frontend.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</body>
</html><?php /**PATH C:\laragon\www\folu\resources\views/frontend/pages/index.blade.php ENDPATH**/ ?>