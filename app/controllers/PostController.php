<?php

class PostController extends Controller {
    private $postModel;
    private $categoryModel;

    public function __construct() {
        parent::__construct();
        $this->postModel = new PostModel();
        $this->categoryModel = new CategoryModel();
    }

    public function index() {
        // Get filters from query string
        $page = $this->get('page', 1);
        $status = $this->get('status');
        $category = $this->get('category');
        $search = $this->get('search');

        // Build filters
        $filters = [];
        if ($status) $filters['status'] = $status;
        if ($category) $filters['category_id'] = $category;
        if ($search) $filters['search'] = $search;

        // If not admin/editor, only show own posts
        if (!hasPermission('manage_posts')) {
            $filters['user_id'] = getCurrentUser()['id'];
        }

        // Get posts with pagination
        $result = $this->postModel->getAll($page, $filters);

        // Get categories for filter
        $categories = $this->categoryModel->getParents();

        // Render view
        $this->view('post/index', [
            'title' => 'Posts',
            'posts' => $result['data'],
            'pagination' => [
                'total' => $result['total'],
                'per_page' => $result['per_page'],
                'current_page' => $result['current_page'],
                'last_page' => $result['last_page']
            ],
            'filters' => [
                'status' => $status,
                'category' => $category,
                'search' => $search
            ],
            'categories' => $categories
        ]);
    }

    public function create() {
        // Check permission
        $this->requirePermission('create_posts');

        if ($this->isPost()) {
            // Validate CSRF token
            $this->validateCsrf();

            // Validate input
            $rules = [
                'title' => 'required|min:3|max:255',
                'content' => 'required|min:10',
                'category_id' => 'nullable|exists:categories,id',
                'status' => 'required|in:draft,published',
                'comment_status' => 'required|in:open,closed'
            ];

            $data = $this->validate($this->post(), $rules);

            if ($data === false) {
                $this->setFlash('error', 'Please check your input');
                return $this->redirect('post/create');
            }

            // Handle featured image upload
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                $upload = $this->handleImageUpload($_FILES['featured_image']);
                if ($upload['success']) {
                    $data['featured_image'] = $upload['filename'];
                } else {
                    $this->setFlash('error', $upload['message']);
                    return $this->redirect('post/create');
                }
            }

            // Add user_id
            $data['user_id'] = getCurrentUser()['id'];

            // Create post
            $postId = $this->postModel->create($data);

            if ($postId) {
                $this->setFlash('success', 'Post created successfully');
                return $this->redirect('post/edit/' . $postId);
            } else {
                $this->setFlash('error', 'Failed to create post');
                return $this->redirect('post/create');
            }
        }

        // Get categories for form
        $categories = $this->categoryModel->getTree();

