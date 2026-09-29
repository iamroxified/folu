{{-- Modern Folu Header & Navigation --}}
<div class="folu-topbar">
  <div class="kingster-container clearfix" style="max-width: 1240px; margin: 0 auto; padding: 0 20px;">
    <div class="folu-topbar-inner">
      <div class="folu-topbar-info">
        <span class="folu-topbar-item">
          <i class="fa fa-map-marker"></i>
          <span>P.O. Box 37, Itedo-Ijowa, Isanlu, Kogi State</span>
        </span>
        <span class="folu-topbar-item">
          <i class="fa fa-phone"></i>
          <a href="tel:08165354191">08165354191</a> / <a href="tel:08057421037">08057421037</a>
        </span>
        <span class="folu-topbar-item d-none d-md-inline-flex">
          <i class="fa fa-envelope-o"></i>
          <a href="mailto:info@foluinternationalschools.sch.ng">info@foluinternationalschools.sch.ng</a>
        </span>
      </div>
      <div class="folu-topbar-actions">
        <a href="{{ url('/admin/login') }}" class="folu-portal-link">
          <i class="fa fa-lock"></i>
          <span>Portal Login</span>
        </a>
      </div>
    </div>
  </div>
</div>

<header class="folu-navbar-wrap">
  <div class="kingster-container clearfix" style="max-width: 1240px; margin: 0 auto; padding: 0 20px;">
    <nav class="folu-navbar">
      <!-- School Brand -->
      <a href="{{ url('/') }}" class="folu-brand">
        <img src="{{ asset('images/folu-logo.png') }}" alt="Folu International Schools Logo" class="folu-brand-logo">
        <div class="folu-brand-text">
          <span class="folu-brand-name">Folu International Schools</span>
          <span class="folu-brand-motto">Knowledge &amp; Discipline</span>
        </div>
      </a>

      <!-- Desktop Navigation Menu -->
      <ul class="folu-nav-menu">
        <li class="folu-nav-item {{ request()->is('/') ? 'active' : '' }}">
          <a href="{{ url('/') }}" class="folu-nav-link">Home</a>
        </li>
        <li class="folu-nav-item {{ request()->is('about*') ? 'active' : '' }}">
          <a href="{{ url('/about-us') }}" class="folu-nav-link">
            About Us <i class="fa fa-angle-down"></i>
          </a>
          <ul class="folu-dropdown">
            <li class="folu-dropdown-item"><a href="{{ url('/about-us#story') }}" class="folu-dropdown-link">Our Story &amp; Founders</a></li>
            <li class="folu-dropdown-item"><a href="{{ url('/about-us#vision') }}" class="folu-dropdown-link">Vision &amp; Mission</a></li>
            <li class="folu-dropdown-item"><a href="{{ url('/about-us#values') }}" class="folu-dropdown-link">Core Values</a></li>
            <li class="folu-dropdown-item"><a href="{{ url('/our-staffs') }}" class="folu-dropdown-link">Leadership &amp; Faculty</a></li>
          </ul>
        </li>
        <li class="folu-nav-item {{ request()->is('academics*', 'creche*', 'primary*', 'secondary*') ? 'active' : '' }}">
          <a href="{{ url('/overview-academics') }}" class="folu-nav-link">
            Academics <i class="fa fa-angle-down"></i>
          </a>
          <ul class="folu-dropdown">
            <li class="folu-dropdown-item"><a href="{{ url('/overview-academics') }}" class="folu-dropdown-link">Overview &amp; Curriculum</a></li>
            <li class="folu-dropdown-item"><a href="{{ url('/creche') }}" class="folu-dropdown-link">Creche &amp; Early Years</a></li>
            <li class="folu-dropdown-item"><a href="{{ url('/creche#nursery') }}" class="folu-dropdown-link">Nursery School</a></li>
            <li class="folu-dropdown-item"><a href="{{ url('/primary') }}" class="folu-dropdown-link">Primary School</a></li>
            <li class="folu-dropdown-item"><a href="{{ url('/secondary') }}" class="folu-dropdown-link">Secondary College</a></li>
          </ul>
        </li>
        <li class="folu-nav-item {{ request()->is('student-life*', 'gallery*') ? 'active' : '' }}">
          <a href="{{ url('/gallery') }}" class="folu-nav-link">
            Student Life <i class="fa fa-angle-down"></i>
          </a>
          <ul class="folu-dropdown">
            <li class="folu-dropdown-item"><a href="{{ url('/gallery') }}" class="folu-dropdown-link">School Gallery</a></li>
            <li class="folu-dropdown-item"><a href="{{ url('/#activities') }}" class="folu-dropdown-link">Clubs &amp; Competitions</a></li>
            <li class="folu-dropdown-item"><a href="{{ url('/#journey') }}" class="folu-dropdown-link">Sports &amp; Culture</a></li>
          </ul>
        </li>
        <li class="folu-nav-item {{ request()->is('admission*', 'apply*') ? 'active' : '' }}">
          <a href="{{ url('/apply') }}" class="folu-nav-link">
            Admissions <i class="fa fa-angle-down"></i>
          </a>
          <ul class="folu-dropdown">
            <li class="folu-dropdown-item"><a href="{{ url('/apply') }}" class="folu-dropdown-link">Apply for Admission</a></li>
            <li class="folu-dropdown-item"><a href="{{ url('/admission-process') }}" class="folu-dropdown-link">Admission Process</a></li>
            <li class="folu-dropdown-item"><a href="{{ url('/admission-policy') }}" class="folu-dropdown-link">Admission Policy</a></li>
          </ul>
        </li>
        <li class="folu-nav-item {{ request()->is('blog*') ? 'active' : '' }}">
          <a href="{{ url('/blog') }}" class="folu-nav-link">Stories &amp; News</a>
        </li>
        <li class="folu-nav-item {{ request()->is('contact*') ? 'active' : '' }}">
          <a href="{{ url('/contact') }}" class="folu-nav-link">Contact</a>
        </li>
      </ul>

      <!-- CTA Buttons & Mobile Toggle -->
      <div class="folu-nav-cta">
        <a href="{{ url('/apply') }}" class="folu-btn folu-btn-primary">
          <i class="fa fa-graduation-cap"></i>
          <span>Apply Now</span>
        </a>
        <button type="button" class="folu-mobile-toggle" id="foluMobileMenuBtn" aria-label="Toggle navigation">
          <i class="fa fa-bars"></i>
        </button>
      </div>
    </nav>
  </div>
