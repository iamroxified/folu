<?php
define('DEVELOPMENT_MODE', false); // Set to true to enable debug info
session_start();
require_once('../db/config.php');
require_once('../db/functions.php');

if (isset($_SESSION['adid'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$debug = ''; // For debugging
$error_type = '';
$login_attempts = 0;
$max_attempts = 5;
$lockout_time = 15 * 60; // 15 minutes in seconds

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['userid']);
    $password = trim($_POST['pass']);
    $remember_me = isset($_POST['remember_me']);
    
    // Validate input data
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all required fields.';
        $debug = 'Username or password field is empty.';
        $error_type = 'validation_error';
    } else {
        // Process the authentication
        $result = authenticateUser($username, $password, $remember_me);
        
        if ($result['success']) {
            // Handle remember me functionality
            if ($remember_me) {
                // Set remember me cookie for 30 days
                $cookie_name = 'remember_me_' . md5($username);
                $cookie_value = base64_encode($username . '|' . time());
                setcookie($cookie_name, $cookie_value, time() + (30 * 24 * 60 * 60), '/', '', false, true);
            }
            
            // Redirect to dashboard
            header('Location: index.php');
            exit();
        } else {
            $error = $result['message'];
            $debug = $result['debug'] ?? '';
            $error_type = $result['error_type'] ?? 'general_error';
        }
    }
}

