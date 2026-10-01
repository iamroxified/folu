<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Unified Portal Login | Folu International Group of Schools</title>
  <link rel="icon" href="{{ asset('images/folu-logo.png') }}" type="image/x-icon">
  
  <!-- FontAwesome & Google Fonts -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Folu Modern Core Stylesheet -->
  <link rel="stylesheet" href="{{ asset('css/folu-modern.css') }}" type="text/css" media="all">

  <style>
    :root {
      --folu-wine: #720922;          /* Rich Regal Wine */
      --folu-wine-dark: #38040f;     /* Deep Velveteen Wine */
      --folu-wine-light: #8e1532;    /* Luminous Wine */
      --folu-wine-soft: #fcf1f3;     /* Soft Wine tint */
      
      --folu-peach: #e76f51;         /* Warm Signature Peach */
      --folu-peach-accent: #f4845f;  /* Radiant Peach Highlight */
      --folu-peach-light: #ffece5;   /* Creamy Peach tint */
      --folu-peach-hover: #d95d3f;   /* Deep Peach hover */
      
      --folu-milk-page: #faf6ee;     /* Creamy Page Background */
      --folu-border-subtle: #e6d7c3; /* Soft Border */
      --folu-text-heading: #38040f;  /* Dark Wine Heading */
      --folu-text-body: #443733;     /* Warm Body Text */
      --folu-text-muted: #78665f;    /* Muted Text */
      
      --radius-lg: 20px;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    body {
      background: linear-gradient(135deg, #38040f 0%, #720922 50%, #38040f 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px 16px;
      color: var(--folu-text-body);
    }

    .login-container {
      width: 100%;
      max-width: 1040px;
      background: #ffffff;
      border-radius: var(--radius-lg);
      box-shadow: 0 20px 40px -10px rgba(56, 4, 15, 0.35);
      overflow: hidden;
      display: grid;
      grid-template-columns: 1fr 1.2fr;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    @media (max-width: 850px) {
      .login-container {
        grid-template-columns: 1fr;
        max-width: 480px;
      }
      .brand-side {
        display: none;
      }
    }

    /* Left side brand panel - Regal Wine theme */
    .brand-side {
      background: linear-gradient(135deg, rgba(114, 9, 34, 0.96), rgba(56, 4, 15, 0.97)), url('{{ asset("images/folu-logo.png") }}') center/cover;
      padding: 48px 36px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      color: #ffffff;
      position: relative;
    }

    .brand-header {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .brand-logo {
      width: 52px;
      height: 52px;
      border-radius: 12px;
      object-fit: cover;
      background: #ffffff;
      padding: 4px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .brand-title {
      font-family: 'Outfit', sans-serif;
      font-size: 20px;
      font-weight: 800;
      letter-spacing: -0.01em;
      color: #ffffff;
    }

    .brand-subtitle {
      font-size: 13px;
      color: var(--folu-peach-accent);
      font-weight: 600;
    }

    .brand-hero {
      margin: 40px 0;
    }

    .brand-hero h1 {
      font-family: 'Outfit', sans-serif;
      font-size: 28px;
      font-weight: 800;
      line-height: 1.3;
      margin-bottom: 14px;
      color: #ffffff;
    }

    .brand-hero p {
      font-size: 14px;
      color: #f1d3cb;
      line-height: 1.6;
    }

    .brand-features {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .brand-features li {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 13.5px;
      color: #ffffff;
    }

    .brand-features i {
      color: var(--folu-peach);
      font-size: 14px;
    }

    .brand-footer {
      font-size: 12px;
      color: #d1b5ac;
      border-top: 1px solid rgba(255, 255, 255, 0.15);
      padding-top: 20px;
    }

    /* Right side form panel */
    .form-side {
      padding: 44px 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      background: #ffffff;
    }

    .form-header {
      margin-bottom: 26px;
    }

    .form-header h2 {
      font-family: 'Outfit', sans-serif;
      font-size: 26px;
      font-weight: 800;
      color: var(--folu-wine-dark);
      margin-bottom: 6px;
    }

    .form-header p {
      font-size: 14px;
      color: var(--folu-text-muted);
    }

    /* Category Role Tabs */
    .role-tabs {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 8px;
      background: #faf6ee;
      padding: 6px;
      border-radius: 14px;
      border: 1px solid var(--folu-border-subtle);
      margin-bottom: 24px;
    }

    .role-tab {
      border: none;
      background: transparent;
      padding: 10px 4px;
      border-radius: 10px;
      font-size: 12.5px;
      font-weight: 700;
      color: var(--folu-text-muted);
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 4px;
      transition: all 0.2s ease;
    }

    .role-tab i {
      font-size: 15px;
    }

    .role-tab:hover {
      color: var(--folu-wine);
    }

    .role-tab.active {
      background: #ffffff;
      color: var(--folu-wine);
      box-shadow: 0 4px 10px rgba(114, 9, 34, 0.12);
      border: 1px solid var(--folu-wine-soft);
    }

    /* Alert Boxes */
    .alert {
      padding: 14px 16px;
      border-radius: 12px;
      font-size: 13.5px;
      margin-bottom: 20px;
      display: flex;
      align-items: flex-start;
      gap: 12px;
    }

    .alert-danger {
      background: #fef2f2;
      border: 1.5px solid #fecaca;
      color: #991b1b;
    }

    .alert-success {
      background: #f0fdf4;
      border: 1.5px solid #bbf7d0;
      color: #166534;
    }

    /* Form Fields */
    .form-group {
      margin-bottom: 20px;
    }

    .form-label {
      display: block;
      font-size: 13px;
      font-weight: 700;
      color: var(--folu-text-heading);
      margin-bottom: 8px;
    }

    .input-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-icon {
      position: absolute;
      left: 14px;
      color: #a3958f;
      font-size: 15px;
      pointer-events: none;
      transition: color 0.2s ease;
    }

    .form-control {
      width: 100%;
      padding: 13px 14px 13px 42px;
      border: 1.5px solid var(--folu-border-subtle);
      border-radius: 12px;
      font-size: 14px;
      color: var(--folu-text-heading);
      background: #ffffff;
      outline: none;
      transition: all 0.2s ease;
    }

    .form-control:focus {
      border-color: var(--folu-wine);
      box-shadow: 0 0 0 4px rgba(114, 9, 34, 0.1);
    }

    .form-control:focus + .input-icon {
      color: var(--folu-wine);
    }

    .toggle-password {
      position: absolute;
      right: 14px;
      color: #a3958f;
      cursor: pointer;
      font-size: 15px;
      background: none;
      border: none;
      padding: 4px;
    }

    .toggle-password:hover {
      color: var(--folu-wine-dark);
    }

    .form-options {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
      font-size: 13px;
    }

    .remember-me {
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      color: var(--folu-text-muted);
      font-weight: 500;
    }

    .remember-me input {
      accent-color: var(--folu-wine);
      width: 16px;
      height: 16px;
    }

    .forgot-link {
      color: var(--folu-wine);
      text-decoration: none;
      font-weight: 700;
    }

    .forgot-link:hover {
      color: var(--folu-peach);
      text-decoration: underline;
    }

    /* Submit Button - Folu Signature Peach & Wine */
    .btn-submit {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, var(--folu-wine), var(--folu-wine-light));
      color: #ffffff;
      border: none;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      box-shadow: 0 6px 18px rgba(114, 9, 34, 0.28);
      transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .btn-submit:hover {
      background: linear-gradient(135deg, var(--folu-peach), var(--folu-peach-hover));
      transform: translateY(-2px);
      box-shadow: 0 8px 22px rgba(231, 111, 81, 0.35);
    }

    .btn-submit:active {
      transform: translateY(0);
    }

    /* Notice banner matching site cards */
    .category-notice {
      background: var(--folu-wine-soft);
      border: 1px solid #f3d5dc;
      border-radius: 10px;
      padding: 11px 14px;
      font-size: 12.5px;
      color: var(--folu-wine);
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 600;
    }

    .home-link-footer {
      text-align: center;
      margin-top: 24px;
      font-size: 13px;
      color: var(--folu-text-muted);
    }

    .home-link-footer a {
      color: var(--folu-wine);
      font-weight: 700;
      text-decoration: none;
    }

    .home-link-footer a:hover {
      color: var(--folu-peach);
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="login-container">
    <!-- Left branding pane -->
    <div class="brand-side">
      <div class="brand-header">
        <img src="{{ asset('images/folu-logo.png') }}" alt="Folu Logo" class="brand-logo" onerror="this.src='/images/folu_logo.jpg'">
        <div>
          <div class="brand-title">FOLU SCHOOLS</div>
          <div class="brand-subtitle">Isanlu, Kogi State</div>
        </div>
      </div>

      <div class="brand-hero">
        <h1>Unified Learning &amp; Management Portal</h1>
        <p>Access academic records, term report cards, fee receipts, payroll, and school administrative tools from a single secure login portal.</p>
      </div>

      <ul class="brand-features">
        <li><i class="fa-solid fa-circle-check"></i> Real-time Results &amp; Continuous Assessments</li>
        <li><i class="fa-solid fa-circle-check"></i> Digital Fee Invoices &amp; Payment Verification</li>
        <li><i class="fa-solid fa-circle-check"></i> Dedicated Student, Parent, Staff &amp; Admin Dashboards</li>
      </ul>

      <div class="brand-footer">
        &copy; {{ date('Y') }} Folu International Group of Schools. All rights reserved.
      </div>
    </div>

    <!-- Right form pane -->
    <div class="form-side">
      <div class="form-header">
        <h2>Welcome Back</h2>
        <p>Select your category tab to log in to your account</p>
      </div>

      <!-- Category Role Selector Tabs -->
      <div class="role-tabs">
        <button type="button" class="role-tab {{ $activeRole === 'student' ? 'active' : '' }}" onclick="switchRole('student')">
          <i class="fa-solid fa-user-graduate"></i>
          <span>Student</span>
        </button>
        <button type="button" class="role-tab {{ $activeRole === 'parent' ? 'active' : '' }}" onclick="switchRole('parent')">
          <i class="fa-solid fa-users"></i>
          <span>Parent</span>
        </button>
        <button type="button" class="role-tab {{ $activeRole === 'staff' ? 'active' : '' }}" onclick="switchRole('staff')">
          <i class="fa-solid fa-chalkboard-user"></i>
          <span>Staff</span>
        </button>
        <button type="button" class="role-tab {{ $activeRole === 'admin' ? 'active' : '' }}" onclick="switchRole('admin')">
          <i class="fa-solid fa-shield-halved"></i>
          <span>Admin</span>
        </button>
      </div>

      <!-- Category Context Banner -->
      <div id="categoryNotice" class="category-notice">
        <i class="fa-solid fa-circle-info" style="font-size: 15px;"></i>
        <span id="noticeText">Enter your Admission Number or Student Email to sign in.</span>
      </div>

      <!-- Alert Error Display -->
      @if (session('error'))
        <div class="alert alert-danger">
          <i class="fa-solid fa-triangle-exclamation" style="font-size: 16px;"></i>
          <div>{{ session('error') }}</div>
        </div>
      @endif

      @if ($errors->any())
        <div class="alert alert-danger">
          <i class="fa-solid fa-circle-xmark" style="font-size: 16px;"></i>
          <div>
            @foreach ($errors->all() as $error)
              <div>{{ $error }}</div>
            @endforeach
          </div>
        </div>
      @endif

      @if (session('success'))
        <div class="alert alert-success">
          <i class="fa-solid fa-circle-check" style="font-size: 16px;"></i>
          <div>{{ session('success') }}</div>
        </div>
      @endif

      <form method="POST" action="{{ route('login.submit') }}">
        @csrf
        <input type="hidden" name="role" id="roleInput" value="{{ $activeRole }}">

        <!-- Username / Identifier -->
        <div class="form-group">
          <label for="username" id="usernameLabel" class="form-label">Admission Number / Email</label>
          <div class="input-wrapper">
            <input type="text" name="username" id="username" class="form-control" placeholder="e.g. FOLU/2026/001" value="{{ old('username') }}" required autofocus>
            <i class="fa-solid fa-user input-icon"></i>
          </div>
        </div>

        <!-- Password -->
        <div class="form-group">
          <label for="password" class="form-label">Password</label>
          <div class="input-wrapper">
            <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
            <i class="fa-solid fa-lock input-icon"></i>
            <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">
              <i class="fa-solid fa-eye" id="passwordEyeIcon"></i>
            </button>
          </div>
        </div>

        <!-- Options -->
        <div class="form-options">
          <label class="remember-me">
            <input type="checkbox" name="remember_me" value="1" checked>
            <span>Remember me</span>
          </label>
          <a href="{{ route('password.request') }}" class="forgot-link">Forgot Password?</a>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-submit">
          <span id="btnText">Sign In to Student Portal</span>
          <i class="fa-solid fa-arrow-right"></i>
        </button>
      </form>

      <div class="home-link-footer">
        Return to main site? <a href="{{ url('/') }}">Back to Homepage</a> &bull; Need help? <a href="{{ url('/apply') }}">Apply for Admission</a>
      </div>
    </div>
  </div>

  <script>
    const roleConfigs = {
      student: {
        label: 'Admission Number / Email',
        placeholder: 'e.g. FOLU/2026/001 or student email',
        notice: 'Students enter your Admission Number or Student Email to sign in.',
        btnText: 'Sign In to Student Portal'
      },
      parent: {
        label: 'Parent Email / Phone / Student Admission No',
        placeholder: 'e.g. parent@example.com or 08012345678',
        notice: 'Parents enter your registered Email, Phone, or your child\'s Admission Number.',
        btnText: 'Sign In to Parent Portal'
      },
      staff: {
        label: 'Staff ID / Username / Email',
        placeholder: 'e.g. TCH001 or staff email',
        notice: 'Teachers and Staff enter your Staff ID or official email address.',
        btnText: 'Sign In to Staff Portal'
      },
      admin: {
        label: 'Admin Username / Email',
        placeholder: 'e.g. admin or administrator email',
        notice: 'Administrative personnel only. Sign in to manage school system.',
        btnText: 'Sign In to Admin Dashboard'
      }
    };

    function switchRole(role) {
      document.querySelectorAll('.role-tab').forEach(tab => tab.classList.remove('active'));
      const activeTab = document.querySelector(`.role-tab[onclick*="${role}"]`);
      if (activeTab) activeTab.classList.add('active');

      document.getElementById('roleInput').value = role;

      const config = roleConfigs[role] || roleConfigs.student;
      document.getElementById('usernameLabel').innerText = config.label;
      document.getElementById('username').placeholder = config.placeholder;
      document.getElementById('noticeText').innerText = config.notice;
      document.getElementById('btnText').innerText = config.btnText;
    }

    function togglePasswordVisibility() {
      const passInput = document.getElementById('password');
      const eyeIcon = document.getElementById('passwordEyeIcon');
      if (passInput.type === 'password') {
        passInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
      } else {
        passInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
      }
    }

    // Initialize initial state based on default active role
    document.addEventListener('DOMContentLoaded', function() {
      const initialRole = document.getElementById('roleInput').value || 'student';
      switchRole(initialRole);
    });
  </script>
</body>
</html>
