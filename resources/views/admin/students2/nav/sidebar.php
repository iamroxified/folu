<?php
// Get current page name
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Function to check if current page matches the link
function isActive($page, $currentPage) {
    return ($page === $currentPage) ? 'active' : '';
}

// Function to check if current page is in submenu
function isSubmenuActive($pages, $currentPage) {
    return in_array($currentPage, $pages) ? 'show' : '';
}

// Function to check if parent menu should be active
function isParentActive($pages, $currentPage) {
    return in_array($currentPage, $pages) ? 'active' : '';
}
?>
    <div class="sidebar" data-background-color="dark">
      <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
          <a href="index.html" class="logo">
            <img src="assets/img/logo_dark.png" alt="navbar brand" class="navbar-brand"/>
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
        <!-- End Logo Header -->
      </div>
      <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
          <ul class="nav nav-secondary">
            <li class="nav-item <?php echo isActive('dashboard', $currentPage); ?>">
              <a href="dashboard"> <i class="fas fa-home"></i>
                <p>Dashboard</p>
              </a>
            </li>
            <li class="nav-item <?php echo isParentActive(['students', 'add', 'list', 'edit', 'view', 'delete', 'import_export'], $currentPage); ?>">
              <a data-bs-toggle="collapse" href="#students">
                <i class="fas fa-user-graduate"></i>
                <p>Students</p>
                <span class="caret"></span>
              </a>
              <div class="collapse <?php echo isSubmenuActive(['students', 'add', 'list', 'edit', 'view', 'delete', 'import_export'], $currentPage); ?>" id="students">
                <ul class="nav nav-collapse">
                  <li>
                    <a href="students">Dashboard</a>
                  </li>
                  <li>
                    <a href="modules/students/list.php">All Students</a>
                  </li>
                  <li>
                    <a href="modules/students/add.php">Add Student</a>
                  </li>
                  <li>
                    <a href="modules/students/import_export.php">Import/Export</a>
                  </li>
                </ul>
              </div>
            </li>
            <li class="nav-item <?php echo isActive('teachers', $currentPage); ?>">
              <a href="teachers"> <i class="fas fa-chalkboard-teacher"></i>
                <p>Teachers</p>
              </a>
            </li>
            <li class="nav-item <?php echo isActive('classes', $currentPage); ?>">
              <a href="classes"> <i class="fas fa-school"></i>
                <p>Classes</p>
              </a>
            </li>
            <li class="nav-item <?php echo isActive('subjects', $currentPage); ?>">
              <a href="subjects"> <i class="fas fa-book"></i>
                <p>Subjects</p>
              </a>
            </li>
            <li class="nav-item <?php echo isActive('attendance', $currentPage); ?>">
              <a href="attendance"> <i class="fas fa-calendar-check"></i>
                <p>Attendance</p>
              </a>
            </li>
            <li class="nav-item <?php echo isActive('grades', $currentPage); ?>">
              <a href="grades"> <i class="fas fa-clipboard"></i>
                <p>Grades</p>
              </a>
            </li>
            <li class="nav-item <?php echo isActive('fees', $currentPage); ?>">
              <a href="fees"> <i class="fas fa-dollar-sign"></i>
                <p>Fees</p>
              </a>
            </li>
            <li class="nav-item <?php echo isActive('timetables', $currentPage); ?>">
              <a href="timetables"> <i class="fas fa-calendar"></i>
                <p>Timetables</p>
              </a>
            </li>
            <li class="nav-item <?php echo isActive('reports', $currentPage); ?>">
              <a href="reports"> <i class="fas fa-chart-bar"></i>
                <p>Reports</p>
              </a>
            </li>
            <li class="nav-item <?php echo isActive('notifications', $currentPage); ?>">
              <a href="notifications"> <i class="fas fa-bell"></i>
                <p>Notifications</p>
              </a>
            </li>
            <li class="nav-item <?php echo isActive('settings', $currentPage); ?>">
              <a href="settings"> <i class="fas fa-cogs"></i>
                <p>Settings</p>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>