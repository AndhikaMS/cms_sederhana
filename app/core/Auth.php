<?php
namespace App\Core;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\ActivityLogModel;
use App\Helpers\Functions;

class Auth {
    private static $instance = null;
    private $user = null;
    private $userModel;
    private $roleModel;
    private $activityLogModel;
    
    private function __construct() {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->activityLogModel = new ActivityLogModel();
        
        // Check for remember me token
        if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
            $this->loginWithRememberToken($_COOKIE['remember_token']);
        }
        
        // Load user if logged in
        if (isset($_SESSION['user_id'])) {
            $this->user = $this->userModel->getById($_SESSION['user_id']);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function login($email, $password, $remember = false) {
        $user = $this->userModel->getByEmail($email);
        
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }
        
        if ($user['status'] !== 'active') {
            return false;
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role_slug'];
        
        // Update last login
        $this->userModel->updateLastLogin($user['id']);
        
        // Handle remember me
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
            
            $this->userModel->setRememberToken($user['id'], $token, $expiresAt);
            
            setcookie(
                'remember_token',
                $token,
                strtotime('+30 days'),
                '/',
                '',
                true, // Secure
                true  // HttpOnly
            );
        }
        
        // Log activity
        $this->activityLogModel->create([
            'user_id' => $user['id'],
            'action' => 'login',
            'description' => 'User logged in successfully'
        ]);
        
        $this->user = $user;
        return true;
    }
    
    public function loginWithRememberToken($token) {
        $user = $this->userModel->getByRememberToken($token);
        
        if (!$user) {
            return false;
        }
        
        if ($user['status'] !== 'active') {
            $this->userModel->clearRememberToken($user['id']);
            return false;
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role_slug'];
        
        // Update last login
        $this->userModel->updateLastLogin($user['id']);
        
        // Generate new remember token
        $newToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $this->userModel->setRememberToken($user['id'], $newToken, $expiresAt);
        
        setcookie(
            'remember_token',
            $newToken,
            strtotime('+30 days'),
            '/',
            '',
            true, // Secure
            true  // HttpOnly
        );
        
        // Log activity
        $this->activityLogModel->create([
            'user_id' => $user['id'],
            'action' => 'login',
            'description' => 'User logged in with remember token'
        ]);
        
        $this->user = $user;
        return true;
    }
    
    public function logout() {
        if ($this->isLoggedIn()) {
            // Log activity
            $this->activityLogModel->create([
                'user_id' => $this->user['id'],
                'action' => 'logout',
                'description' => 'User logged out'
            ]);
            
            // Clear remember token
            if (isset($_COOKIE['remember_token'])) {
                $this->userModel->clearRememberToken($this->user['id']);
                setcookie('remember_token', '', time() - 3600, '/', '', true, true);
            }
        }
        
        // Clear session
        session_unset();
        session_destroy();
        
        $this->user = null;
    }
    
    public function isLoggedIn() {
        return $this->user !== null;
    }
    
    public function getUser() {
        return $this->user;
    }
    
    public function getUserId() {
        return $this->user ? $this->user['id'] : null;
    }
    
    public function getUserRole() {
        return $this->user ? $this->user['role_slug'] : null;
    }
    
    public function hasPermission($permission) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        return $this->roleModel->hasPermission($this->user['role_id'], $permission);
    }
    
    public function requirePermission($permission) {
        global $router;
        if (!$this->hasPermission($permission)) {
            // Log unauthorized access attempt
            if ($this->isLoggedIn()) {
                $this->activityLogModel->create([
                    'user_id' => $this->user['id'],
                    'action' => 'unauthorized_access',
                    'description' => "Attempted to access resource requiring permission: {$permission}"
                ]);
            }
            
            // Redirect to error page or show error message
            // We'll redirect to a 403 route if defined, otherwise a generic error.
            $router->redirectTo(Functions::url('403')); // Assuming a '403' route exists
            exit;
        }
    }
    
    public function requireLogin() {
        global $router;
        if (!$this->isLoggedIn()) {
            // Store intended URL for redirect after login
            // We want to store the URL relative to the base_url, so the router can rebuild it correctly.
            // $_SERVER['REQUEST_URI'] can contain the base_url, e.g., /cms_sederhana/login
            // We need to strip the base_url from it before storing.
            $request_uri_stripped = $_SERVER['REQUEST_URI'];
            $baseUrlPath = parse_url(config('app.url'), PHP_URL_PATH);
            if (!empty($baseUrlPath) && strpos($request_uri_stripped, $baseUrlPath) === 0) {
                $request_uri_stripped = substr($request_uri_stripped, strlen($baseUrlPath));
            }
            // Ensure leading slash for routes
            $request_uri_stripped = '/' . ltrim($request_uri_stripped, '/');

            $_SESSION['intended_url'] = $request_uri_stripped;

            // Redirect to login page using router's redirectTo method
            $router->redirectTo(Functions::url('auth/login'));
            exit;
        }
    }
    
