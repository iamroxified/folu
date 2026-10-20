@php
    $rawHdrStudent = $student ?? null;
    $hdrStudent = null;

    if ($rawHdrStudent instanceof \App\Models\Student) {
        $hdrStudent = $rawHdrStudent;
    } elseif (is_array($rawHdrStudent) && !empty($rawHdrStudent['id'])) {
        $hdrStudent = \App\Models\Student::with(['currentClass', 'currentSession', 'currentTerm'])->find($rawHdrStudent['id']);
    }

    if (!$hdrStudent && auth()->check()) {
        $u = auth()->user();
        $hdrStudent = \App\Models\Student::with(['currentClass', 'currentSession', 'currentTerm'])
            ->where('email', $u->email)
            ->orWhere('admission_no', $u->username)
            ->orWhere('student_number', $u->username)
            ->orWhere('user_link', $u->id)
            ->first();
    }
    if (!$hdrStudent && !empty($_SESSION['student_id'])) {
        $hdrStudent = \App\Models\Student::with(['currentClass', 'currentSession', 'currentTerm'])->find($_SESSION['student_id']);
    }

    if (is_array($hdrStudent)) {
        $hdrStudent = (object) $hdrStudent;
    } elseif (!$hdrStudent && is_array($rawHdrStudent)) {
        $hdrStudent = (object) $rawHdrStudent;
    }

    $hdrSession = $currentSession ?? ($hdrStudent?->currentSession ?? \App\Models\AcademicSession::where('is_active', true)->first());
    $hdrTerm = $currentTerm ?? ($hdrStudent?->currentTerm ?? \App\Models\Term::where('is_active', true)->first());
    $hdrClass = $hdrStudent?->currentClass;

    $hdrPassport = !empty($hdrStudent?->passport) 
        ? (str_starts_with($hdrStudent->passport, 'http') || str_starts_with($hdrStudent->passport, '/') ? $hdrStudent->passport : '/storage/' . $hdrStudent->passport)
        : null;
@endphp

