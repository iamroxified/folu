@php($currentSessionLabel = function_exists('current_session_label') ? current_session_label() : 'No active session')
<div class="main-header">
  <div class="main-header-logo">
    <div class="logo-header" data-background-color="dark">
      <a href="{{ url('/student/index.php') }}" class="logo">
        <img src="/admin/assets/img/folu_logo.png" alt="navbar brand" class="navbar-brand" height="20" />
      </a>
      <div class="nav-toggle">
        <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
        <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
      </div>
      <button class="topbar-toggler more"><i class="gg-more-vertical-alt"></i></button>
    </div>
  </div>
  <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
    <div class="container-fluid">
      <div class="d-none d-lg-flex align-items-center">
        <span class="badge bg-primary text-white px-3 py-2">Current Session: <?php echo htmlspecialchars($currentSessionLabel); ?></span>
      </div>
      <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
        <li class="nav-item topbar-user dropdown hidden-caret">
          <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
            <div class="avatar-sm">
              <img src="/admin/assets/img/profile.jpg" alt="profile" class="avatar-img rounded-circle" />
            </div>
            <span class="profile-username">
              <span class="op-7">Hi,</span>
              <span class="fw-bold"><?php echo htmlspecialchars($_SESSION['student_name'] ?? $_SESSION['student_username'] ?? 'Student'); ?></span>
            </span>
          </a>
          <ul class="dropdown-menu dropdown-user animated fadeIn">
            <div class="dropdown-user-scroll scrollbar-outer">
              <li>
                <div class="user-box">
                  <div class="avatar-lg">
                    <img src="/admin/assets/img/profile.jpg" alt="profile" class="avatar-img rounded" />
                  </div>
                  <div class="u-text">
                    <h4><?php echo htmlspecialchars($_SESSION['student_name'] ?? $_SESSION['student_username'] ?? 'Student'); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($_SESSION['student_username'] ?? ''); ?></p>
                  </div>
                </div>
              </li>
              <li>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ url('/student/account.php') }}">Account Settings</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ url('/student/logout.php') }}">Logout</a>
              </li>
            </div>
          </ul>
        </li>
      </ul>
    </div>
  </nav>
</div>
