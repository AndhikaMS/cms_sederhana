<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Services\Auth;

class AuthController extends Controller {
    private $auth;
    private $userModel;
    private $roleModel;

    public function __construct() {
        parent::__construct();
        $this->auth = Auth::getInstance();
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
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
                redirect('auth/login');
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
                    redirect($this->auth->getIntendedUrl());
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
                redirect('auth/register');
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
                    redirect('auth/login');
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
        redirect('auth/login');
    }

    public function forgotPassword() {
        // Redirect if already logged in
        $this->auth->requireGuest();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            
            // Validate CSRF token
            if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->setFlash('error', 'Invalid request');
                redirect('auth/forgot-password');
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
                    redirect('auth/login');
                } else {
                    // Don't reveal if email exists or not
                    $this->setFlash('success', 'If your email is registered, you will receive password reset instructions.');
                    redirect('auth/login');
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
            redirect('auth/forgot-password');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $password_confirmation = $_POST['password_confirmation'] ?? '';
            
            // Validate CSRF token
            if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->setFlash('error', 'Invalid request');
                redirect("auth/reset-password/$token");
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
                    redirect('auth/login');
                } else {
                    $this->setFlash('error', 'Invalid or expired reset token.');
                    redirect('auth/forgot-password');
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
            redirect('auth/login');
        }
        
        if ($this->auth->verifyEmail($token)) {
            $this->setFlash('success', 'Your email has been verified. You can now login.');
        } else {
            $this->setFlash('error', 'Invalid or expired verification token.');
        }
        
        redirect('auth/login');
    }

    public function profile() {
        // Require login
        $this->auth->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $new_password_confirmation = $_POST['new_password_confirmation'] ?? '';
            
            // Validate CSRF token
            if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->setFlash('error', 'Invalid request');
                redirect('auth/profile');
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
            } elseif ($email !== $this->auth->getUser()['email'] && $this->userModel->getByEmail($email)) {
                $errors['email'] = 'Email already registered';
            }
            
            // Only validate password if user wants to change it
            if (!empty($current_password) || !empty($new_password) || !empty($new_password_confirmation)) {
                if (empty($current_password)) {
                    $errors['current_password'] = 'Current password is required to change password';
                } elseif (!$this->auth->validatePassword($current_password)) {
                    $errors['current_password'] = 'Current password is incorrect';
                }
                
                if (empty($new_password)) {
                    $errors['new_password'] = 'New password is required';
                } elseif (strlen($new_password) < 8) {
                    $errors['new_password'] = 'New password must be at least 8 characters';
                }
                
                if ($new_password !== $new_password_confirmation) {
                    $errors['new_password_confirmation'] = 'Password confirmation does not match';
                }
            }
            
            if (empty($errors)) {
                $data = [
                    'name' => $name,
                    'email' => $email
                ];
                
                // Update password if provided
                if (!empty($new_password)) {
                    $data['password'] = $new_password;
                }
                
                // Handle avatar upload
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $avatar = $_FILES['avatar'];
                    
                    // Validate file type
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!in_array($avatar['type'], $allowedTypes)) {
                        $errors['avatar'] = 'Only JPG, PNG and GIF images are allowed';
                    }
                    
                    // Validate file size (max 2MB)
                    if ($avatar['size'] > 2 * 1024 * 1024) {
                        $errors['avatar'] = 'Image size must be less than 2MB';
                    }
                    
                    if (empty($errors)) {
                        // Generate unique filename
                        $extension = pathinfo($avatar['name'], PATHINFO_EXTENSION);
                        $filename = uniqid() . '.' . $extension;
                        $uploadPath = 'uploads/avatars/' . $filename;
                        
                        // Create directory if not exists
                        if (!is_dir('uploads/avatars')) {
                            mkdir('uploads/avatars', 0777, true);
                        }
                        
                        // Move uploaded file
                        if (move_uploaded_file($avatar['tmp_name'], $uploadPath)) {
                            // Delete old avatar if exists
                            $oldAvatar = $this->auth->getUser()['avatar'];
                            if ($oldAvatar && file_exists($oldAvatar)) {
                                unlink($oldAvatar);
                            }
                            
                            $data['avatar'] = $uploadPath;
                        } else {
                            $errors['avatar'] = 'Failed to upload avatar';
                        }
                    }
                }
                
                if (empty($errors)) {
                    if ($this->auth->updateProfile($data)) {
                        $this->setFlash('success', 'Profile updated successfully');
                    } else {
                        $this->setFlash('error', 'Failed to update profile');
                    }
                } else {
                    $this->setFlash('errors', $errors);
                    $this->setFlash('old', $_POST);
                }
            } else {
                $this->setFlash('errors', $errors);
                $this->setFlash('old', $_POST);
            }
        }
        
        // Show profile form
        $this->view('auth/profile', [
            'title' => 'Profile',
            'user' => $this->auth->getUser(),
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    private function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    private function validateCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
} 