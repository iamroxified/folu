@php
  $rawStudent = $student ?? null;
  $currentStudent = null;

  if ($rawStudent instanceof \App\Models\Student) {
    $currentStudent = $rawStudent;
  } elseif (is_array($rawStudent) && !empty($rawStudent['id'])) {
    $currentStudent = \App\Models\Student::with(['currentClass', 'currentSession', 'currentTerm'])->find($rawStudent['id']);
  }

  if (!$currentStudent && auth()->check()) {
    $u = auth()->user();
    $currentStudent = \App\Models\Student::with(['currentClass', 'currentSession', 'currentTerm'])
      ->where('email', $u->email)
      ->orWhere('admission_no', $u->username)
      ->orWhere('student_number', $u->username)
      ->orWhere('user_link', $u->id)
      ->first();
  }
  if (!$currentStudent && !empty($_SESSION['student_id'])) {
    $currentStudent = \App\Models\Student::with(['currentClass', 'currentSession', 'currentTerm'])->find($_SESSION['student_id']);
  }

  if (is_array($currentStudent)) {
    $currentStudent = (object) $currentStudent;
  } elseif (!$currentStudent && is_array($rawStudent)) {
    $currentStudent = (object) $rawStudent;
  }

  $currentRouteName = request()->route() ? request()->route()->getName() : '';
  $currentPath = trim(request()->path(), '/');

  $isNavActive = function ($patterns) use ($currentRouteName, $currentPath) {
    foreach ((array) $patterns as $p) {
      if ($currentRouteName === $p || $currentRouteName === 'student.' . $p || str_contains($currentPath, $p)) {
        return 'active';
      }
    }
    return '';
  };

  $passportUrl = !empty($currentStudent?->passport)
    ? (str_starts_with($currentStudent->passport, 'http') || str_starts_with($currentStudent->passport, '/') ? $currentStudent->passport : '/storage/' . $currentStudent->passport)
    : null;
@endphp

