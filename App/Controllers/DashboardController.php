<?php
namespace App\Controllers;

class DashboardController extends BaseController {
    public function index() {
        // You can fetch data for the dashboard here
        // For now, let's just render a basic view
        
        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'user' => $this->auth->getUser(),
            'totalPosts' => 0, // Placeholder
            'totalCategories' => 0, // Placeholder
            'totalUsers' => 0, // Placeholder
        ]);
    }
} 