function authenticateUser($username, $password, $remember_me = false) {
    global $pdo;
    $debugInfo = '';

    try {
        // First, check if user exists at all
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $debugInfo .= "Admin user lookup attempt for '$username'.\n";
        
        if (!$user) {
            // User doesn't exist
            return [
                'success' => false, 
                'message' => 'User account not found. Please check your username and try again.',
                'debug' => $debugInfo . "User '$username' not found in database.\n",
                'error_type' => 'user_not_found'
            ];
        }
        
        // Check if user account is active
        if ($user['status'] !== 'active') {
            $status = $user['status'] ?? 'unknown';
            $message = '';
            
            switch($status) {
                case 'inactive':
                    $message = 'Your account is currently inactive. Please contact the administrator to reactivate your account.';
                    break;
                case 'suspended':
                    $message = 'Your account has been suspended. Please contact the administrator for assistance.';
                    break;
                case 'pending':
                    $message = 'Your account is still pending approval. Please wait for administrator activation.';
                    break;
                default:
                    $message = "Your account status is '$status'. Please contact the administrator for assistance.";
            }
            
            return [
                'success' => false, 
                'message' => $message,
                'debug' => $debugInfo . "User '$username' has status: '$status'.\n",
                'error_type' => 'account_inactive'
            ];
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            // Log failed login attempt
            $debugInfo .= "Password verification failed for '$username'.\n";
            
            return [
                'success' => false, 
                'message' => 'Invalid password. Please check your password and try again. If you\'ve forgotten your password, use the "Reset Password" link below.',
                'debug' => $debugInfo,
                'error_type' => 'invalid_password'
            ];
        }
        
        // Check if user has proper role/permissions for admin access
        $allowed_roles = ['admin'];
        if (empty($user['role']) || !in_array($user['role'], $allowed_roles)) {
            return [
                'success' => false, 
                'message' => 'Your account does not have administrative privileges. Please contact the administrator.',
                'debug' => $debugInfo . "User '$username' has role '{$user['role']}' but needs admin role.\n",
                'error_type' => 'insufficient_privileges'
            ];
        }
        
        // All checks passed - proceed with login
        session_regenerate_id(true);
        $_SESSION['adid'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['access'] = $user['role']; // Keep for backward compatibility
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Log successful login (optional)
        $debugInfo .= "Successful login for '$username' at " . date('Y-m-d H:i:s') . ".\n";
        
        return [
            'success' => true, 
            'message' => 'Login successful! Redirecting to dashboard...', 
            'user' => $user,
            'debug' => $debugInfo
        ];
        
    } catch (PDOException $e) {
        // Database connection or query error
        $error_msg = 'A system error occurred while processing your login. Please try again in a few moments.';
        
        // In development, you might want to show more details
        if (defined('DEVELOPMENT_MODE') && 'DEVELOPMENT_MODE') {
            $error_msg .= ' Error: ' . $e->getMessage();
        }
        
        return [
            'success' => false, 
            'message' => $error_msg, 
            'debug' => $debugInfo . 'Database error: ' . $e->getMessage(),
            'error_type' => 'system_error'
        ];
    } catch (Exception $e) {
        // General error
        return [
            'success' => false, 
            'message' => 'An unexpected error occurred. Please try again later.',
            'debug' => $debugInfo . 'General error: ' . $e->getMessage(),
            'error_type' => 'general_error'
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>FIMOCOL School Management - Admin Login</title>
    <?php include('nav/links.php'); ?>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid d-flex align-items-center justify-content-center">
            <div class="page-inner" style="margin: auto;">
                <div class="col-md-6 offset-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="card card-profile">
                                <div class="card-header" style="background-image: url('assets/img/blogpost.jpg')">
                                    <div class="profile-picture">
                                        <div class="avatar avatar-xl">
                                            <img src="../images/folu_logo.jpg" alt="..." class="avatar-img rounded-circle" />
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
                                        <form method="POST" action="" class="form-horizontal">
                                            <?php if ($error): ?>
                                                <?php 
                                                // Determine alert type and icon based on error type
                                                $alert_class = 'alert-danger';
                                                $alert_icon = 'fas fa-exclamation-triangle';
                                                $alert_title = 'Login Failed';
                                                
                                                if (isset($error_type)) {
                                                    switch($error_type) {
                                                        case 'user_not_found':
                                                            $alert_icon = 'fas fa-user-slash';
                                                            $alert_title = 'User Not Found';
                                                            break;
                                                        case 'invalid_password':
                                                            $alert_icon = 'fas fa-key';
                                                            $alert_title = 'Invalid Password';
                                                            break;
                                                        case 'account_inactive':
                                                            $alert_class = 'alert-warning';
                                                            $alert_icon = 'fas fa-user-lock';
                                                            $alert_title = 'Account Inactive';
                                                            break;
                                                        case 'insufficient_privileges':
                                                            $alert_class = 'alert-warning';
                                                            $alert_icon = 'fas fa-shield-alt';
                                                            $alert_title = 'Access Denied';
                                                            break;
                                                        case 'system_error':
                                                            $alert_icon = 'fas fa-server';
                                                            $alert_title = 'System Error';
                                                            break;
                                                        case 'validation_error':
                                                            $alert_class = 'alert-warning';
                                                            $alert_icon = 'fas fa-exclamation-circle';
                                                            $alert_title = 'Validation Error';
                                                            break;
                                                        default:
                                                            $alert_icon = 'fas fa-exclamation-triangle';
                                                            $alert_title = 'Login Failed';
                                                    }
                                                }
                                                ?>
                                                <div class="alert <?php echo $alert_class; ?> alert-dismissible fade show" role="alert">
                                                    <div class="d-flex align-items-center">
                                                        <i class="<?php echo $alert_icon; ?> me-2" style="font-size: 1.2em;"></i>
                                                        <div>
                                                            <strong><?php echo $alert_title; ?>:</strong><br>
                                                            <span><?php echo htmlspecialchars($error); ?></span>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                </div>
                                                
                                                <?php if (isset($error_type) && $error_type === 'invalid_password'): ?>
                                                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-lightbulb me-2"></i>
                                                            <div>
                                                                <strong>Helpful Tips:</strong><br>
                                                                <ul class="mb-0 mt-2" style="padding-left: 1.2em;">
                                                                    <li>Check if Caps Lock is enabled</li>
                                                                    <li>Ensure you're using the correct password</li>
                                                                    <li>Try typing your password in a text editor first to verify it</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            
                                            <?php if ($debug && (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE)): ?>
                                                <div class="alert alert-secondary alert-dismissible fade show" role="alert">
                                                    <div class="d-flex align-items-start">
                                                        <i class="fas fa-bug me-2 mt-1"></i>
                                                        <div>
                                                            <strong>Debug Information:</strong><br>
                                                            <pre class="mb-0 mt-2" style="font-size: 0.85em; white-space: pre-wrap;"><?php echo htmlspecialchars($debug); ?></pre>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (isset($success) && $success): ?>
                                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-check-circle me-2"></i>
                                                        <div>
                                                            <strong>Success:</strong><br>
                                                            <span><?php echo htmlspecialchars($success); ?></span>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="row">
                                                <div class="form-group">
                                                    <label for="userid">Username: <span class="text-danger">*</span></label>
                                                    <div class="input-icon">
                                                        <span class="input-icon-addon">
                                                            <i class="fa fa-user"></i>
                                                        </span>
                                                        <input type="text" name="userid" id="userid" required class="form-control" placeholder="Enter your username" value="<?php echo htmlspecialchars($_POST['userid'] ?? ''); ?>" />
                                                    </div>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="pass">Password: <span class="text-danger">*</span></label>
                                                    <div class="input-icon">
                                                        <span class="input-icon-addon">
                                                            <i class="fa fa-lock"></i>
                                                        </span>
                                                        <input type="password" name="pass" id="pass" required class="form-control" placeholder="Enter your password" />
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
                                            <p>Forgot your credentials? <a href="reset_password.php">Reset Password</a></p>
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
    <script src="assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugin/sweetalert/sweetalert.min.js"></script>
</body>
</html>
