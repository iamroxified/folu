@php
    $adminPath = ltrim(preg_replace('/^admin\/?/i', '', request()->path()), '/');
    $adminPath = preg_replace('/\.(html|php)$/i', '', $adminPath) ?: 'index';

    $matchesPath = function (array $patterns) use ($adminPath): bool {
        foreach ($patterns as $pattern) {
            $pattern = trim($pattern, '/');

            if ($adminPath === $pattern || str_starts_with($adminPath, $pattern . '/')) {
                return true;
            }
        }

        return false;
    };

    $isActive = fn (array $patterns): string => $matchesPath($patterns) ? 'active' : '';
    $isSubmenuActive = fn (array $patterns): string => $matchesPath($patterns) ? 'show' : '';
@endphp
    <div class="sidebar" data-background-color="dark">
      <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
          <a href="{{ url('/admin/index.php') }}" class="logo text-white">
            <img src="{{ $schoolSettings->school_logo ? asset('storage/' . $schoolSettings->school_logo) : '/admin/assets/img/folu-banner-white.png' }}" alt="navbar brand" width="50%" class="navbar-brand img"/>
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
            <li class="nav-item {{ $isActive(['index', 'dashboard', 'dashboard/index']) }}">
              <a href="{{ url('/admin/index.php') }}"> <i class="fas fa-home"></i>
                <p>Dashboard</p>
              </a>
            </li>
            <li class="nav-item {{ $isActive(['students', 'list_students', 'add_students', 'edit_students', 'view_students', 'delete_students', 'import_export_students', 'fetch_states', 'fetch_lgas', 'students/index', 'students/list', 'students/add', 'students/edit', 'students/view', 'students/delete', 'students/import-export', 'students/ajax']) }}">
              <a data-bs-toggle="collapse" href="#students">
                <i class="fas fa-user-graduate"></i>
                <p>Students</p>
                <span class="caret"></span>
              </a>
              <div class="collapse {{ $isSubmenuActive(['students', 'list_students', 'add_students', 'edit_students', 'view_students', 'delete_students', 'import_export_students', 'fetch_states', 'fetch_lgas', 'students/index', 'students/list', 'students/add', 'students/edit', 'students/view', 'students/delete', 'students/import-export', 'students/ajax']) }}" id="students">
                <ul class="nav nav-collapse">
                  <li>
                    <a href="{{ url('/admin/students.php') }}">Overview</a>
                  </li>
                  <li>
                    <a href="{{ url('/admin/list_students.php') }}">All Students</a>
                  </li>
                  <li>
                    <a href="{{ url('/admin/add_students.php') }}">Add Student</a>
                  </li>
                  <li>
                    <a href="{{ url('/admin/import_export_students.php') }}">Import/Export</a>
                  </li>
                </ul>
              </div>
            </li>
            <li class="nav-item {{ $isActive(['teachers', 'teachers/index', 'teachers/list', 'teachers/add', 'teachers/edit', 'teachers/view', 'teachers/assign_subjects']) }}">
              <a data-bs-toggle="collapse" href="#teachersSubmenu">
                <i class="fas fa-chalkboard-teacher"></i>
                <p>Teachers</p>
                <span class="caret"></span>
              </a>
              <div class="collapse {{ $isSubmenuActive(['teachers', 'teachers/index', 'teachers/list', 'teachers/add', 'teachers/edit', 'teachers/view', 'teachers/assign_subjects']) }}" id="teachersSubmenu">
                <ul class="nav nav-collapse">
                  <li>
                    <a href="{{ url('/admin/teachers.php') }}">Overview</a>
                  </li>
                  <li>
                    <a href="{{ url('/admin/teachers/list.php') }}">All Teachers</a>
                  </li>
                  <li>
                    <a href="{{ url('/admin/teachers/add.php') }}">Add Teacher</a>
                  </li>
                  <li>
                    <a href="{{ url('/admin/teachers/assign_subjects.php') }}">Assign Subjects</a>
                  </li>
                </ul>
              </div>
            </li>
            <li class="nav-item {{ $isActive(['classes/manage', 'classes/assign_students', 'classes/timetable']) }}">
              <a href="{{ url('/admin/classes/manage.php') }}"> <i class="fas fa-school"></i>
                <p>Classes</p>
              </a>
            </li>
            <li class="nav-item {{ $isActive(['subjects', 'subjects/index', 'subjects/manage', 'subjects/assign']) }}">
              <a href="{{ url('/admin/subjects.php') }}"> <i class="fas fa-book"></i>
                <p>Subjects</p>
              </a>
            </li>
            <li class="nav-item {{ $isActive(['attendance', 'attendance/index', 'attendance/mark', 'attendance/report', 'attendance/view']) }}">
              <a href="{{ url('/admin/attendance.php') }}"> <i class="fas fa-calendar-check"></i>
                <p>Attendance</p>
              </a>
            </li>
            <li class="nav-item {{ $isActive(['grades', 'grades/index']) }}">
              <a href="{{ url('/admin/grades.php') }}"> <i class="fas fa-clipboard"></i>
                <p>Grades</p>
              </a>
            </li>
            <li class="nav-item {{ $isActive(['sessions', 'sessions/index']) }}">
              <a href="{{ url('/admin/sessions.php') }}"> <i class="fas fa-calendar-alt"></i>
                <p>Sessions</p>
              </a>
            </li>
    
               <li class="nav-item {{ $isActive(['fees', 'add_fees', 'fee_structure', 'all_payments', 'print_receipt', 'fee_type', 'add_fee_type', 'edit_fee_type', 'fees/index', 'fees/add', 'fees/structure', 'fees/payments', 'fees/receipt', 'fees/types']) }}">
              <a data-bs-toggle="collapse" href="#fees">
                <i class="fas fa-dollar-sign"></i>
                <p>Fees</p>
                <span class="caret"></span>
              </a>
              <div class="collapse {{ $isSubmenuActive(['fees', 'add_fees', 'fee_structure', 'all_payments', 'print_receipt', 'fee_type', 'add_fee_type', 'edit_fee_type', 'fees/index', 'fees/add', 'fees/structure', 'fees/payments', 'fees/receipt', 'fees/types']) }}" id="fees">
                <ul class="nav nav-collapse">
                  <li>
                    <a href="{{ url('/admin/fees.php') }}">All Fees</a>
                  </li>
                  <li>
                    <a href="{{ url('/admin/fees/payments.php') }}">Student Payments</a>
                  </li>
                  <li>
                    <a href="{{ url('/admin/add_fees.php') }}">Add Fees</a>
                  </li>
                  <li>
                    <a href="{{ url('/admin/add_fee_type.php') }}">Add Fee Type</a>
                  </li>
                     <li>
                    <a href="{{ url('/admin/fee_type.php') }}">Fee Types</a>
                  </li>
         
                </ul>
              </div>
            </li>
            <li class="nav-item {{ $isActive(['classes/timetable']) }}">
              <a href="{{ url('/admin/classes/timetable.php') }}"> <i class="fas fa-calendar"></i>
                <p>Timetables</p>
              </a>
            </li>
            <li class="nav-item {{ $isActive(['payroll', 'payroll/create', 'payroll/index', 'payroll/edit', 'payroll/show']) }}">
              <a href="{{ route('admin.payroll') }}"> <i class="fas fa-file-invoice-dollar"></i>
                <p>Staff Payroll</p>
              </a>
            </li>
            <li class="nav-item {{ $isActive(['expenditures', 'expenditures/create', 'expenditures/index', 'expenditures/edit', 'expenditures/show']) }}">
              <a href="{{ route('admin.expenditures') }}"> <i class="fas fa-receipt"></i>
                <p>Expenditures</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="javascript:void(0)"> <i class="fas fa-chart-bar"></i>
                <p>Reports</p>
              </a>
            </li>
            <li class="nav-item {{ $isActive(['users', 'users/list', 'users/add', 'users/edit', 'users/roles']) }}">
              <a data-bs-toggle="collapse" href="#usersSubmenu">
                <i class="fas fa-users-cog"></i>
                <p>User Management</p>
                <span class="caret"></span>
              </a>
              <div class="collapse {{ $isSubmenuActive(['users', 'users/list', 'users/add', 'users/edit', 'users/roles']) }}" id="usersSubmenu">
                <ul class="nav nav-collapse">
                  <li>
                    <a href="{{ url('/admin/users/list.php') }}">All Users</a>
                  </li>
                  <li>
                    <a href="{{ url('/admin/users/add.php') }}">Add User</a>
                  </li>
                  <li>
                    <a href="{{ url('/admin/users/roles.php') }}">Roles & Permissions</a>
                  </li>
                </ul>
              </div>
            </li>
            <li class="nav-item {{ $isActive(['blog', 'blog/list', 'blog/add', 'blog/edit']) }}">
              <a data-bs-toggle="collapse" href="#blogSubmenu">
                <i class="fas fa-newspaper"></i>
                <p>Blog Management</p>
                <span class="caret"></span>
              </a>
              <div class="collapse {{ $isSubmenuActive(['blog', 'blog/list', 'blog/add', 'blog/edit']) }}" id="blogSubmenu">
                <ul class="nav nav-collapse">
                  <li>
                    <a href="{{ url('/admin/blog/list.php') }}">All Blog Posts</a>
                  </li>
                  <li>
                    <a href="{{ url('/admin/blog/add.php') }}">Add New Post</a>
                  </li>
                </ul>
              </div>
            </li>
            <li class="nav-item {{ $isActive(['announcements', 'announcements/index']) }}">
              <a href="{{ url('/admin/announcements.php') }}"> <i class="fas fa-bell"></i>
                <p>Announcements</p>
              </a>
            </li>
            <li class="nav-item {{ $isActive(['settings', 'settings/index']) }}">
              <a href="{{ route('admin.settings') }}"> <i class="fas fa-cogs"></i>
                <p>Settings</p>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>

