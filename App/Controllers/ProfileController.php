<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ActivityLogModel;

class ProfileController extends BaseController
{
    private $userModel;
    private $activityLogModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->activityLogModel = new ActivityLogModel();
        
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
        }
    }

    /**
     * Show user profile
     */
    public function index()
    {
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getById($userId);
        $activities = $this->activityLogModel->getUserActivities($userId);
        
        $this->view('profile/index', [
            'user' => $user,
            'activities' => $activities
        ]);
    }

    /**
     * Update user profile
     */
    public function update()
    {
        if (!$this->isPost()) {
            $this->redirect('/profile');
        }

        $userId = $_SESSION['user_id'];
        $data = [
            'name' => $this->clean($_POST['username']),
            'email' => $this->clean($_POST['email'])
        ];
        
        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_picture']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $newFilename = 'profile_' . $userId . '_' . time() . '.' . $ext;
                $uploadPath = 'uploads/profiles/' . $newFilename;
                
                // Create directory if it doesn't exist
                if (!is_dir('uploads/profiles/')) {
                    mkdir('uploads/profiles/', 0755, true);
                }
                
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadPath)) {
                    $data['avatar'] = $uploadPath;
                }
            }
        }
        
        if ($this->userModel->update($userId, $data)) {
            $this->setFlash('success', 'Profile updated successfully!');
        } else {
            $this->setFlash('error', 'Error updating profile!');
        }

        $this->redirect('/profile');
    }

    /**
     * Change user password
     */
    public function changePassword()
    {
        if (!$this->isPost()) {
            $this->redirect('/profile');
        }

        $userId = $_SESSION['user_id'];
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if ($newPassword !== $confirmPassword) {
            $this->setFlash('error', 'New passwords do not match!');
            $this->redirect('/profile');
        }
        
        if (strlen($newPassword) < 6) {
            $this->setFlash('error', 'Password must be at least 6 characters long!');
            $this->redirect('/profile');
        }
        
        // Verify current password
        if (!$this->userModel->validatePassword($userId, $currentPassword)) {
            $this->setFlash('error', 'Current password is incorrect!');
            $this->redirect('/profile');
        }
        
        if ($this->userModel->updatePassword($userId, $newPassword)) {
            $this->setFlash('success', 'Password changed successfully!');
        } else {
            $this->setFlash('error', 'Error changing password!');
        }

        $this->redirect('/profile');
    }
} 