</header>

<!-- Mobile Navigation Drawer -->
<div class="folu-drawer-backdrop" id="foluDrawerBackdrop"></div>
<div class="folu-mobile-drawer" id="foluMobileDrawer">
  <div class="folu-drawer-header">
    <div class="folu-brand">
      <img src="{{ asset('images/folu-logo.png') }}" alt="Folu Logo" style="height: 44px;">
      <div class="folu-brand-text">
        <span class="folu-brand-name" style="font-size: 15px;">Folu Schools</span>
        <span class="folu-brand-motto" style="font-size: 10px;">Isanlu, Kogi State</span>
      </div>
    </div>
    <button type="button" class="folu-drawer-close" id="foluDrawerCloseBtn">&times;</button>
  </div>

  <ul class="folu-mobile-nav">
    <li><a href="{{ url('/') }}" class="folu-mobile-nav-link">Home</a></li>
    <li>
      <a href="javascript:void(0)" class="folu-mobile-nav-link has-submenu">
        About Us <i class="fa fa-angle-down"></i>
      </a>
      <ul class="folu-mobile-submenu">
        <li><a href="{{ url('/about-us') }}">Overview &amp; Story</a></li>
        <li><a href="{{ url('/about-us#vision') }}">Vision &amp; Mission</a></li>
        <li><a href="{{ url('/our-staffs') }}">Faculty &amp; Staff</a></li>
      </ul>
    </li>
    <li>
      <a href="javascript:void(0)" class="folu-mobile-nav-link has-submenu">
        Academics <i class="fa fa-angle-down"></i>
      </a>
      <ul class="folu-mobile-submenu">
        <li><a href="{{ url('/overview-academics') }}">Overview</a></li>
        <li><a href="{{ url('/creche') }}">Creche &amp; Nursery</a></li>
        <li><a href="{{ url('/primary') }}">Primary School</a></li>
        <li><a href="{{ url('/secondary') }}">Secondary College</a></li>
      </ul>
    </li>
    <li>
      <a href="javascript:void(0)" class="folu-mobile-nav-link has-submenu">
        Admissions <i class="fa fa-angle-down"></i>
      </a>
      <ul class="folu-mobile-submenu">
        <li><a href="{{ url('/apply') }}">Apply Now</a></li>
        <li><a href="{{ url('/admission-process') }}">Admission Process</a></li>
        <li><a href="{{ url('/admission-policy') }}">Admission Policy</a></li>
      </ul>
    </li>
    <li><a href="{{ url('/gallery') }}" class="folu-mobile-nav-link">Gallery &amp; Life</a></li>
    <li><a href="{{ url('/blog') }}" class="folu-mobile-nav-link">News &amp; Events</a></li>
    <li><a href="{{ url('/contact') }}" class="folu-mobile-nav-link">Contact</a></li>
  </ul>

  <div style="margin-top: 24px; display: flex; flex-direction: column; gap: 12px;">
    <a href="{{ url('/apply') }}" class="folu-btn folu-btn-primary" style="width: 100%; text-align: center;">
      Apply for Admission
    </a>
    <a href="{{ url('/admin/login') }}" class="folu-btn folu-btn-outline" style="width: 100%; text-align: center;">
      <i class="fa fa-lock"></i> Portal Login
    </a>
  </div>

  <div style="margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--folu-border); font-size: 13px; color: var(--folu-text-muted);">
    <div style="margin-bottom: 8px;"><i class="fa fa-phone text-warning me-2"></i> 08165354191</div>
    <div style="margin-bottom: 8px;"><i class="fa fa-whatsapp text-success me-2"></i> 08057421037</div>
    <div><i class="fa fa-map-marker text-danger me-2"></i> Itedo-Ijowa, Isanlu, Kogi State</div>
  </div>
