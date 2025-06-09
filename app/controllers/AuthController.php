<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Core\Auth;

class AuthController extends Controller {
    protected $auth;
    private $userModel;
    private $roleModel;

    public function __construct() {
        parent::__construct();
        $this->auth = Auth::getInstance();
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    /**
     * Show login form
     */
    public function loginForm() {
        // Redirect if already logged in
        $this->auth->requireGuest();
        
        // Set CSRF token
        $csrf_token = $this->generateCsrfToken();
        
        // Include view file directly
        $view_file = dirname(__DIR__) . '/views/auth/login.php';
        if (file_exists($view_file)) {
            require $view_file;
        } else {
            throw new \Exception("View file not found: {$view_file}");
        }
    }

    /**
     * Show register form
     */
    public function registerForm() {
        // Redirect if already logged in
        $this->auth->requireGuest();
        
        $this->view('auth/register', [
            'title' => 'Register',
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function login() {
        // Redirect if already logged in
        $this->auth->requireGuest();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);
            
            // Validate CSRF token
            if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->setFlash('error', 'Invalid request');
                $this->redirect('auth/login');
            }
            
            // Validate input
            $errors = [];
            
            if (empty($email)) {
                $errors['email'] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            }
            
            if (empty($password)) {
                $errors['password'] = 'Password is required';
            }
            
            if (empty($errors)) {
                if ($this->auth->login($email, $password, $remember)) {
                    // Redirect to intended URL or dashboard
                    $intendedUrl = $this->auth->getIntendedUrl();
                    error_log("Auth: Redirecting to intended URL: " . $intendedUrl);
                    $this->redirect($intendedUrl);
                } else {
                    $this->setFlash('error', 'Invalid email or password');
                }
            } else {
                $this->setFlash('errors', $errors);
                $this->setFlash('old', $_POST);
            }
        }
        
        // Show login form
        $this->view('auth/login', [
            'title' => 'Login',
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function register() {
        // Redirect if already logged in
        $this->auth->requireGuest();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $password_confirmation = $_POST['password_confirmation'] ?? '';
            
            // Validate CSRF token
            if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->setFlash('error', 'Invalid request');
                $this->redirect('auth/register');
            }
            
            // Validate input
            $errors = [];
            
            if (empty($name)) {
                $errors['name'] = 'Name is required';
            } elseif (strlen($name) < 3) {
                $errors['name'] = 'Name must be at least 3 characters';
            }
            
            if (empty($email)) {
                $errors['email'] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            } elseif ($this->userModel->getByEmail($email)) {
                $errors['email'] = 'Email already registered';
            }
            
            if (empty($password)) {
                $errors['password'] = 'Password is required';
            } elseif (strlen($password) < 8) {
                $errors['password'] = 'Password must be at least 8 characters';
            }
            
            if ($password !== $password_confirmation) {
                $errors['password_confirmation'] = 'Password confirmation does not match';
            }
            
            if (empty($errors)) {
                // Get default role
                $defaultRole = $this->roleModel->getBySlug('user');
                
                if ($this->auth->register([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'role_id' => $defaultRole['id'],
                    'status' => 'active'
                ])) {
                    $this->setFlash('success', 'Registration successful! Please check your email to verify your account.');
                    $this->redirect('auth/login');
                } else {
                    $this->setFlash('error', 'Registration failed. Please try again.');
                }
            } else {
                $this->setFlash('errors', $errors);
                $this->setFlash('old', $_POST);
            }
        }
        
        // Show registration form
        $this->view('auth/register', [
            'title' => 'Register',
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function logout() {
        $this->auth->logout();
        $this->redirect('auth/login');
    }

    public function forgotPassword() {
        // Redirect if already logged in
        $this->auth->requireGuest();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            
            // Validate CSRF token
            if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->setFlash('error', 'Invalid request');
                $this->redirect('auth/forgot-password');
            }
            
            // Validate input
            $errors = [];
            
            if (empty($email)) {
                $errors['email'] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            }
            
            if (empty($errors)) {
                if ($this->auth->forgotPassword($email)) {
                    $this->setFlash('success', 'Password reset instructions have been sent to your email.');
                    $this->redirect('auth/login');
                } else {
                    // Don't reveal if email exists or not
                    $this->setFlash('success', 'If your email is registered, you will receive password reset instructions.');
                    $this->redirect('auth/login');
                }
            } else {
                $this->setFlash('errors', $errors);
                $this->setFlash('old', $_POST);
            }
        }
        
        // Show forgot password form
        $this->view('auth/forgot-password', [
            'title' => 'Forgot Password',
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function resetPassword($token = null) {
        // Redirect if already logged in
        $this->auth->requireGuest();
        
        if (!$token) {
            $this->redirect('auth/forgot-password');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $password_confirmation = $_POST['password_confirmation'] ?? '';
            
            // Validate CSRF token
            if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->setFlash('error', 'Invalid request');
                $this->redirect("auth/reset-password/$token");
            }
            
            // Validate input
            $errors = [];
            
            if (empty($password)) {
                $errors['password'] = 'Password is required';
            } elseif (strlen($password) < 8) {
                $errors['password'] = 'Password must be at least 8 characters';
            }
            
            if ($password !== $password_confirmation) {
                $errors['password_confirmation'] = 'Password confirmation does not match';
            }
            
            if (empty($errors)) {
                if ($this->auth->resetPassword($token, $password)) {
                    $this->setFlash('success', 'Your password has been reset. You can now login with your new password.');
                    $this->redirect('auth/login');
                } else {
                    $this->setFlash('error', 'Invalid or expired reset token.');
                    $this->redirect('auth/forgot-password');
                }
            } else {
                $this->setFlash('errors', $errors);
            }
        }
        
        // Show reset password form
        $this->view('auth/reset-password', [
            'title' => 'Reset Password',
            'token' => $token,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function verifyEmail($token = null) {
        // Redirect if already logged in
        $this->auth->requireGuest();
        
        if (!$token) {
            $this->redirect('auth/login');
        }
        
        if ($this->auth->verifyEmail($token)) {
            $this->setFlash('success', 'Email verified successfully! You can now log in.');
            $this->redirect('auth/login');
        } else {
            $this->setFlash('error', 'Invalid or expired verification token.');
            $this->redirect('auth/login');
        }
    }

    public function profile() {
        $this->auth->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $newPasswordConfirmation = $_POST['new_password_confirmation'] ?? '';
            
            // Validate CSRF token
            if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->setFlash('error', 'Invalid request');
                $this->redirect('profile');
            }
            
            // Validate input
            $errors = [];

            if (empty($name)) {
                $errors['name'] = 'Name is required';
            } elseif (strlen($name) < 3) {
                $errors['name'] = 'Name must be at least 3 characters';
            }

            if (empty($email)) {
                $errors['email'] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            }
            
            // Check if email already exists for another user
            $existingUser = $this->userModel->getByEmail($email);
            if ($existingUser && $existingUser['id'] !== $this->auth->getUserId()) {
                $errors['email'] = 'Email already registered';
            }
            
            if (!empty($newPassword)) {
                if (strlen($newPassword) < 8) {
                    $errors['new_password'] = 'New password must be at least 8 characters';
                }
                if ($newPassword !== $newPasswordConfirmation) {
                    $errors['new_password_confirmation'] = 'New password confirmation does not match';
                }
                if (!$this->auth->validatePassword($currentPassword)) {
                    $errors['current_password'] = 'Incorrect current password';
                }
            }
            
            if (empty($errors)) {
                $updateData = [
                    'name' => $name,
                    'email' => $email,
                ];

                if (!empty($newPassword)) {
                    $updateData['password'] = $newPassword;
                }
                
                if ($this->auth->updateProfile($updateData)) {
                    $this->setFlash('success', 'Profile updated successfully.');
                    $this->redirect('profile');
                } else {
                    $this->setFlash('error', 'Profile update failed. Please try again.');
                }
            } else {
                $this->setFlash('errors', $errors);
                $this->setFlash('old', $_POST);
            }
        }
        
        $this->view('auth/profile', [
            'title' => 'My Profile',
            'user' => $this->auth->getUser(),
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    // Helper methods for CSRF token
    protected function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function validateCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && $token === $_SESSION['csrf_token'];
    }
} 