        // Render view
        $this->view('post/create', [
            'title' => 'Create Post',
            'categories' => $categories
        ]);
    }

    public function edit($id) {
        // Get post
        $post = $this->postModel->getById($id);
        if (!$post) {
            $this->setFlash('error', 'Post not found');
            return $this->redirect('post');
        }

        // Check permission
        if (!hasPermission('manage_posts') && $post['user_id'] != getCurrentUser()['id']) {
            $this->setFlash('error', 'You do not have permission to edit this post');
            return $this->redirect('post');
        }

        if ($this->isPost()) {
            // Validate CSRF token
            $this->validateCsrf();

            // Validate input
            $rules = [
                'title' => 'required|min:3|max:255',
                'content' => 'required|min:10',
                'category_id' => 'nullable|exists:categories,id',
                'status' => 'required|in:draft,published,archived',
                'comment_status' => 'required|in:open,closed'
            ];

            $data = $this->validate($this->post(), $rules);

            if ($data === false) {
                $this->setFlash('error', 'Please check your input');
                return $this->redirect('post/edit/' . $id);
            }

            // Handle featured image upload
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                $upload = $this->handleImageUpload($_FILES['featured_image']);
                if ($upload['success']) {
                    // Delete old image if exists
                    if ($post['featured_image']) {
                        $this->deleteImage($post['featured_image']);
                    }
                    $data['featured_image'] = $upload['filename'];
                } else {
                    $this->setFlash('error', $upload['message']);
                    return $this->redirect('post/edit/' . $id);
                }
            }

            // Update post
            if ($this->postModel->update($id, $data)) {
                $this->setFlash('success', 'Post updated successfully');
                return $this->redirect('post/edit/' . $id);
            } else {
                $this->setFlash('error', 'No changes made to post');
                return $this->redirect('post/edit/' . $id);
            }
        }

        // Get categories for form
        $categories = $this->categoryModel->getTree();

        // Render view
        $this->view('post/edit', [
            'title' => 'Edit Post',
            'post' => $post,
            'categories' => $categories
        ]);
    }

    public function delete($id) {
        // Check permission
        $this->requirePermission('delete_posts');

        // Get post
        $post = $this->postModel->getById($id);
        if (!$post) {
            $this->setFlash('error', 'Post not found');
            return $this->redirect('post');
        }

        // Check if user can delete
        if (!hasPermission('manage_posts') && $post['user_id'] != getCurrentUser()['id']) {
            $this->setFlash('error', 'You do not have permission to delete this post');
            return $this->redirect('post');
        }

        // Delete featured image if exists
        if ($post['featured_image']) {
            $this->deleteImage($post['featured_image']);
        }

        // Delete post
        if ($this->postModel->delete($id)) {
            $this->setFlash('success', 'Post deleted successfully');
        } else {
            $this->setFlash('error', 'Failed to delete post');
        }

        return $this->redirect('post');
    }

    public function status($id, $status) {
        // Check permission
        $this->requirePermission('manage_posts');

        // Get post
        $post = $this->postModel->getById($id);
        if (!$post) {
            $this->setFlash('error', 'Post not found');
            return $this->redirect('post');
        }

        // Update status
        if ($this->postModel->updateStatus($id, $status)) {
            $this->setFlash('success', 'Post status updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update post status');
        }

        return $this->redirect('post');
    }

    public function view($slug) {
        // Get post
        $post = $this->postModel->getBySlug($slug);
        if (!$post) {
            $this->setFlash('error', 'Post not found');
            return $this->redirect('post');
        }

        // Check if post is published or user has permission
        if ($post['status'] !== 'published' && 
            (!isLoggedIn() || 
             (!hasPermission('manage_posts') && $post['user_id'] != getCurrentUser()['id']))) {
            $this->setFlash('error', 'Post not found');
            return $this->redirect('post');
        }

        // Increment view count if published
        if ($post['status'] === 'published') {
            $this->postModel->incrementViewCount($post['id']);
        }

        // Get related posts
        $relatedPosts = $this->postModel->getRelatedPosts($post['id']);

        // Render view
        $this->view('post/view', [
            'title' => $post['title'],
            'post' => $post,
            'relatedPosts' => $relatedPosts
        ]);
    }

    private function handleImageUpload($file) {
        // Get upload config
        $config = require __DIR__ . '/../config/app.php';
        $uploadConfig = $config['upload'];

        // Validate file
        if (!isset($file['type']) || !in_array($file['type'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
            return [
                'success' => false,
                'message' => 'Invalid file type. Only JPG, PNG, GIF and WebP images are allowed.'
            ];
        }

        if ($file['size'] > $uploadConfig['max_size']) {
            return [
                'success' => false,
                'message' => 'File size exceeds limit of ' . formatFileSize($uploadConfig['max_size'])
            ];
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $uploadPath = __DIR__ . '/../../public/uploads/posts/' . date('Y/m');

        // Create directory if not exists
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath . '/' . $filename)) {
            return [
                'success' => true,
                'filename' => 'posts/' . date('Y/m') . '/' . $filename
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to upload file'
        ];
    }

    private function deleteImage($filename) {
        $path = __DIR__ . '/../../public/uploads/' . $filename;
        if (file_exists($path)) {
            unlink($path);
        }
    }
} 