<div class="main-header">
  <div class="main-header-logo">
    <div class="logo-header" data-background-color="dark">
      <a href="{{ route('student.dashboard') }}" class="logo text-white d-flex align-items-center gap-2 text-decoration-none">
        <img src="/images/folu-logo.png" alt="logo" height="30" onerror="this.onerror=null; this.src='/admin/assets/img/folu_logo.png';" />
        <span class="fw-bold" style="font-size: 13px;">FOLU PORTAL</span>
      </a>
      <div class="nav-toggle">
        <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
        <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
      </div>
      <button class="topbar-toggler more"><i class="gg-more-vertical-alt"></i></button>
    </div>
  </div>

  <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom bg-white shadow-sm py-2">
    <div class="container-fluid">
      <!-- Academic Context Badges (Left) -->
      <div class="d-none d-md-flex align-items-center gap-2">
        @if($hdrSession)
          <span class="badge bg-primary text-white px-3 py-2 fw-bold" style="background: linear-gradient(135deg, #720922, #8e1532) !important;">
            <i class="fas fa-calendar-alt me-1 text-warning"></i> Session: {{ $hdrSession->session_name }}
          </span>
        @endif
        @if($hdrTerm)
          <span class="badge bg-light text-dark border px-3 py-2 fw-bold">
            <i class="fas fa-clock me-1 text-primary"></i> {{ $hdrTerm->term_name }}
          </span>
        @endif
        @if($hdrClass)
          <span class="badge bg-warning-subtle text-dark border border-warning px-3 py-2 fw-bold">
            <i class="fas fa-chalkboard me-1 text-danger"></i> Class: {{ $hdrClass->class_name }}
          </span>
        @endif
      </div>

      <!-- Right Navigation Icons & Profile Dropdown -->
      <ul class="navbar-nav topbar-nav ms-auto align-items-center gap-2">
        <!-- Announcements Shortcut -->
        <li class="nav-item">
          <a class="nav-link btn btn-icon btn-round btn-light border position-relative" href="{{ url('/student/announcements.php') }}" title="School Notices">
            <i class="fas fa-bell text-danger"></i>
          </a>
        </li>

        <!-- Quick Results Shortcut -->
        <li class="nav-item d-none d-sm-block">
          <a class="btn btn-outline-primary btn-sm fw-bold px-3" href="{{ route('student.results') }}">
            <i class="fas fa-award me-1"></i> Result Slip
          </a>
        </li>

        <!-- User Profile Dropdown -->
        <li class="nav-item topbar-user dropdown hidden-caret ms-2">
          <a class="dropdown-toggle profile-pic d-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="dropdown" href="#" aria-expanded="false">
            <div class="avatar-sm">
              @if($hdrPassport)
                <img src="{{ $hdrPassport }}" alt="{{ $hdrStudent?->first_name }}" class="avatar-img rounded-circle border border-2 border-primary" style="object-fit: cover; width: 38px; height: 38px;" />
              @else
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 38px; height: 38px; background: linear-gradient(135deg, #720922, #8e1532); font-size: 14px;">
                  {{ strtoupper(substr($hdrStudent?->first_name ?? 'S', 0, 1) . substr($hdrStudent?->last_name ?? 'T', 0, 1)) }}
                </div>
              @endif
            </div>
            <div class="d-none d-lg-flex flex-column text-start">
              <span class="profile-username fw-bold text-dark lh-1" style="font-size: 13px;">
                {{ $hdrStudent?->first_name }} {{ $hdrStudent?->last_name }}
              </span>
              <span class="text-muted small" style="font-size: 11px;">
                {{ $hdrStudent?->admission_no ?? 'Student' }}
              </span>
            </div>
            <i class="fas fa-chevron-down text-muted small ms-1 d-none d-lg-inline"></i>
          </a>
          <ul class="dropdown-menu dropdown-user animated fadeIn shadow-lg border-0 rounded-3 mt-2" style="min-width: 240px;">
            <div class="dropdown-user-scroll scrollbar-outer">
              <li class="p-3 bg-light border-bottom">
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar-md">
                    @if($hdrPassport)
                      <img src="{{ $hdrPassport }}" alt="{{ $hdrStudent?->first_name }}" class="avatar-img rounded-circle border border-2 border-primary" style="object-fit: cover; width: 48px; height: 48px;" />
                    @else
                      <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 48px; height: 48px; background: linear-gradient(135deg, #720922, #8e1532); font-size: 18px;">
                        {{ strtoupper(substr($hdrStudent?->first_name ?? 'S', 0, 1) . substr($hdrStudent?->last_name ?? 'T', 0, 1)) }}
                      </div>
                    @endif
                  </div>
                  <div class="u-text text-truncate">
                    <h6 class="fw-bold mb-0 text-dark text-truncate">{{ $hdrStudent?->first_name }} {{ $hdrStudent?->last_name }}</h6>
                    <small class="text-muted d-block text-truncate">Adm: {{ $hdrStudent?->admission_no ?? 'N/A' }}</small>
                    <span class="badge bg-primary text-white" style="font-size: 10px;">{{ $hdrClass?->class_name ?? 'Student' }}</span>
                  </div>
                </div>
              </li>
              <li>
                <div class="dropdown-divider my-1"></div>
                <a class="dropdown-item py-2" href="{{ route('student.profile') }}">
                  <i class="fas fa-user-circle me-2 text-primary"></i> My Full Profile
                </a>
                <a class="dropdown-item py-2" href="{{ route('student.results') }}">
                  <i class="fas fa-award me-2 text-warning"></i> Examination Results
                </a>
                <a class="dropdown-item py-2" href="{{ route('student.receipts') }}">
                  <i class="fas fa-receipt me-2 text-info"></i> Payment Receipts
                </a>
                <a class="dropdown-item py-2" href="{{ url('/student/account.php') }}">
                  <i class="fas fa-key me-2 text-secondary"></i> Change Password
                </a>
                <div class="dropdown-divider my-1"></div>
                <a class="dropdown-item py-2 text-danger fw-bold" href="{{ route('logout') }}">
                  <i class="fas fa-sign-out-alt me-2 text-danger"></i> Sign Out
                </a>
              </li>
            </div>
          </ul>
        </li>
      </ul>
    </div>
  </nav>
</div>
