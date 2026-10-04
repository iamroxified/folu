@extends('admin.layout')

@section('title', 'Admin Profile & Settings')

@section('content')
<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pb-3">
        <h2 class="text-dark pb-2 fw-bold"><i class="fas fa-user-shield me-2 text-primary"></i>Admin Profile & Account Settings</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Admin Profile Info Card -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm text-center">
                <div class="card-body py-4">
                    <div class="mb-3">
                        <div class="avatar-xl d-inline-block rounded-circle bg-primary text-white p-3 shadow-sm mb-2" style="width: 100px; height: 100px; line-height: 70px;">
                            <i class="fas fa-user-cog fa-4x"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-1">{{ $user ? $user->name : 'Administrator' }}</h4>
                    <p class="text-muted small mb-2"><i class="fas fa-envelope me-1"></i> {{ $user ? $user->email : 'N/A' }}</p>
                    <span class="badge bg-success px-3 py-2 fs-6">System Administrator</span>

                    <hr class="my-3">
                    <div class="text-start small text-muted">
                        <p class="mb-1"><strong>Username:</strong> <code>{{ $user ? $user->username : 'admin' }}</code></p>
                        <p class="mb-1"><strong>Account Created:</strong> {{ $user && $user->created_at ? $user->created_at->format('d M, Y') : 'N/A' }}</p>
                        <p class="mb-0"><strong>Role ID:</strong> {{ $user ? $user->role_id : 1 }}</p>
                    </div>

                    <div class="d-grid mt-4">
                        <a href="{{ route('logout') }}" class="btn btn-outline-danger font-weight-bold">
                            <i class="fas fa-sign-out-alt me-1"></i> Log Out
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Forms: Update Profile Details & Security -->
        <div class="col-lg-8 mb-4">
            <!-- Update Profile Form -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title fw-bold mb-0"><i class="fas fa-user-edit me-2 text-primary"></i>Update Profile Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.profile.update') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="name" value="{{ old('name', $user ? $user->name : '') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label fw-bold">Username</label>
                                <input type="text" class="form-control bg-light" id="username" value="{{ $user ? $user->username : '' }}" readonly disabled>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" id="email" value="{{ old('email', $user ? $user->email : '') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label fw-bold">Phone Number</label>
                                <input type="text" class="form-control" name="phone" id="phone" value="{{ old('phone', $user ? ($user->phone ?? '') : '') }}" placeholder="e.g. +234 800 000 0000">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-save me-1"></i> Save Profile Changes</button>
                    </form>
                </div>
            </div>

            <!-- Change Password Form -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title fw-bold mb-0"><i class="fas fa-lock me-2 text-warning"></i>Security & Password</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.profile.password') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-bold">Current Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="current_password" id="current_password" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label fw-bold">New Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password" id="password" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label fw-bold">Confirm New Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning fw-bold text-dark"><i class="fas fa-key me-1"></i> Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
