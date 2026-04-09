@php
    $portalPath = ltrim(preg_replace('/^teacher\/?/i', '', request()->path()), '/');
    $portalPath = preg_replace('/\.(html|php)$/i', '', $portalPath) ?: 'index';

    $matchesPath = function (array $patterns) use ($portalPath): bool {
        foreach ($patterns as $pattern) {
            $pattern = trim($pattern, '/');

            if ($portalPath === $pattern || str_starts_with($portalPath, $pattern . '/')) {
                return true;
            }
        }

        return false;
    };

    $isActive = fn (array $patterns): string => $matchesPath($patterns) ? 'active' : '';
@endphp
<div class="sidebar" data-background-color="dark">
  <div class="sidebar-logo">
    <div class="logo-header" data-background-color="dark">
      <a href="{{ url('/teacher/index.php') }}" class="logo text-white">
        <img src="/admin/assets/img/folu-banner-white.png" alt="navbar brand" width="50%" class="navbar-brand img" />
      </a>
      <div class="nav-toggle">
        <button class="btn btn-toggle toggle-sidebar">
          <i class="gg-menu-right"></i>
        </button>
        <button class="btn btn-toggle sidenav-toggler">
          <i class="gg-menu-left"></i>
        </button>
      </div>
      <button class="topbar-toggler more">
        <i class="gg-more-vertical-alt"></i>
      </button>
    </div>
  </div>
  <div class="sidebar-wrapper scrollbar scrollbar-inner">
    <div class="sidebar-content">
      <ul class="nav nav-secondary">
        <li class="nav-item {{ $isActive(['index']) }}">
          <a href="{{ url('/teacher/index.php') }}"><i class="fas fa-home"></i><p>Dashboard</p></a>
        </li>
        <li class="nav-item {{ $isActive(['students']) }}">
          <a href="{{ url('/teacher/students.php') }}"><i class="fas fa-user-graduate"></i><p>My Students</p></a>
        </li>
        <li class="nav-item {{ $isActive(['attendance']) }}">
          <a href="{{ url('/teacher/attendance.php') }}"><i class="fas fa-calendar-check"></i><p>Attendance</p></a>
        </li>
        <li class="nav-item {{ $isActive(['grades']) }}">
          <a href="{{ url('/teacher/grades.php') }}"><i class="fas fa-book-open"></i><p>Scores & Grades</p></a>
        </li>
        <li class="nav-item {{ $isActive(['announcements']) }}">
          <a href="{{ url('/teacher/announcements.php') }}"><i class="fas fa-bullhorn"></i><p>Announcements</p></a>
        </li>
        <li class="nav-item {{ $isActive(['account']) }}">
          <a href="{{ url('/teacher/account.php') }}"><i class="fas fa-user-cog"></i><p>Account</p></a>
        </li>
        <li class="nav-item">
          <a href="{{ url('/teacher/logout.php') }}"><i class="fas fa-sign-out-alt"></i><p>Logout</p></a>
        </li>
      </ul>
    </div>
  </div>
</div>