    public function requireGuest() {
        global $router;
        if ($this->isLoggedIn()) {
            // Redirect to dashboard using router's redirectTo method
            $router->redirectTo(Functions::url('dashboard'));
            exit;
        }
    }
    
    public function validatePassword($password) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        return $this->userModel->validatePassword($this->user['id'], $password);
    }
    
    public function changePassword($currentPassword, $newPassword) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        if (!$this->validatePassword($currentPassword)) {
            return false;
        }
        
        $success = $this->userModel->updatePassword($this->user['id'], $newPassword);
        
        if ($success) {
            // Log activity
            $this->activityLogModel->create([
                'user_id' => $this->user['id'],
                'action' => 'change_password',
                'description' => 'User changed their password'
            ]);
        }
        
        return $success;
    }
    
    public function updateProfile($data) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $success = $this->userModel->update($this->user['id'], $data);
        
        if ($success) {
            // Log activity
            $this->activityLogModel->create([
                'user_id' => $this->user['id'],
                'action' => 'profile_update',
                'description' => 'User updated their profile'
            ]);
        }
        
        return $success;
    }

    public function updateAvatar($avatar) {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $success = $this->userModel->updateAvatar($this->user['id'], $avatar);

        if ($success) {
            // Log activity
            $this->activityLogModel->create([
                'user_id' => $this->user['id'],
                'action' => 'avatar_update',
                'description' => 'User updated their avatar'
            ]);
        }

        return $success;
    }

    public function register($data) {
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Set default role
        if (!isset($data['role_id'])) {
            $roleModel = new RoleModel();
            $defaultRole = $roleModel->getBySlug('user');
            $data['role_id'] = $defaultRole['id'] ?? 2; // Default to author if 'user' role not found
        }
        
        $userId = $this->userModel->create($data);
        
        if ($userId) {
            // Log activity
            $this->activityLogModel->create([
                'user_id' => $userId,
                'action' => 'register',
                'description' => 'New user registered'
            ]);
            
            // Generate email verification token
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 day'));
            $this->userModel->setVerificationToken($userId, $token, $expiresAt);

            // Send verification email
            // For now, let's just log the link
            error_log("Email verification link for user {$userId}: " . Functions::url('auth/verify-email', ['token' => $token]));

            return true;
        }
        return false;
    }

    public function forgotPassword($email) {
        $user = $this->userModel->getByEmail($email);
        
        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $this->userModel->setPasswordResetToken($user['id'], $token, $expiresAt);
            
            // Send reset email
            // For now, let's just log the link
            error_log("Password reset link for user {$user['id']}: " . Functions::url('auth/reset-password', ['token' => $token]));

            return true;
        }
        return false;
    }

    public function resetPassword($token, $newPassword) {
        $user = $this->userModel->getByPasswordResetToken($token);
        
        if (!$user) {
            return false;
        }
        
        // Update password
        $success = $this->userModel->updatePassword($user['id'], $newPassword);
        
        if ($success) {
            // Clear reset token
            $this->userModel->clearPasswordResetToken($user['id']);
            
            // Log activity
            $this->activityLogModel->create([
                'user_id' => $user['id'],
                'action' => 'password_reset',
                'description' => 'User reset their password'
            ]);
            return true;
        }
        return false;
    }

    public function verifyEmail($token) {
        $user = $this->userModel->getByVerificationToken($token);

        if (!$user) {
            return false;
        }

        // Mark email as verified
        $success = $this->userModel->markEmailAsVerified($user['id']);

        if ($success) {
            // Clear verification token
            $this->userModel->clearVerificationToken($user['id']);
            
            // Log activity
            $this->activityLogModel->create([
                'user_id' => $user['id'],
                'action' => 'email_verified',
                'description' => 'User email verified'
            ]);
            return true;
        }
        return false;
    }

    public function isEmailVerified() {
        return $this->user ? $this->user['email_verified_at'] !== null : false;
    }

    public function requireEmailVerification() {
        global $router;
        if ($this->isLoggedIn() && !$this->isEmailVerified()) {
            // Redirect to email verification notice page
            $router->redirectTo(Functions::url('auth/verify-email-notice'));
            exit;
        }
    }

    public function getIntendedUrl() {
        $url = $_SESSION['intended_url'] ?? Functions::url('dashboard');
        unset($_SESSION['intended_url']);

        // If the stored URL doesn't start with base_url, then Functions::url() will add it.
        // If it starts with base_url, then we need to strip it before passing it to Functions::url()
        // Or simply, if it's already a full URL, return it as is.
        // Given that we now store it *without* the base_url in requireLogin(),
        // Functions::url() should be able to handle it directly.
        // However, if the fallback `Functions::url('dashboard')` is used, it's fine.

        // The key is to ensure that $url passed to Functions::url() is always a route (e.g., /dashboard, /auth/login)
        // not a full URL with cms_sederhana already prepended.

        return Functions::url($url);
    }
}