<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>{{ $post->title }} | FOLU INTERNATIONAL GROUP OF SCHOOLS</title>
  <meta name="description" content="{{ $post->excerpt ?? Str::limit(strip_tags($post->content), 150) }}">

  <!-- OpenGraph / Social Metadata -->
  <meta property="og:title" content="{{ $post->title }} | Folu International Group of Schools">
  <meta property="og:description" content="{{ $post->excerpt ?? Str::limit(strip_tags($post->content), 150) }}">
  <meta property="og:image" content="{{ $post->image_path ? asset($post->image_path) : asset('images/folu-logo.png') }}">
  <meta property="og:type" content="article">

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
          <a href="{{ url('/blog') }}">Stories &amp; News</a>
          <span class="sep">/</span>
          <span class="current">{{ Str::limit($post->title, 40) }}</span>
        </nav>
        <h1 class="folu-page-title" style="font-size: 36px;">{{ $post->title }}</h1>
        <div style="display: flex; align-items: center; gap: 16px; font-size: 14px; color: #cbd5e1; margin-top: 10px;">
          <span><i class="fa fa-calendar-o" style="color: var(--folu-gold); margin-right: 5px;"></i> {{ $post->published_at ? $post->published_at->format('F d, Y') : $post->created_at->format('F d, Y') }}</span>
          <span>&bull;</span>
          <span><i class="fa fa-user-o" style="color: var(--folu-gold); margin-right: 5px;"></i> Folu Editorial</span>
        </div>
      </div>
    </div>
  </header>

  <!-- 2. ARTICLE CONTENT SECTION -->
  <section class="folu-section">
    <div class="folu-container">
      <div class="folu-editorial-grid" style="grid-template-columns: 1.25fr 0.75fr; align-items: start;">
        
        <!-- Left: Article Body -->
        <article style="background: var(--folu-surface); border-radius: var(--folu-radius-lg); border: 1px solid var(--folu-border); overflow: hidden; padding: 44px 40px; box-shadow: var(--folu-shadow-sm);">
          
          @if($post->image_path)
            <div style="border-radius: var(--folu-radius-md); overflow: hidden; margin-bottom: 32px; box-shadow: var(--folu-shadow-md);">
              <img src="{{ asset($post->image_path) }}" alt="{{ $post->title }}" style="width: 100%; max-height: 440px; object-fit: cover; display: block;">
            </div>
          @endif

          <div class="folu-article-content" style="font-size: 16px; color: var(--folu-text-body); line-height: 1.8;">
            {!! $post->content !!}
          </div>

          <!-- Article Footer & Share -->
          <div style="margin-top: 40px; padding-top: 24px; border-top: 1px solid var(--folu-border); display: flex; justify-content: space-between; align-items: center; flex-wrap: gap; gap: 16px;">
            <a href="{{ url('/blog') }}" class="folu-btn folu-btn-secondary" style="padding: 10px 20px; font-size: 13.5px;">
              &larr; Back to All Stories
            </a>

            <a href="https://wa.me/?text={{ urlencode($post->title . ' - Read more: ' . url('/blog/' . $post->slug)) }}" target="_blank" rel="noopener noreferrer" class="folu-btn" style="background: #22c55e; color: #ffffff; padding: 10px 18px; font-size: 13.5px;">
              <i class="fa fa-whatsapp"></i> Share on WhatsApp
            </a>
          </div>

        </article>

        <!-- Right: Sidebar -->
        <div>
          
          <!-- About the School Box -->
          <div style="background: var(--folu-surface); border-radius: var(--folu-radius-md); border: 1px solid var(--folu-border); padding: 28px 24px; box-shadow: var(--folu-shadow-sm); margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
              <img src="{{ asset('images/folu-logo.png') }}" alt="Folu Logo" style="height: 40px;">
              <div>
                <h4 style="margin: 0; font-size: 16px; font-weight: 800; color: var(--folu-navy);">Folu International</h4>
                <span style="font-size: 12px; color: var(--folu-gold); font-weight: 600;">Knowledge &amp; Discipline</span>
              </div>
            </div>
            <p style="font-size: 13.5px; color: var(--folu-text-muted); line-height: 1.6; margin-bottom: 18px;">
              Founded to provide holistic, disciplined, and academically rigorous education across Creche, Nursery, Primary, and Secondary levels in Isanlu, Kogi State.
            </p>
            <a href="{{ url('/about-us') }}" style="font-size: 13px; font-weight: 600; color: var(--folu-blue); text-decoration: none;">
              Read our full story &rarr;
            </a>
          </div>

          <!-- Direct Admissions Help Desk -->
          <div style="background: var(--folu-navy); color: #ffffff; border-radius: var(--folu-radius-md); padding: 28px 24px; box-shadow: var(--folu-shadow-md); margin-bottom: 24px;">
            <span class="folu-section-badge" style="background: rgba(217, 119, 6, 0.2); color: var(--folu-gold-accent); margin-bottom: 8px;">Admissions Desk</span>
            <h4 style="font-size: 18px; font-weight: 700; color: #ffffff; margin-bottom: 10px;">Enrol Your Child</h4>
            <p style="font-size: 13.5px; color: #cbd5e1; line-height: 1.5; margin-bottom: 18px;">
              Admissions are open for Creche, Nursery, Primary, and Secondary school. Speak directly with our admissions desk.
            </p>
            <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 18px; font-size: 13.5px;">
              <a href="tel:08165354191" style="color: #ffffff; text-decoration: none;"><i class="fa fa-phone" style="color: var(--folu-gold);"></i> 08165354191</a>
              <a href="tel:08057421037" style="color: #ffffff; text-decoration: none;"><i class="fa fa-phone" style="color: var(--folu-gold);"></i> 08057421037</a>
            </div>
            <a href="{{ url('/apply') }}" class="folu-btn folu-btn-primary" style="width: 100%; justify-content: center; font-size: 13.5px;">
              Apply Now Online
            </a>
          </div>

        </div>

      </div>
    </div>
  </section>

  {{-- Verified Footer --}}
  @include('frontend.partials.footer')

</body>
</html>
