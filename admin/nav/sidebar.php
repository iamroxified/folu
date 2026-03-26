<?php
// Get current page name
$currentPage = basename($_SERVER['PHP_SELF'], '');

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
          <a href="index" class="logo text-white">
            <img src="assets/img/folu-banner-white.png" alt="navbar brand" width="50%" class="navbar-brand img"/>
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
            <li class="nav-item <?php echo isActive('index', $currentPage); ?>">
              <a href="index"> <i class="fas fa-home"></i>
                <p>Dashboard</p>
              </a>
            </li>
            <li class="nav-item <?php echo isParentActive(['students', 'add_students', 'list_students', 'edit_students', 'view_students', 'delete_students', 'import_export_students'], $currentPage); ?>">
              <a data-bs-toggle="collapse" href="#students">
                <i class="fas fa-user-graduate"></i>
                <p>Students</p>
                <span class="caret"></span>
              </a>
              <div class="collapse <?php echo isSubmenuActive(['students', 'add_students', 'list_students', 'edit', 'view', 'delete_students', 'import_export_students'], $currentPage); ?>" id="students">
                <ul class="nav nav-collapse">
                  <li>
                    <a href="students">Overview</a>
                  </li>
                  <li>
                    <a href="list_students">All Students</a>
                  </li>
                  <li>
                    <a href="add_students">Add Student</a>
                  </li>
                  <li>
                    <a href="import_export_students">Import/Export</a>
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
    
               <li class="nav-item <?php echo isParentActive(['fees', 'add_fees','add_fee_type,fee_type'], $currentPage); ?>">
              <a data-bs-toggle="collapse" href="#fees">
                <i class="fas fa-dollar-sign"></i>
                <p>Fees</p>
                <span class="caret"></span>
              </a>
              <div class="collapse <?php echo isSubmenuActive(['fees', 'add_fees','add_fee_type,fee_type'], $currentPage); ?>" id="fees">
                <ul class="nav nav-collapse">
                  <li>
                    <a href="fees">All Fees</a>
                  </li>
        
                  <li>
                    <a href="add_fees">Add Fees</a>
                  </li>
                  <li>
                    <a href="add_fee_type">Add Fee Type</a>
                  </li>
                     <li>
                    <a href="fee_type">Fee Types</a>
                  </li>
         
                </ul>
              </div>
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