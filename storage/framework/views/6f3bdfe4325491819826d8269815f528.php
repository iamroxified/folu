<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>FIMOCOL School Management - Admin Login</title>
    <?php echo $__env->make('admin.partials.links', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid d-flex align-items-center justify-content-center">
            <div class="page-inner" style="margin: auto;">
                <div class="col-md-6 offset-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="card card-profile">
                                <div class="card-header" style="background-image: url('/admin/assets/img/blogpost.jpg')">
                                    <div class="profile-picture">
                                        <div class="avatar avatar-xl">
                                            <img src="/images/folu_logo.jpg" alt="..." class="avatar-img rounded-circle" />
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div>
                                        <h5>Welcome to</h5>
                                        <h3>FIMOCOL School Management System</h3>
                                        <hr>
                                        <p class="text-center">Login to Access Administrative Dashboard</p>
                                        <p class="text-center"><small>Admin access required</small></p>
                                    </div>
                                    <div class="user-profile">
                                        <form method="POST" action="<?php echo e(route('admin.login.submit')); ?>" class="form-horizontal">
                                            <?php echo csrf_field(); ?>

                                            <?php if(session('error')): ?>
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-exclamation-triangle me-2" style="font-size: 1.2em;"></i>
                                                    <div>
                                                        <strong>Login Failed:</strong><br>
                                                        <span><?php echo e(session('error')); ?></span>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                            <?php endif; ?>

                                            <?php if($errors->any()): ?>
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-exclamation-circle me-2" style="font-size: 1.2em;"></i>
                                                    <div>
                                                        <strong>Validation Error:</strong><br>
                                                        <ul class="mb-0 mt-2" style="padding-left: 1.2em;">
                                                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $validationError): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <li><?php echo e($validationError); ?></li>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                            <?php endif; ?>

                                            <div class="row">
                                                <div class="form-group">
                                                    <label for="username">Username: <span class="text-danger">*</span></label>
                                                    <div class="input-icon">
                                                        <span class="input-icon-addon">
                                                            <i class="fa fa-user"></i>
                                                        </span>
                                                        <input type="text" name="username" id="username" required class="form-control <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Enter your username" value="<?php echo e(old('username')); ?>" />
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="pass">Password: <span class="text-danger">*</span></label>
                                                    <div class="input-icon">
                                                        <span class="input-icon-addon">
                                                            <i class="fa fa-lock"></i>
                                                        </span>
                                                        <input type="password" name="password" id="password" required class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Enter your password" />
                                                    </div>
                                                </div>

                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="remember_me" id="remember_me" />
                                                    <label class="form-check-label" for="remember_me">
                                                        Remember Me (30 days)
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="view-profile">
                                                    <input type="submit" name="login" class="btn btn-primary w-100" value="Login to Dashboard" />
                                                </div>
                                            </div>
                                        </form>

                                        <div class="text-center mt-3">
                                            <p>Forgot your credentials? <a href="<?php echo e(route('password.request')); ?>">Reset Password</a></p>
                                            <hr>
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle"></i>
                                                    Authorized personnel only. Your login attempt will be logged.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include necessary scripts -->
    <script src="/admin/assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="/admin/assets/js/core/bootstrap.min.js"></script>
    <script src="/admin/assets/js/plugin/sweetalert/sweetalert.min.js"></script>
</body>
</html>
<?php /**PATH C:\laragon\www\folu\resources\views/admin/pages/auth/login.blade.php ENDPATH**/ ?>