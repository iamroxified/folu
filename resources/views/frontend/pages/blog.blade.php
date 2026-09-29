<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Stories &amp; News | FOLU INTERNATIONAL GROUP OF SCHOOLS | Isanlu, Kogi State</title>
  <meta name="description" content="Read the latest school news, student achievements, academic milestones, and campus activities at Folu International Group of Schools in Itedo-Ijowa, Isanlu.">
  <meta name="keywords" content="Folu International Schools Blog, School News Isanlu, Student Achievements Kogi State, Folu Stories">

  <!-- OpenGraph / Social Metadata -->
  <meta property="og:title" content="Stories &amp; News | Folu International Group of Schools">
  <meta property="og:description" content="Discover inspiring stories of academic excellence, moral discipline, and student life in Isanlu, Kogi State.">
  <meta property="og:image" content="{{ asset('images/assembly.jpg') }}">
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
          <span class="current">Stories &amp; News</span>
        </nav>
        <h1 class="folu-page-title">Folu Stories &amp; Campus Updates</h1>
        <p class="folu-page-subtitle">
          Celebrating student achievements, academic discoveries, inter-house sports, and community life at Folu International Group of Schools in Itedo-Ijowa, Isanlu.
        </p>
      </div>
    </div>
  </header>

  <!-- 2. BLOG LISTING SECTION -->
  <section class="folu-section">
    <div class="folu-container">
      <div class="folu-editorial-grid" style="grid-template-columns: 1.25fr 0.75fr; align-items: start;">
        
        <!-- Left: Stories List -->
        <div>
          @if(isset($posts) && $posts->count() > 0)
            <div style="display: flex; flex-direction: column; gap: 32px;">
              @foreach($posts as $post)
                <article style="background: var(--folu-surface); border-radius: var(--folu-radius-lg); border: 1px solid var(--folu-border); overflow: hidden; box-shadow: var(--folu-shadow-sm); transition: var(--folu-transition);">
                  
                  @if($post->image_path)
                    <div style="height: 260px; overflow: hidden; position: relative;">
                      <a href="{{ url('/blog/' . $post->slug) }}">
                        <img src="{{ asset($post->image_path) }}" alt="{{ $post->title }}" style="width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.5s ease;" onmouseover="this.style.transform='scale(1.04)'" onmouseout="this.style.transform='scale(1)'">
                      </a>
                      <span class="folu-journey-badge" style="position: absolute; top: 18px; left: 18px;">
                        School Story
                      </span>
                    </div>
                  @endif

                  <div style="padding: 32px 30px;">
                    <div style="display: flex; align-items: center; gap: 14px; font-size: 13px; color: var(--folu-text-muted); margin-bottom: 12px;">
                      <span><i class="fa fa-calendar-o" style="color: var(--folu-gold); margin-right: 4px;"></i> {{ $post->published_at ? $post->published_at->format('M d, Y') : $post->created_at->format('M d, Y') }}</span>
                      <span>&bull;</span>
                      <span><i class="fa fa-user-o" style="color: var(--folu-gold); margin-right: 4px;"></i> Folu Editorial</span>
                    </div>

                    <h2 style="font-size: 22px; font-weight: 800; color: var(--folu-navy); margin-bottom: 12px; line-height: 1.35;">
                      <a href="{{ url('/blog/' . $post->slug) }}" style="color: var(--folu-navy); text-decoration: none; transition: var(--folu-transition);">
                        {{ $post->title }}
                      </a>
                    </h2>

                    <p style="font-size: 14.5px; color: var(--folu-text-body); line-height: 1.65; margin-bottom: 20px;">
                      {{ $post->excerpt ?? Str::limit(strip_tags($post->content), 160) }}
                    </p>

                    <a href="{{ url('/blog/' . $post->slug) }}" class="folu-btn folu-btn-primary" style="padding: 10px 22px; font-size: 13.5px;">
                      Read Full Story &rarr;
                    </a>
                  </div>

                </article>
              @endforeach
            </div>

            <!-- Pagination -->
            <div style="margin-top: 36px;">
              {{ $posts->links() }}
            </div>

          @else
            <!-- Elegant Empty State -->
            <div style="background: var(--folu-surface); border-radius: var(--folu-radius-lg); border: 1px solid var(--folu-border); padding: 60px 40px; text-align: center; box-shadow: var(--folu-shadow-sm);">
              <div style="width: 70px; height: 70px; border-radius: 50%; background: var(--folu-blue-soft); color: var(--folu-blue); display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 20px;">
                <i class="fa fa-newspaper-o"></i>
              </div>
              <h3 style="font-size: 24px; font-weight: 800; color: var(--folu-navy); margin-bottom: 10px;">Stories &amp; News Coming Soon</h3>
              <p style="font-size: 15px; color: var(--folu-text-muted); max-width: 520px; margin: 0 auto 24px auto; line-height: 1.6;">
                We are currently compiling fresh stories of student achievements, inter-house competitions, and academic milestones from our campus in Itedo-Ijowa, Isanlu.
              </p>
              <a href="{{ url('/apply') }}" class="folu-btn folu-btn-primary">Apply for Admission</a>
            </div>
          @endif
        </div>

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
