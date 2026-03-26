<?php
/**
 * Session Management System for FOLU School Management
 * Handles user authentication, session security, and role-based access control
 */

// Secure session configuration
if (session_status() === PHP_SESSION_NONE) {
    // Configure secure session settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    // Set session name
    session_name('FOLU_ADMIN_SESSION');
    
    // Start session
    session_start();
}

// Regenerate session ID periodically for security
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Include database configuration
require_once(__DIR__ . '/../db/config.php');
require_once(__DIR__ . '/../db/functions.php');

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']) && isset($_SESSION['admin_access']);
}

/**
 * Check if user has specific access level
 */
function hasAccess($required_access = 'admin') {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user_access = $_SESSION['admin_access'];
    
    // Super admin has access to everything
    if ($user_access === 'super_admin') {
        return true;
    }
    
    // Regular admin only has admin access
    if ($user_access === 'admin' && $required_access === 'admin') {
        return true;
    }
    
    return false;
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Redirect to login if access level insufficient
 */
function requireAccess($required_access = 'admin') {
    if (!hasAccess($required_access)) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Get current admin user data
 */
function getCurrentAdmin() {
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM adminuser WHERE username = ? AND status = 'active'");
        $stmt->execute([$_SESSION['admin_username']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching admin data: " . $e->getMessage());
        return null;
    }
}

/**
 * Login user with credentials
 */
function loginUser($username, $password, $remember_me = false) {
    global $pdo;
    
    try {
        // Get user from database
        $stmt = $pdo->prepare("SELECT * FROM adminuser WHERE username = ? AND status = 'active'");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_access'] = $user['access'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_name'] = $user['afname'] . ' ' . $user['lname'];
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();
            
            // Handle "Remember Me" functionality
            if ($remember_me) {
                $token = bin2hex(random_bytes(32));
                $expiry = time() + (30 * 24 * 60 * 60); // 30 days
                
                // Store remember token in database
                $stmt = $pdo->prepare("UPDATE adminuser SET remember_token = ?, remember_expires = ? WHERE id = ?");
                $stmt->execute([$token, date('Y-m-d H:i:s', $expiry), $user['id']]);
                
                // Set cookie
                setcookie('folu_remember', $token, $expiry, '/', '', true, true);
            }
            
            // Log successful login
            logActivity($user['id'], 'login', 'Successful login from IP: ' . $_SERVER['REMOTE_ADDR']);
            
            return ['success' => true, 'message' => 'Login successful'];
        } else {
            // Log failed login attempt
            if ($user) {
                logActivity($user['id'], 'login_failed', 'Failed login attempt from IP: ' . $_SERVER['REMOTE_ADDR']);
            }
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Login system error'];
    }
}

/**
 * Logout user
 */
function logoutUser() {
    if (isLoggedIn()) {
        // Log logout activity
        logActivity($_SESSION['admin_id'], 'logout', 'User logged out');
        
        // Clear remember me cookie and database token
        if (isset($_COOKIE['folu_remember'])) {
            global $pdo;
            try {
                $stmt = $pdo->prepare("UPDATE adminuser SET remember_token = NULL, remember_expires = NULL WHERE id = ?");
                $stmt->execute([$_SESSION['admin_id']]);
            } catch (PDOException $e) {
                error_log("Error clearing remember token: " . $e->getMessage());
            }
            
            // Clear cookie
            setcookie('folu_remember', '', time() - 3600, '/', '', true, true);
        }
    }
    
    // Clear all session variables
    $_SESSION = [];
    
    // Destroy session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
}

/**
 * Check remember me cookie and auto-login
 */
function checkRememberMe() {
    if (!isLoggedIn() && isset($_COOKIE['folu_remember'])) {
        global $pdo;
        
        try {
            $token = $_COOKIE['folu_remember'];
            $stmt = $pdo->prepare("SELECT * FROM adminuser WHERE remember_token = ? AND remember_expires > NOW() AND status = 'active'");
            $stmt->execute([$token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Auto login user
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_access'] = $user['access'];
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['admin_name'] = $user['afname'] . ' ' . $user['lname'];
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();
                
                // Log auto-login
                logActivity($user['id'], 'auto_login', 'Auto-login via remember token from IP: ' . $_SERVER['REMOTE_ADDR']);
                
                return true;
            } else {
                // Invalid token, clear cookie
                setcookie('folu_remember', '', time() - 3600, '/', '', true, true);
            }
        } catch (PDOException $e) {
            error_log("Remember me check error: " . $e->getMessage());
        }
    }
    
    return false;
}

/**
 * Check session timeout
 */
function checkSessionTimeout($timeout = 3600) { // 1 hour default
    if (isLoggedIn()) {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            logoutUser();
            return false;
        }
        $_SESSION['last_activity'] = time();
    }
    return true;
}

/**
 * Log user activity
 */
function logActivity($user_id, $action, $description = '') {
    global $pdo;
    
    try {
        // Create activity log table if it doesn't exist
        $pdo->exec("CREATE TABLE IF NOT EXISTS admin_activity_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            action VARCHAR(50) NOT NULL,
            description TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_action (action),
            INDEX idx_created_at (created_at)
        )");
        
        $stmt = $pdo->prepare("INSERT INTO admin_activity_log (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            $action,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (PDOException $e) {
        error_log("Activity logging error: " . $e->getMessage());
    }
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get user display name
 */
function getAdminDisplayName() {
    if (isLoggedIn()) {
        return $_SESSION['admin_name'] ?? $_SESSION['admin_username'];
    }
    return 'Guest';
}

/**
 * Get user first name
 */
function getAdminFirstName() {
    if (isLoggedIn()) {
        $name_parts = explode(' ', $_SESSION['admin_name'] ?? '');
        return $name_parts[0] ?? $_SESSION['admin_username'];
    }
    return 'Guest';
}

// Auto-check remember me on every page load
checkRememberMe();

// Check session timeout
checkSessionTimeout();
?>
