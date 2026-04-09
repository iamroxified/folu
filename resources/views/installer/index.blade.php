<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Folu School Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Install Folu School Management System</h3>
                    </div>
                    <div class="card-body">
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        <form action="{{ route('install') }}" method="POST">
                            @csrf
                            <h5>School Information</h5>
                            <div class="mb-3">
                                <label for="school_name" class="form-label">School Name *</label>
                                <input type="text" class="form-control" id="school_name" name="school_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="school_address" class="form-label">School Address</label>
                                <textarea class="form-control" id="school_address" name="school_address" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="school_phone" class="form-label">School Phone</label>
                                <input type="text" class="form-control" id="school_phone" name="school_phone">
                            </div>
                            <div class="mb-3">
                                <label for="school_email" class="form-label">School Email</label>
                                <input type="email" class="form-control" id="school_email" name="school_email">
                            </div>
                            <h5>Admin User</h5>
                            <div class="mb-3">
                                <label for="admin_username" class="form-label">Admin Username *</label>
                                <input type="text" class="form-control" id="admin_username" name="admin_username" required>
                            </div>
                            <div class="mb-3">
                                <label for="admin_email" class="form-label">Admin Email *</label>
                                <input type="email" class="form-control" id="admin_email" name="admin_email" required>
                            </div>
                            <div class="mb-3">
                                <label for="admin_password" class="form-label">Admin Password *</label>
                                <input type="password" class="form-control" id="admin_password" name="admin_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="admin_password_confirmation" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" id="admin_password_confirmation" name="admin_password_confirmation" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Install</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
