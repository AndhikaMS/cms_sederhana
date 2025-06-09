<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\InviteCodeModel;

class UserController extends BaseController
{
    private $userModel;
    private $inviteCodeModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->inviteCodeModel = new InviteCodeModel();
        
        // Check if user is admin
        if (!$this->isAdmin()) {
            $this->redirect('/dashboard');
        }
    }

    /**
     * List all users
     */
    public function index()
    {
        $users = $this->userModel->getAllUsersWithStats();
        
        $this->view('users/index', [
            'users' => $users,
            'currentUserId' => $_SESSION['user_id'] ?? null
        ]);
    }

    /**
     * Delete a user
     */
    public function delete($id)
    {
        if (!$this->isPost()) {
            $this->redirect('/users');
        }

        $userId = (int)$id;
        
        // Prevent deleting own account
        if ($userId == ($_SESSION['user_id'] ?? 0)) {
            $this->setFlash('error', 'You cannot delete your own account!');
            $this->redirect('/users');
        }

        if ($this->userModel->deleteUser($userId)) {
            $this->setFlash('success', 'User deleted successfully!');
        } else {
            $this->setFlash('error', 'Error deleting user!');
        }

        $this->redirect('/users');
    }

    /**
     * Change user role
     */
    public function changeRole()
    {
        if (!$this->isPost()) {
            $this->redirect('/users');
        }

        $userId = (int)$_POST['user_id'];
        $newRole = $this->clean($_POST['role']);
        
        // Prevent changing own role
        if ($userId == ($_SESSION['user_id'] ?? 0)) {
            $this->setFlash('error', 'You cannot change your own role!');
            $this->redirect('/users');
        }

        if ($this->userModel->updateUserRole($userId, $newRole)) {
            $this->setFlash('success', 'User role updated successfully!');
        } else {
            $this->setFlash('error', 'Error updating user role!');
        }

        $this->redirect('/users');
    }

    /**
     * Show invite codes management
     */
    public function inviteCodes()
    {
        $inviteCodes = $this->inviteCodeModel->getAllInviteCodes();
        
        $this->view('users/invite_codes', [
            'inviteCodes' => $inviteCodes
        ]);
    }

    /**
     * Generate new invite code
     */
    public function generateInviteCode()
    {
        if (!$this->isPost()) {
            $this->redirect('/users/invite-codes');
        }

        $role = $this->clean($_POST['role']);
        $expiresIn = (int)$_POST['expires_in'];
        
        if ($this->inviteCodeModel->generateInviteCode($role, $expiresIn, $_SESSION['user_id'])) {
            $this->setFlash('success', 'Invite code generated successfully!');
        } else {
            $this->setFlash('error', 'Error generating invite code!');
        }

        $this->redirect('/users/invite-codes');
    }

    /**
     * Delete invite code
     */
    public function deleteInviteCode($id)
    {
        if (!$this->isPost()) {
            $this->redirect('/users/invite-codes');
        }

        $codeId = (int)$id;
        
        if ($this->inviteCodeModel->deleteInviteCode($codeId)) {
            $this->setFlash('success', 'Invite code deleted successfully!');
        } else {
            $this->setFlash('error', 'Error deleting invite code!');
        }

        $this->redirect('/users/invite-codes');
    }
} 