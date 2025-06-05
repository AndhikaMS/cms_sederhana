<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new UserModel();
    }

    public function loginForm() {
        // Redirect if already logged in
        if ($this->isLoggedIn()) {
            $this->redirect('/');
        }

        // Check remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            $user = $this->userModel->findByRememberToken($_COOKIE['remember_token']);
            if ($user) {
                $this->loginUser($user, true);
                $this->redirect('/');
            }
        }

        $this->view('auth/login');
    }

    public function login() {
        if (!$this->isPost()) {
            $this->redirect('/login');
        }

        $username = $this->post('username');
        $password = $this->post('password');
        $remember = $this->post('remember') ? true : false;

        // Validate input
        if (empty($username) || empty($password)) {
            $this->setFlash('error', 'Username and password are required!');
            $this->redirect('/login');
        }

        // Check user
        $user = $this->userModel->findByUsername($username);
        if (!$user || !password_verify($password, $user['password'])) {
            $this->setFlash('error', 'Invalid username or password!');
            $this->redirect('/login');
        }

        // Login user
        $this->loginUser($user, $remember);
        $this->redirect('/');
    }

    public function registerForm() {
        // Redirect if already logged in
        if ($this->isLoggedIn()) {
            $this->redirect('/');
        }

        $this->view('auth/register');
    }

    public function register() {
        if (!$this->isPost()) {
            $this->redirect('/register');
        }

        $username = $this->post('username');
        $email = $this->post('email');
        $password = $this->post('password');
        $confirm_password = $this->post('confirm_password');
        $invite_code = $this->post('invite_code');

        // Validate input
        if (empty($username) || empty($email) || empty($password) || empty($invite_code)) {
            $this->setFlash('error', 'All fields are required!');
            $this->redirect('/register');
        }

        if ($password !== $confirm_password) {
            $this->setFlash('error', 'Passwords do not match!');
            $this->redirect('/register');
        }

        if (strlen($password) < 6) {
            $this->setFlash('error', 'Password must be at least 6 characters long!');
            $this->redirect('/register');
        }

        // Check if username/email exists
        if ($this->userModel->findByUsername($username) || $this->userModel->findByEmail($email)) {
            $this->setFlash('error', 'Username or email already exists!');
            $this->redirect('/register');
        }

        // Validate invite code
        $invite = $this->validateInviteCode($invite_code);
        if (!$invite) {
            $this->setFlash('error', 'Invalid or expired invite code!');
            $this->redirect('/register');
        }

        // Create user
        $user_id = $this->userModel->create([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => $invite['role']
        ]);

        if ($user_id) {
            // Update invite code status
            $this->markInviteCodeAsUsed($invite['id'], $user_id);
            
            $this->setFlash('success', 'Registration successful! You can now login.');
            $this->redirect('/login');
        } else {
            $this->setFlash('error', 'Error registering user!');
            $this->redirect('/register');
        }
    }

    public function logout() {
        // Clear remember me cookie if exists
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }

        // Clear session
        session_destroy();
        
        $this->redirect('/login');
    }

    public function forgotPassword() {
        if ($this->isPost()) {
            $email = $this->post('email');
            
            if (empty($email)) {
                $this->setFlash('error', 'Email is required!');
                $this->redirect('/forgot-password');
            }

            $user = $this->userModel->findByEmail($email);
            if ($user) {
                $token = $this->generatePasswordResetToken($user['id']);
                // TODO: Send reset password email
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password?token=" . $token;
                $this->setFlash('success', "Password reset link has been sent to your email. For testing, here's the link: <a href='$reset_link'>Reset Password</a>");
            } else {
                $this->setFlash('error', 'Email not found!');
            }
            
            $this->redirect('/forgot-password');
        }

        $this->view('auth/forgot-password');
    }

    public function resetPassword() {
        $token = $this->get('token');
        
        if (!$token) {
            $this->redirect('/forgot-password');
        }

        $reset = $this->verifyPasswordResetToken($token);
        if (!$reset) {
            $this->setFlash('error', 'Invalid or expired reset token!');
            $this->redirect('/forgot-password');
        }

        if ($this->isPost()) {
            $password = $this->post('password');
            $confirm_password = $this->post('confirm_password');

            if (empty($password) || empty($confirm_password)) {
                $this->setFlash('error', 'All fields are required!');
                $this->redirect('/reset-password?token=' . $token);
            }

            if ($password !== $confirm_password) {
                $this->setFlash('error', 'Passwords do not match!');
                $this->redirect('/reset-password?token=' . $token);
            }

            if (strlen($password) < 6) {
                $this->setFlash('error', 'Password must be at least 6 characters long!');
                $this->redirect('/reset-password?token=' . $token);
            }

            if ($this->userModel->updatePassword($reset['user_id'], $password)) {
                $this->markPasswordResetTokenAsUsed($token);
                $this->setFlash('success', 'Password has been reset successfully!');
                $this->redirect('/login');
            } else {
                $this->setFlash('error', 'Error resetting password!');
                $this->redirect('/reset-password?token=' . $token);
            }
        }

        $this->view('auth/reset-password', ['token' => $token]);
    }

    private function loginUser($user, $remember = false) {
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Update last login
        $this->userModel->updateLastLogin($user['id']);

        // Log activity
        $this->userModel->logActivity($user['id'], 'login', 'User logged in successfully');

        // Handle remember me
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $this->userModel->update($user['id'], ['remember_token' => $token]);
            setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 days
        }
    }

    private function validateInviteCode($code) {
        $code = $this->clean($code);
        $query = "SELECT * FROM invite_codes 
                 WHERE code = '$code' 
                 AND is_used = 0 
                 AND (expires_at IS NULL OR expires_at > NOW())";
        $result = $this->query($query);
        return $result->fetch_assoc();
    }

    private function markInviteCodeAsUsed($code_id, $user_id) {
        $code_id = (int)$code_id;
        $user_id = (int)$user_id;
        $query = "UPDATE invite_codes 
                 SET is_used = 1, used_by = $user_id 
                 WHERE id = $code_id";
        return $this->query($query);
    }

    private function generatePasswordResetToken($user_id) {
        $user_id = (int)$user_id;
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $query = "INSERT INTO password_resets (user_id, token, expires_at) 
                 VALUES ($user_id, '$token', '$expires')";
        $this->query($query);
        
        return $token;
    }

    private function verifyPasswordResetToken($token) {
        $token = $this->clean($token);
        $query = "SELECT * FROM password_resets 
                 WHERE token = '$token' 
                 AND used = 0 
                 AND expires_at > NOW()";
        $result = $this->query($query);
        return $result->fetch_assoc();
    }

    private function markPasswordResetTokenAsUsed($token) {
        $token = $this->clean($token);
        $query = "UPDATE password_resets SET used = 1 WHERE token = '$token'";
        return $this->query($query);
    }
} 