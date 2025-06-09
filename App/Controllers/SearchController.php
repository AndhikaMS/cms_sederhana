<?php

namespace App\Controllers;

use App\Models\PostModel;
use App\Models\CategoryModel;

class SearchController extends BaseController
{
    private $postModel;
    private $categoryModel;

    public function __construct()
    {
        parent::__construct();
        $this->postModel = new PostModel();
        $this->categoryModel = new CategoryModel();
    }

    /**
     * Search posts
     */
    public function index()
    {
        $search = isset($_GET['q']) ? trim($_GET['q']) : '';
        
        // If search is empty, redirect to home
        if (empty($search)) {
            $this->redirect('/');
        }
        
        $posts = $this->postModel->searchPosts($search);
        $categories = $this->categoryModel->getPopularCategories(5);
        
        $this->view('search/index', [
            'search' => $search,
            'posts' => $posts,
            'categories' => $categories,
            'totalResults' => count($posts)
        ]);
    }
} 