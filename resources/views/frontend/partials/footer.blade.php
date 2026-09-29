{{-- Modern Folu Footer Partial --}}
<footer class="folu-footer">
  <div class="kingster-container clearfix" style="max-width: 1240px; margin: 0 auto; padding: 0 20px;">
    <div class="row g-4" style="display: flex; flex-wrap: wrap; margin: 0 -15px;">
      
      <!-- Column 1: Brand & Identity -->
      <div class="col-lg-4 col-md-6" style="padding: 0 15px; flex: 0 0 33.333%; max-width: 33.333%; box-sizing: border-box; margin-bottom: 30px;">
        <div class="folu-footer-brand">
          <img src="{{ asset('images/folu-logo.png') }}" alt="Folu International Schools Logo" class="folu-footer-logo" style="height: 64px; margin-bottom: 15px;">
          <h4 style="color: #fff; font-size: 19px; font-weight: 800; margin-bottom: 6px; text-transform: uppercase; letter-spacing: -0.01em;">
            Folu International Group of Schools
          </h4>
          <span class="folu-footer-motto-tag">
            Knowledge &amp; Discipline
          </span>
          <p style="color: #94a3b8; font-size: 14px; line-height: 1.7; margin-top: 12px;">
            A premier educational institution in Isanlu, Kogi State, dedicated to academic excellence, moral discipline, character building, and nurturing tomorrow's leaders.
          </p>
          <div style="margin-top: 16px; font-size: 13px; color: #cbd5e1;">
            <p style="margin: 0 0 4px 0;"><strong>Proprietor:</strong> Rev. Dr. Samuel Babaniyi</p>
            <p style="margin: 0;"><strong>Proprietress:</strong> Rev. Mrs. Mofoluwake Babaniyi</p>
          </div>
        </div>
      </div>

      <!-- Column 2: Our Educational Journey -->
      <div class="col-lg-2 col-md-6" style="padding: 0 15px; flex: 0 0 20%; max-width: 20%; box-sizing: border-box; margin-bottom: 30px;">
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
      <div class="col-lg-3 col-md-6" style="padding: 0 15px; flex: 0 0 22%; max-width: 22%; box-sizing: border-box; margin-bottom: 30px;">
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
      <div class="col-lg-3 col-md-6" style="padding: 0 15px; flex: 0 0 24.666%; max-width: 24.666%; box-sizing: border-box; margin-bottom: 30px;">
        <h4>Contact &amp; Location</h4>
        <ul class="folu-footer-contact-list">
          <li class="folu-footer-contact-item">
            <i class="fa fa-map-marker" style="color: #f59e0b; margin-top: 4px;"></i>
            <span>P.O. Box 37, Itedo-Ijowa, Isanlu, Kogi State, Nigeria</span>
          </li>
          <li class="folu-footer-contact-item">
            <i class="fa fa-phone" style="color: #f59e0b; margin-top: 4px;"></i>
            <div>
              <a href="tel:08165354191">08165354191</a><br>
              <a href="tel:08057421037">08057421037</a>
            </div>
          </li>
          <li class="folu-footer-contact-item">
            <i class="fa fa-envelope" style="color: #f59e0b; margin-top: 4px;"></i>
            <a href="mailto:info@foluinternationalschools.sch.ng">info@foluinternationalschools.sch.ng</a>
          </li>
          <li class="folu-footer-contact-item">
            <i class="fa fa-whatsapp" style="color: #25d366; margin-top: 4px;"></i>
            <a href="https://wa.me/2348165354191" target="_blank" rel="noopener">WhatsApp Chat Line</a>
          </li>
        </ul>
      </div>

    </div>
  </div>

  <!-- Bottom Bar -->
  <div class="folu-footer-bottom">
    <div class="kingster-container clearfix" style="max-width: 1240px; margin: 0 auto; padding: 0 20px;">
      <div class="folu-footer-bottom-inner" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div style="color: #94a3b8;">
          &copy; {{ date('Y') }} Folu International Group of Schools. All Rights Reserved.
        </div>
        <div style="color: #cbd5e1; font-weight: 600; font-size: 13px;">
          Motto: <span style="color: #f59e0b;">KNOWLEDGE &amp; DISCIPLINE</span> &bull; Isanlu, Kogi State
        </div>
      </div>
    </div>
  </div>
</footer>
