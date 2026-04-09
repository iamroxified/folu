<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - {{ $schoolSettings ? $schoolSettings->school_name : 'School Management' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                {{ $schoolSettings ? $schoolSettings->school_name : 'School Management' }} - Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.students') }}">Students</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.students.promotions') }}">Promotions</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.classes') }}">Classes</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.classes.timetable') }}">Timetable</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.classes.assignments') }}">Assignments</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.fees') }}">Fees</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.announcements') }}">Announcements</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.settings') }}">Settings</a></li>
                </ul>
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="{{ route('logout') }}">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
