<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no" />
    <title>@yield('title', 'Student Portal') - {{ $schoolSettings ? $schoolSettings->school_name : 'Folu International Schools' }}</title>
    @include('admin.partials.links')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --brand-primary: #720922;
            --brand-secondary: #8e1532;
            --brand-dark: #4a0616;
            --brand-gradient: linear-gradient(135deg, #720922 0%, #8e1532 50%, #4a0616 100%);
            --brand-gold: #f59e0b;
            --card-radius: 14px;
            --card-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
        }

        body {
            background-color: #f5f7fb;
            font-family: 'Public Sans', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #2b3445;
        }

        .main-panel {
            background-color: #f5f7fb;
        }

        .card {
            border-radius: var(--card-radius);
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: var(--card-shadow);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            padding: 1.1rem 1.4rem;
        }

        .card-body {
            padding: 1.4rem;
        }

        /* Hero Banner */
        .student-hero-banner {
            background: var(--brand-gradient);
            color: #ffffff;
            border-radius: var(--card-radius);
            padding: 30px;
            box-shadow: 0 10px 25px rgba(114, 9, 34, 0.25);
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
        }

        .student-hero-banner::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -10%;
            width: 320px;
            height: 320px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
            pointer-events: none;
        }

        .student-hero-banner::after {
            content: '';
            position: absolute;
            bottom: -30%;
            right: 20%;
            width: 200px;
            height: 200px;
            background: rgba(245, 158, 11, 0.1);
            border-radius: 50%;
            pointer-events: none;
        }

        /* KPI Cards */
        .kpi-card {
            border-radius: var(--card-radius);
            border: none;
            overflow: hidden;
            position: relative;
            transition: all 0.25s ease;
        }

        .kpi-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.09);
        }

        .kpi-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        /* Badges & Tables */
        .badge {
            font-weight: 600;
            padding: 0.35em 0.65em;
        }

        .table > :not(caption) > * > * {
            padding: 0.85rem 0.95rem;
        }

        /* Quick actions list */
        .quick-action-link {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border-radius: 10px;
            background-color: #f8fafc;
            color: #334155;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.2s ease;
            margin-bottom: 8px;
            border: 1px solid #e2e8f0;
        }

        .quick-action-link:hover {
            background-color: #ffffff;
            color: var(--brand-primary);
            border-color: var(--brand-primary);
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(114, 9, 34, 0.08);
        }

        .quick-action-link i {
            width: 24px;
            font-size: 16px;
            margin-right: 10px;
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="wrapper">
        @include('student.partials.sidebar')

        <div class="main-panel">
            @include('student.partials.header')

            <div class="container">
                <div class="page-inner py-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 10px;">
                            <i class="fas fa-check-circle me-2 fs-5 align-middle"></i>
                            <strong>Success!</strong> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 10px;">
                            <i class="fas fa-exclamation-triangle me-2 fs-5 align-middle"></i>
                            <strong>Error:</strong> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 10px;">
                            <i class="fas fa-info-circle me-2 fs-5 align-middle"></i>
                            <strong>Notice:</strong> {{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>

            <footer class="footer bg-white border-top py-3 mt-auto">
                <div class="container-fluid d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                    <div class="small text-muted">
                        &copy; {{ date('Y') }} <strong>{{ $schoolSettings->school_name ?? 'Folu International Schools' }}</strong>. Student Portal Hub.
                    </div>
                    <div class="small text-muted d-flex gap-3">
                        <a href="{{ route('student.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a>
                        <a href="{{ route('student.results') }}" class="text-decoration-none text-muted">Results</a>
                        <a href="{{ route('student.payments') }}" class="text-decoration-none text-muted">Fees</a>
                        <a href="{{ route('student.complaints') }}" class="text-decoration-none text-muted">Helpdesk</a>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Core Scripts -->
    <script src="/admin/assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="/admin/assets/js/core/popper.min.js"></script>
    <script src="/admin/assets/js/core/bootstrap.min.js"></script>
    <script src="/admin/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="/admin/assets/js/kaiadmin.min.js"></script>
    @yield('scripts')
</body>
</html>