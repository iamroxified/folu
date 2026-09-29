<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Admission Policy | FOLU INTERNATIONAL GROUP OF SCHOOLS | Isanlu, Kogi State</title>
  <meta name="description" content="Read the admission policy, age guidelines, discipline charter, and enrollment regulations of Folu International Group of Schools in Isanlu, Kogi State.">
  <meta name="keywords" content="Folu International Schools Admission Policy, School Rules Isanlu, Age Requirements Creche Primary Secondary Kogi State">

  <!-- OpenGraph / Social Metadata -->
  <meta property="og:title" content="Admission Policy | Folu International Group of Schools">
  <meta property="og:description" content="Maintaining high academic and moral standards across Creche, Nursery, Primary, and Secondary education.">
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
          <span class="current">Admission Policy</span>
        </nav>
        <h1 class="folu-page-title">Admission Policy &amp; Standards</h1>
        <p class="folu-page-subtitle">
          Our admission guidelines ensure a fair, transparent, and structured enrolment process that preserves our academic excellence and moral discipline in Itedo-Ijowa, Isanlu.
        </p>
      </div>
    </div>
  </header>

  <!-- 2. MAIN POLICY CONTENT -->
  <section class="folu-section">
    <div class="folu-container">
      <div class="folu-editorial-grid" style="grid-template-columns: 1.25fr 0.75fr; align-items: start;">
        
        <!-- Left: Policy Principles -->
        <div>
          
          <!-- Section 1 -->
          <div style="background: var(--folu-surface); border-radius: var(--folu-radius-md); border: 1px solid var(--folu-border); padding: 36px 32px; box-shadow: var(--folu-shadow-sm); margin-bottom: 28px;">
            <span class="folu-section-badge">Section 1</span>
            <h2 style="font-size: 22px; font-weight: 800; color: var(--folu-navy); margin-bottom: 12px;">Principles of Enrolment &amp; Non-Discrimination</h2>
            <p style="font-size: 15px; color: var(--folu-text-body); line-height: 1.7; margin-bottom: 14px;">
              <strong>Folu International Group of Schools</strong> welcomes applications for children from all backgrounds, communities, and religious affiliations who are willing to thrive under our institutional ethos of <strong>Knowledge &amp; Discipline</strong>.
            </p>
            <p style="font-size: 14.5px; color: var(--folu-text-muted); line-height: 1.65; margin: 0;">
              Admission is offered based on academic suitability, classroom capacity, and readiness to subscribe to the school's moral and disciplinary guidelines. We maintain healthy teacher-to-pupil ratios to safeguard individualized attention.
            </p>
          </div>

          <!-- Section 2 -->
          <div style="background: var(--folu-surface); border-radius: var(--folu-radius-md); border: 1px solid var(--folu-border); padding: 36px 32px; box-shadow: var(--folu-shadow-sm); margin-bottom: 28px;">
            <span class="folu-section-badge">Section 2</span>
            <h2 style="font-size: 22px; font-weight: 800; color: var(--folu-navy); margin-bottom: 12px;">Age Guidelines &amp; Placement Criteria</h2>
            <p style="font-size: 14.5px; color: var(--folu-text-muted); line-height: 1.65; margin-bottom: 20px;">
              To ensure learners are emotionally, socially, and academically equipped for their peer groups, the school adheres to general age benchmarks:
            </p>

            <div style="display: flex; flex-direction: column; gap: 14px;">
              <div style="padding: 16px 18px; background: var(--folu-surface-subtle); border-radius: var(--folu-radius-sm); border-left: 3px solid var(--folu-navy);">
                <strong style="color: var(--folu-navy);">Creche &amp; Playgroup:</strong>
                <span style="font-size: 14px; color: var(--folu-text-body);"> 3 months to 2 years of age. Focus is on loving care, early sensory exploration, and motor skills.</span>
              </div>
              <div style="padding: 16px 18px; background: var(--folu-surface-subtle); border-radius: var(--folu-radius-sm); border-left: 3px solid var(--folu-gold);">
                <strong style="color: var(--folu-navy);">Nursery 1, 2 &amp; 3:</strong>
                <span style="font-size: 14px; color: var(--folu-text-body);"> Ages 3 to 5 years. Child must demonstrate basic verbal communication and readiness for group activities.</span>
              </div>
              <div style="padding: 16px 18px; background: var(--folu-surface-subtle); border-radius: var(--folu-radius-sm); border-left: 3px solid var(--folu-green);">
                <strong style="color: var(--folu-navy);">Primary School (Basic 1 - 6):</strong>
                <span style="font-size: 14px; color: var(--folu-text-body);"> Ages 6 to 11 years. Placement into Basic 1 requires successful completion of Nursery or proven reading/number readiness.</span>
              </div>
              <div style="padding: 16px 18px; background: var(--folu-surface-subtle); border-radius: var(--folu-radius-sm); border-left: 3px solid #7c3aed;">
                <strong style="color: var(--folu-navy);">Secondary School (JSS 1 - SSS 3):</strong>
                <span style="font-size: 14px; color: var(--folu-text-body);"> Ages 10 years and above for JSS 1. Requires primary school completion and performance in the entrance examination.</span>
              </div>
            </div>
          </div>

          <!-- Section 3 -->
          <div style="background: var(--folu-surface); border-radius: var(--folu-radius-md); border: 1px solid var(--folu-border); padding: 36px 32px; box-shadow: var(--folu-shadow-sm); margin-bottom: 28px;">
            <span class="folu-section-badge">Section 3</span>
            <h2 style="font-size: 22px; font-weight: 800; color: var(--folu-navy); margin-bottom: 12px;">Discipline, Conduct &amp; Character Charter</h2>
            <p style="font-size: 15px; color: var(--folu-text-body); line-height: 1.7; margin-bottom: 16px;">
              Our institutional identity is anchored on <strong>Knowledge &amp; Discipline</strong>. We expect every student and parent to partner with the school in maintaining:
            </p>
            <ul class="folu-checklist">
              <li><i class="fa fa-check-circle"></i> <strong>Respect &amp; Civility:</strong> Courteous behavior toward teachers, school staff, visitors, and fellow students at all times.</li>
              <li><i class="fa fa-check-circle"></i> <strong>Punctuality &amp; Attendance:</strong> Regular attendance and timely arrival before morning assembly (7:30 AM).</li>
              <li><i class="fa fa-check-circle"></i> <strong>Official Dress Code:</strong> Clean, complete, and properly worn school uniforms on specified days, with neat hair grooming.</li>
              <li><i class="fa fa-check-circle"></i> <strong>Zero Tolerance for Bullying:</strong> Folu maintains a strict anti-bullying and anti-violence environment where all children feel protected and valued.</li>
            </ul>
          </div>

          <!-- Section 4 -->
          <div style="background: var(--folu-surface); border-radius: var(--folu-radius-md); border: 1px solid var(--folu-border); padding: 36px 32px; box-shadow: var(--folu-shadow-sm);">
            <span class="folu-section-badge">Section 4</span>
            <h2 style="font-size: 22px; font-weight: 800; color: var(--folu-navy); margin-bottom: 12px;">Parent Partnership &amp; Communication</h2>
            <p style="font-size: 15px; color: var(--folu-text-body); line-height: 1.7; margin-bottom: 14px;">
              Education is most fruitful when home and school collaborate intimately. Parents are encouraged to attend open days, review homework and communication books, and engage with teachers concerning their child's academic trajectory.
            </p>
            <p style="font-size: 14.5px; color: var(--folu-text-muted); line-height: 1.65; margin: 0;">
              All official concerns or enquiries should be routed through the school administration office or the designated school phone lines.
            </p>
          </div>

        </div>

        <!-- Right: Fast Actions & Summary -->
        <div>
          <div style="background: var(--folu-navy); color: #ffffff; border-radius: var(--folu-radius-lg); padding: 32px 28px; box-shadow: var(--folu-shadow-md); margin-bottom: 24px;">
            <span class="folu-section-badge" style="background: rgba(217, 119, 6, 0.2); color: var(--folu-gold-accent); margin-bottom: 8px;">Admissions 2026/2027</span>
            <h3 style="font-size: 20px; font-weight: 700; color: #ffffff; margin-bottom: 12px;">Enrol Your Child Today</h3>
            <p style="font-size: 14px; color: #cbd5e1; line-height: 1.6; margin-bottom: 22px;">
              Ready to give your child an education that pairs academic distinction with enduring moral discipline?
            </p>
            <a href="{{ url('/apply') }}" class="folu-btn folu-btn-primary" style="width: 100%; justify-content: center; padding: 14px; margin-bottom: 12px;">
              Apply for Admission
            </a>
            <a href="{{ url('/admission-process') }}" class="folu-btn folu-btn-outline" style="width: 100%; justify-content: center; border-color: rgba(255, 255, 255, 0.3); color: #ffffff;">
              View Admission Steps
            </a>
          </div>

          <!-- Contact Details Card -->
          <div style="background: var(--folu-surface); border-radius: var(--folu-radius-md); border: 1px solid var(--folu-border); padding: 26px 22px; box-shadow: var(--folu-shadow-sm);">
            <h4 style="font-size: 16px; font-weight: 700; color: var(--folu-navy); margin-bottom: 12px;">Admissions Office Contacts</h4>
            <div style="font-size: 14px; color: var(--folu-text-body); line-height: 1.6; display: flex; flex-direction: column; gap: 8px;">
              <div><strong>Address:</strong> P.O. Box 37, Itedo-Ijowa, Isanlu, Kogi State, Nigeria</div>
              <div><strong>Phone:</strong> <a href="tel:08165354191" style="color: var(--folu-navy); font-weight: 600;">08165354191</a>, <a href="tel:08057421037" style="color: var(--folu-navy); font-weight: 600;">08057421037</a></div>
              <div><strong>Email:</strong> <a href="mailto:info@foluinternationalschools.sch.ng" style="color: var(--folu-blue); font-size: 13px;">info@foluinternationalschools.sch.ng</a></div>
              <div><strong>Office Hours:</strong> Mon &ndash; Fri: 7:30 AM &ndash; 4:00 PM</div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  {{-- Verified Footer --}}
  @include('frontend.partials.footer')

</body>
</html>