</div>

<!-- Floating Quick Actions (WhatsApp & Call) -->
<div class="folu-floating-contact" id="foluFloatingContact">
  <a href="https://wa.me/2348165354191?text=Hello%2C%20I%20am%20inquiring%20about%20admission%20at%20Folu%20International%20Schools" 
     target="_blank" rel="noopener noreferrer" class="folu-float-btn folu-float-whatsapp" title="Chat on WhatsApp">
    <i class="fa fa-whatsapp"></i>
    <span class="folu-float-tooltip">Chat with Admissions</span>
  </a>
  <a href="tel:08165354191" class="folu-float-btn folu-float-phone" title="Call Us Directly">
    <i class="fa fa-phone"></i>
    <span class="folu-float-tooltip">Call School Office</span>
  </a>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const mobileBtn = document.getElementById('foluMobileMenuBtn');
    const drawer = document.getElementById('foluMobileDrawer');
    const backdrop = document.getElementById('foluDrawerBackdrop');
    const closeBtn = document.getElementById('foluDrawerCloseBtn');

    function openDrawer() {
      drawer.classList.add('open');
      backdrop.classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
      drawer.classList.remove('open');
      backdrop.classList.remove('active');
      document.body.style.overflow = '';
    }

    if (mobileBtn) mobileBtn.addEventListener('click', openDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    if (backdrop) backdrop.addEventListener('click', closeDrawer);

    // Mobile submenu toggles
    document.querySelectorAll('.folu-mobile-nav-link.has-submenu').forEach(function(link) {
      link.addEventListener('click', function() {
        const submenu = this.nextElementSibling;
        if (submenu) {
          submenu.classList.toggle('open');
          const icon = this.querySelector('i');
          if (icon) {
            icon.classList.toggle('fa-angle-up');
            icon.classList.toggle('fa-angle-down');
          }
        }
      });
    });
  });
</script>