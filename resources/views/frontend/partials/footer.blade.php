{{-- Modern Folu Footer Partial --}}
<footer class="folu-footer">
  <div class="folu-container">
    <div class="folu-footer-grid">
      
      <!-- Column 1: Brand & Identity -->
      <div class="folu-footer-col folu-footer-brand-col">
        <div class="folu-footer-brand">
          <img src="{{ asset('images/folu-logo.png') }}" alt="Folu International Schools Logo" class="folu-footer-logo">
          <h4 class="folu-footer-brand-title">
            Folu International Group of Schools
          </h4>
          <span class="folu-footer-motto-tag">
            Knowledge &amp; Discipline
          </span>
          <p class="folu-footer-brand-desc">
            A premier educational institution in Isanlu, Kogi State, dedicated to academic excellence, moral discipline, character building, and nurturing tomorrow's leaders.
          </p>
          <div class="folu-footer-proprietors">
            <p><strong>Proprietor:</strong> Rev. Dr. Samuel Babaniyi</p>
            <p><strong>Proprietress:</strong> Rev. Mrs. Mofoluwake Babaniyi</p>
          </div>
        </div>
      </div>

      <!-- Column 2: Our Educational Journey -->
      <div class="folu-footer-col">
        <h4>Our Schools</h4>
        <ul class="folu-footer-links">
          <li><a href="{{ url('/creche') }}"><i class="fa fa-angle-right"></i> Creche &amp; Daycare</a></li>
          <li><a href="{{ url('/creche#nursery') }}"><i class="fa fa-angle-right"></i> Nursery School</a></li>
          <li><a href="{{ url('/primary') }}"><i class="fa fa-angle-right"></i> Primary School</a></li>
          <li><a href="{{ url('/secondary') }}"><i class="fa fa-angle-right"></i> Secondary College</a></li>
          <li><a href="{{ url('/overview-academics') }}"><i class="fa fa-angle-right"></i> Academic Curriculum</a></li>
          <li><a href="{{ url('/gallery') }}"><i class="fa fa-angle-right"></i> Student Life &amp; Clubs</a></li>
        </ul>
      </div>

      <!-- Column 3: Admissions & Portal -->
      <div class="folu-footer-col">
        <h4>Admissions &amp; Portals</h4>
        <ul class="folu-footer-links">
          <li><a href="{{ url('/apply') }}"><i class="fa fa-angle-right"></i> Apply for Admission</a></li>
          <li><a href="{{ url('/admission-process') }}"><i class="fa fa-angle-right"></i> Admission Process</a></li>
          <li><a href="{{ url('/admission-policy') }}"><i class="fa fa-angle-right"></i> Admissions Policy</a></li>
          <li><a href="{{ url('/about-us') }}"><i class="fa fa-angle-right"></i> About Our School</a></li>
          <li><a href="{{ url('/admin/login') }}"><i class="fa fa-lock"></i> Staff &amp; Admin Portal</a></li>
          <li><a href="{{ url('/student') }}"><i class="fa fa-user"></i> Student Portal</a></li>
        </ul>
      </div>

      <!-- Column 4: Contact & Location -->
      <div class="folu-footer-col">
        <h4>Contact &amp; Location</h4>
        <ul class="folu-footer-contact-list">
          <li class="folu-footer-contact-item">
            <i class="fa fa-map-marker"></i>
            <span>P.O. Box 37, Itedo-Ijowa, Isanlu, Kogi State, Nigeria</span>
          </li>
          <li class="folu-footer-contact-item">
            <i class="fa fa-phone"></i>
            <div>
              <a href="tel:08165354191">08165354191</a><br>
              <a href="tel:08057421037">08057421037</a>
            </div>
          </li>
          <li class="folu-footer-contact-item">
            <i class="fa fa-envelope"></i>
            <a href="mailto:info@foluinternationalschools.sch.ng">info@foluinternationalschools.sch.ng</a>
          </li>
          <li class="folu-footer-contact-item">
            <i class="fa fa-whatsapp"></i>
            <a href="https://wa.me/2348165354191" target="_blank" rel="noopener noreferrer">WhatsApp Chat Line</a>
          </li>
        </ul>
      </div>

    </div>
  </div>

  <!-- Bottom Bar -->
  <div class="folu-footer-bottom">
    <div class="folu-container">
      <div class="folu-footer-bottom-inner">
        <div class="folu-footer-copy">
          &copy; {{ date('Y') }} Folu International Group of Schools. All Rights Reserved.
        </div>
        <div class="folu-footer-motto">
          Motto: <span class="folu-footer-gold">KNOWLEDGE &amp; DISCIPLINE</span> &bull; Isanlu, Kogi State
        </div>
      </div>
    </div>
  </div>
</footer>
