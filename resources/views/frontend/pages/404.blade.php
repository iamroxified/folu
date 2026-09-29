<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Page Not Found (404) | FOLU INTERNATIONAL GROUP OF SCHOOLS</title>
  <meta name="description" content="The page you are looking for cannot be found. Return to Folu International Group of Schools homepage.">

  <!-- Favicon & Icons -->
  <link rel="icon" href="{{ asset('images/folu-logo.png') }}" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  
  <!-- Modern Folu 2026 Stylesheet -->
  <link rel="stylesheet" href="{{ asset('css/folu-modern.css') }}" type="text/css" media="all">
</head>

<body class="folu-theme">

  {{-- Header with verified info & navigation --}}
  @include('frontend.partials.header')

  <!-- 404 CONTENT SECTION -->
  <section class="folu-section" style="padding: 100px 0; text-align: center;">
    <div class="folu-container">
      <div style="max-width: 620px; margin: 0 auto; background: var(--folu-surface); border-radius: var(--folu-radius-xl); border: 1px solid var(--folu-border); padding: 60px 36px; box-shadow: var(--folu-shadow-lg);">
        <div style="font-family: var(--folu-font-heading); font-size: 88px; font-weight: 800; color: var(--folu-navy); line-height: 1; margin-bottom: 12px; letter-spacing: -0.04em;">
          4<span style="color: var(--folu-gold);">0</span>4
        </div>
        <span class="folu-section-badge" style="background: var(--folu-gold-light); color: var(--folu-gold); margin-bottom: 16px;">Page Not Found</span>
        <h1 style="font-size: 28px; font-weight: 800; color: var(--folu-navy); margin-bottom: 14px;">Oops! This page could not be located.</h1>
        <p style="font-size: 15px; color: var(--folu-text-muted); line-height: 1.6; margin-bottom: 32px;">
          The page you requested may have moved or no longer exists. Use the buttons below to return home or browse our primary academic programmes.
        </p>

        <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;">
          <a href="{{ url('/') }}" class="folu-btn folu-btn-primary" style="padding: 14px 28px;">
            <i class="fa fa-home"></i> Return to Homepage
          </a>
          <a href="{{ url('/apply') }}" class="folu-btn folu-btn-secondary" style="padding: 14px 24px;">
            Apply for Admission
          </a>
          <a href="{{ url('/contact') }}" class="folu-btn folu-btn-outline" style="border-color: var(--folu-navy); color: var(--folu-navy); padding: 14px 24px;">
            Contact Support
          </a>
        </div>
      </div>
    </div>
  </section>

  {{-- Verified Footer --}}
  @include('frontend.partials.footer')

</body>
</html>
