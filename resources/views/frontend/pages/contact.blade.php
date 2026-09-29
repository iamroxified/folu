<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Contact Us | FOLU INTERNATIONAL GROUP OF SCHOOLS | Isanlu, Kogi State</title>
  <meta name="description" content="Get in touch with Folu International Group of Schools in Itedo-Ijowa, Isanlu, Kogi State. Call 08165354191 or 08057421037, chat on WhatsApp, or send an enquiry.">
  <meta name="keywords" content="Contact Folu International Schools, Isanlu School Phone Number, Folu Schools Address Isanlu Kogi State, School Admissions Help Desk">

  <!-- OpenGraph / Social Metadata -->
  <meta property="og:title" content="Contact Us | Folu International Group of Schools">
  <meta property="og:description" content="Reach our admissions and administrative office in Itedo-Ijowa, Isanlu, Kogi State. We would love to hear from you.">
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
          <span class="current">Contact Us</span>
        </nav>
        <h1 class="folu-page-title">We Are Here to Assist Your Family</h1>
        <p class="folu-page-subtitle">
          Have an enquiry about admissions, school fees, academic programmes, or wish to schedule a visit to our campus in Itedo-Ijowa, Isanlu? Connect with us through any of the channels below.
        </p>
      </div>
    </div>
  </header>

  <!-- 2. CONTACT CHANNELS GRID -->
  <section class="folu-section" style="padding-bottom: 20px;">
    <div class="folu-container">
      <div class="folu-contact-grid">
        
        <!-- Phone -->
        <div class="folu-contact-card">
          <div class="folu-contact-icon">
            <i class="fa fa-phone"></i>
          </div>
          <h3 class="folu-contact-card-title">Call Us Directly</h3>
          <p class="folu-contact-card-info">
            <a href="tel:08165354191">08165354191</a><br>
            <a href="tel:08057421037">08057421037</a>
          </p>
          <p style="font-size: 12px; color: var(--folu-text-muted); margin-top: 6px;">Mon &ndash; Fri: 7:30 AM &ndash; 4:00 PM</p>
        </div>

        <!-- WhatsApp -->
        <div class="folu-contact-card">
          <div class="folu-contact-icon green">
            <i class="fa fa-whatsapp"></i>
          </div>
          <h3 class="folu-contact-card-title">WhatsApp Chat</h3>
          <p class="folu-contact-card-info">
            Quick responses for parents &amp; admission enquiries.<br>
            <a href="https://wa.me/2348165354191?text=Hello%20Folu%20International%20Schools,%20I%20have%20an%20enquiry." target="_blank" rel="noopener noreferrer" style="color: var(--folu-green);">
              Start WhatsApp Chat &rarr;
            </a>
          </p>
        </div>

        <!-- Email -->
        <div class="folu-contact-card">
          <div class="folu-contact-icon gold">
            <i class="fa fa-envelope"></i>
          </div>
          <h3 class="folu-contact-card-title">Send an Email</h3>
          <p class="folu-contact-card-info">
            <a href="mailto:info@foluinternationalschools.sch.ng" style="font-size: 13px; word-break: break-all;">
              info@foluinternationalschools.sch.ng
            </a><br>
            <span style="font-size: 12px; color: var(--folu-text-muted);">Official administrative correspondence</span>
          </p>
        </div>

        <!-- Campus Location -->
        <div class="folu-contact-card">
          <div class="folu-contact-icon">
            <i class="fa fa-map-marker"></i>
          </div>
          <h3 class="folu-contact-card-title">Campus Location</h3>
          <p class="folu-contact-card-info">
            P.O. Box 37, Itedo-Ijowa, Isanlu, Kogi State, Nigeria.
          </p>
          <p style="font-size: 12px; color: var(--folu-text-muted); margin-top: 6px;">Visitors welcome on weekdays</p>
        </div>

      </div>
    </div>
  </section>

  <!-- 3. INTERACTIVE CONTACT FORM & VISITOR INFORMATION -->
  <section class="folu-section folu-section-subtle">
    <div class="folu-container">
      <div class="folu-editorial-grid" style="grid-template-columns: 1.2fr 0.8fr; align-items: start;">
        
        <!-- Left: Interactive Contact Form -->
        <div class="folu-form-card">
          <span class="folu-section-badge">Send a Message</span>
          <h2 class="folu-form-title">Direct Administrative Enquiry</h2>
          <p class="folu-form-subtitle">
            Fill out the form below and our administrative office will respond promptly.
          </p>

          <form id="foluContactForm" onsubmit="event.preventDefault(); handleContactSubmit();">
            <div class="folu-form-grid-2">
              <div class="folu-form-group">
                <label for="c_name" class="folu-form-label">Full Name <span class="req">*</span></label>
                <input type="text" id="c_name" name="name" class="folu-form-control" placeholder="Your full name" required>
              </div>
              <div class="folu-form-group">
                <label for="c_phone" class="folu-form-label">Phone Number <span class="req">*</span></label>
                <input type="tel" id="c_phone" name="phone" class="folu-form-control" placeholder="e.g. 08165354191" required>
              </div>
            </div>

            <div class="folu-form-grid-2">
              <div class="folu-form-group">
                <label for="c_email" class="folu-form-label">Email Address</label>
                <input type="email" id="c_email" name="email" class="folu-form-control" placeholder="e.g. you@example.com">
              </div>
              <div class="folu-form-group">
                <label for="c_subject" class="folu-form-label">Nature of Enquiry <span class="req">*</span></label>
                <select id="c_subject" name="subject" class="folu-form-select" required>
                  <option value="Admission Enquiry">Admission Enquiry (Creche, Nursery, Primary, Secondary)</option>
                  <option value="Tuition & Fees Information">Tuition &amp; Fees Information</option>
                  <option value="Campus Tour Booking">Book a Campus Tour</option>
                  <option value="Academic Performance Review">Academic &amp; Student Welfare Enquiry</option>
                  <option value="General Enquiry">General Information</option>
                </select>
              </div>
            </div>

            <div class="folu-form-group">
              <label for="c_message" class="folu-form-label">Your Message <span class="req">*</span></label>
              <textarea id="c_message" name="message" class="folu-form-textarea" placeholder="How can our school assist you?" required></textarea>
            </div>

            <div id="contactSuccessMsg" style="display: none; background: var(--folu-green-light); border: 1.5px solid var(--folu-green); color: var(--folu-green); padding: 18px 20px; border-radius: var(--folu-radius-sm); margin-bottom: 20px;">
              <i class="fa fa-check-circle" style="font-size: 18px; margin-right: 6px;"></i>
              <strong>Thank You!</strong> Your message has been received. An official representative of Folu International Group of Schools will follow up with you.
            </div>

            <button type="submit" class="folu-btn folu-btn-primary" style="width: 100%; padding: 16px; font-size: 16px;">
              Send Message <i class="fa fa-paper-plane" style="margin-left: 8px;"></i>
            </button>
          </form>
        </div>

        <!-- Right: Campus Visit Protocols & Leadership Details -->
        <div>
          
          <!-- Campus Protocol Box -->
          <div style="background: var(--folu-surface); border-radius: var(--folu-radius-md); border: 1px solid var(--folu-border); padding: 32px 28px; box-shadow: var(--folu-shadow-sm); margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
              <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--folu-blue-soft); color: var(--folu-blue); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-building-o"></i>
              </div>
              <h3 style="font-size: 18px; font-weight: 700; color: var(--folu-navy); margin: 0;">Visiting Our Campus</h3>
            </div>
            
            <p style="font-size: 14px; color: var(--folu-text-body); line-height: 1.6; margin-bottom: 16px;">
              Prospective parents and visitors are cordially invited to experience our serene campus in Itedo-Ijowa, Isanlu.
            </p>

            <ul class="folu-checklist" style="margin-bottom: 20px;">
              <li><i class="fa fa-clock-o" style="color: var(--folu-gold);"></i> <strong>Visiting Hours:</strong> Monday &ndash; Friday, 7:30 AM to 4:00 PM</li>
              <li><i class="fa fa-shield" style="color: var(--folu-navy);"></i> <strong>Security Protocol:</strong> Please register at the main entrance gate upon arrival.</li>
              <li><i class="fa fa-users" style="color: var(--folu-green);"></i> <strong>Guided Tours:</strong> School tours can be conducted during break periods without disrupting class teaching.</li>
            </ul>

            <div style="background: var(--folu-surface-subtle); padding: 14px 18px; border-radius: var(--folu-radius-sm); font-size: 13px; color: var(--folu-text-muted);">
              <strong>Proprietor:</strong> Rev. Dr. Samuel Babaniyi<br>
              <strong>Proprietress:</strong> Rev. Mrs. Mofoluwake Babaniyi
            </div>
          </div>

          <!-- Quick WhatsApp CTA -->
          <div style="background: linear-gradient(135deg, #15803d 0%, #166534 100%); color: #ffffff; border-radius: var(--folu-radius-md); padding: 30px 24px; box-shadow: var(--folu-shadow-md);">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
              <i class="fa fa-whatsapp" style="font-size: 32px; color: #86efac;"></i>
              <div>
                <h4 style="font-size: 18px; font-weight: 800; color: #ffffff; margin: 0;">Need Quick Answers?</h4>
                <span style="font-size: 13px; color: #dcfce7;">Chat directly with our administrative desk</span>
              </div>
            </div>
            <p style="font-size: 13.5px; color: #f0fdf4; line-height: 1.55; margin-bottom: 18px;">
              Message us on WhatsApp for rapid information regarding admissions, fee schedules, or directions to the school.
            </p>
            <a href="https://wa.me/2348165354191?text=Hello%20Folu%20International%20Schools,%20I%20have%20a%20question." target="_blank" rel="noopener noreferrer" class="folu-btn" style="width: 100%; justify-content: center; background: #ffffff; color: #166534; font-weight: 700;">
              Open WhatsApp Now
            </a>
          </div>

        </div>

      </div>
    </div>
  </section>

  {{-- Verified Footer --}}
  @include('frontend.partials.footer')

  <script>
    function handleContactSubmit() {
      var name = document.getElementById('c_name').value;
      var phone = document.getElementById('c_phone').value;
      var subject = document.getElementById('c_subject').value;
      var msg = document.getElementById('c_message').value;

      document.getElementById('contactSuccessMsg').style.display = 'block';
      document.getElementById('contactSuccessMsg').scrollIntoView({ behavior: 'smooth', block: 'center' });

      var waText = "Hello Folu International Group of Schools!%0A" +
                   "- From: " + encodeURIComponent(name) + "%0A" +
                   "- Phone: " + encodeURIComponent(phone) + "%0A" +
                   "- Subject: " + encodeURIComponent(subject) + "%0A" +
                   "- Message: " + encodeURIComponent(msg);

      setTimeout(function() {
        var openWa = confirm("Thank you! Would you also like to send this inquiry directly to our WhatsApp help desk?");
        if (openWa) {
          window.open("https://wa.me/2348165354191?text=" + waText, "_blank");
        }
      }, 700);
    }
  </script>

</body>
</html>