<div class="sidebar" data-background-color="dark">
  <div class="sidebar-logo">
    <div class="logo-header" data-background-color="dark" style="border-bottom: 1px solid rgba(255,255,255,0.08);">
      <a href="{{ route('student.dashboard') }}"
        class="logo text-white d-flex align-items-center gap-2 text-decoration-none">
        <img src="/images/folu-logo.png" alt="Folu International Schools" height="38" class="navbar-brand"
          onerror="this.onerror=null; this.src='/admin/assets/img/folu_logo.png';" />
        <div class="d-flex flex-column text-start">
          <span class="fw-bold text-white lh-1" style="font-size: 14px; letter-spacing: 0.5px;">FOLU SCHOOLS</span>
          <span class="text-white-50" style="font-size: 11px;">Student Portal</span>
        </div>
      </a>
      <div class="nav-toggle">
        <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
        <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
      </div>
      <button class="topbar-toggler more"><i class="gg-more-vertical-alt"></i></button>
    </div>
  </div>

  <div class="sidebar-wrapper scrollbar scrollbar-inner">
    <div class="sidebar-content">
      @if($currentStudent)
        <!-- Student Mini Profile Card -->
        <div class="user-profile-widget p-3 mx-3 my-3 rounded"
          style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
          <div class="d-flex align-items-center gap-2">
            <div class="avatar avatar-md position-relative">
              @if($passportUrl)
                <img src="{{ $passportUrl }}" alt="{{ $currentStudent->first_name }}"
                  class="avatar-img rounded-circle border border-2 border-warning"
                  style="object-fit: cover; width: 44px; height: 44px;" />
              @else
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                  style="width: 44px; height: 44px; background: linear-gradient(135deg, #720922, #8e1532); font-size: 16px;">
                  {{ strtoupper(substr($currentStudent->first_name ?? 'S', 0, 1) . substr($currentStudent->last_name ?? 'T', 0, 1)) }}
                </div>
              @endif
              <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-dark rounded-circle"
                title="Online"></span>
            </div>
            <div class="user-info text-truncate">
              <h6 class="text-white fw-bold mb-0 text-truncate" style="font-size: 13px;">
                {{ $currentStudent->first_name }} {{ $currentStudent->last_name }}
              </h6>
              <div class="text-white-50 small text-truncate" style="font-size: 11px;">
                <code>{{ $currentStudent->admission_no ?? $currentStudent->student_number ?? 'Student' }}</code>
              </div>
              @if($currentStudent->currentClass)
                <span class="badge bg-warning text-dark px-2 py-0 mt-1" style="font-size: 10px;">
                  {{ $currentStudent->currentClass->class_name }}
                </span>
              @endif
            </div>
          </div>
        </div>
      @endif

      <ul class="nav nav-secondary">
        <li class="nav-section">
          <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
          <h4 class="text-section text-white-50"
            style="font-size: 10px; text-transform: uppercase; letter-spacing: 1px;">Academic Workspace</h4>
        </li>

        <li class="nav-item {{ $isNavActive(['dashboard', 'index']) }}">
          <a href="{{ route('student.dashboard') }}">
            <i class="fas fa-th-large text-primary"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <li class="nav-item {{ $isNavActive(['profile']) }}">
          <a href="{{ route('student.profile') }}">
            <i class="fas fa-user-graduate text-info"></i>
            <p>My Profile</p>
          </a>
        </li>

        <li class="nav-item {{ $isNavActive(['results']) }}">
          <a href="{{ route('student.results') }}">
            <i class="fas fa-award text-warning"></i>
            <p>Academic Results</p>
          </a>
        </li>

        <li class="nav-item {{ $isNavActive(['payments']) }}">
          <a href="{{ route('student.payments') }}">
            <i class="fas fa-wallet text-success"></i>
            <p>Fees & Payments</p>
          </a>
        </li>

        <li class="nav-item {{ $isNavActive(['receipts', 'receipt']) }}">
          <a href="{{ route('student.receipts') }}">
            <i class="fas fa-receipt text-info"></i>
            <p>Payment Receipts</p>
          </a>
        </li>

        <li class="nav-item {{ $isNavActive(['attendance']) }}">
          <a href="{{ url('/student/attendance.php') }}">
            <i class="fas fa-calendar-check text-success"></i>
            <p>Attendance Record</p>
          </a>
        </li>

        <li class="nav-section mt-3">
          <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
          <h4 class="text-section text-white-50"
            style="font-size: 10px; text-transform: uppercase; letter-spacing: 1px;">Support & Records</h4>
        </li>

        <li class="nav-item {{ $isNavActive(['announcements']) }}">
          <a href="{{ url('/student/announcements.php') }}">
            <i class="fas fa-bullhorn text-danger"></i>
            <p>Notice Board</p>
          </a>
        </li>

        <li class="nav-item {{ $isNavActive(['complaints']) }}">
          <a href="{{ route('student.complaints') }}">
            <i class="fas fa-comment-dots text-warning"></i>
            <p>Complaints & Help</p>
          </a>
        </li>

        <li class="nav-item {{ $isNavActive(['documents']) }}">
          <a href="{{ route('student.documents') }}">
            <i class="fas fa-folder-open text-primary"></i>
            <p>My Documents</p>
          </a>
        </li>

        <li class="nav-item {{ $isNavActive(['progression']) }}">
          <a href="{{ route('student.progression') }}">
            <i class="fas fa-graduation-cap text-info"></i>
            <p>Academic Progression</p>
          </a>
        </li>

        <li class="nav-item {{ $isNavActive(['account']) }}">
          <a href="{{ url('/student/account.php') }}">
            <i class="fas fa-shield-alt text-secondary"></i>
            <p>Account Security</p>
          </a>
        </li>

        <li class="nav-item mt-2">
          <a href="{{ route('logout') }}" class="text-danger">
            <i class="fas fa-sign-out-alt text-danger"></i>
            <p>Logout</p>
          </a>
        </li>
      </ul>
    </div>
  </div>
</div>