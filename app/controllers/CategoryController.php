<?php
namespace App\Controllers;

class CategoryController extends Controller {
    private $categoryModel;
    private $postModel;

    public function __construct() {
        parent::__construct();
        $this->categoryModel = new CategoryModel();
        $this->postModel = new PostModel();
    }

    public function index() {
        // Check permission
        $this->requirePermission('manage_categories');

        // Get filters from query string
        $page = $this->get('page', 1);
        $status = $this->get('status');
        $parent = $this->get('parent');
        $search = $this->get('search');

        // Build filters
        $filters = [];
        if ($status) $filters['status'] = $status;
        if ($parent) $filters['parent_id'] = $parent;
        if ($search) $filters['search'] = $search;

        // Get categories with pagination
        $result = $this->categoryModel->getAll($page, $filters);

        // Get parent categories for filter
        $parentCategories = $this->categoryModel->getParents();

        // Render view
        $this->view('category/index', [
            'title' => 'Categories',
            'categories' => $result['data'],
            'pagination' => [
                'total' => $result['total'],
                'per_page' => $result['per_page'],
                'current_page' => $result['current_page'],
                'last_page' => $result['last_page']
            ],
            'filters' => [
                'status' => $status,
                'parent' => $parent,
                'search' => $search
            ],
            'parentCategories' => $parentCategories
        ]);
    }

    public function create() {
        // Check permission
        $this->requirePermission('manage_categories');

        if ($this->isPost()) {
            // Validate CSRF token
            $this->validateCsrf();

            // Validate input
            $rules = [
                'name' => 'required|min:2|max:50',
                'slug' => 'nullable|min:2|max:50|alpha_dash',
                'description' => 'nullable|max:255',
                'parent_id' => 'nullable|exists:categories,id',
                'status' => 'required|in:active,inactive'
            ];

            $data = $this->validate($this->post(), $rules);

            if ($data === false) {
                $this->setFlash('error', 'Please check your input');
                return $this->redirect('category/create');
            }

            // Validate parent category
            if (isset($data['parent_id'])) {
                if (!$this->categoryModel->validateParent(null, $data['parent_id'])) {
                    $this->setFlash('error', 'Invalid parent category');
                    return $this->redirect('category/create');
                }
            }

            // Create category
            $categoryId = $this->categoryModel->create($data);

            if ($categoryId) {
                $this->setFlash('success', 'Category created successfully');
                return $this->redirect('category/edit/' . $categoryId);
            } else {
                $this->setFlash('error', 'Failed to create category');
                return $this->redirect('category/create');
            }
        }

        // Get parent categories for form
        $parentCategories = $this->categoryModel->getParents();

        // Render view
        $this->view('category/create', [
            'title' => 'Create Category',
            'parentCategories' => $parentCategories
        ]);
    }

    public function edit($id) {
        // Check permission
        $this->requirePermission('manage_categories');

        // Get category
        $category = $this->categoryModel->getById($id);
        if (!$category) {
            $this->setFlash('error', 'Category not found');
            return $this->redirect('category');
        }

        if ($this->isPost()) {
            // Validate CSRF token
            $this->validateCsrf();

            // Validate input
            $rules = [
                'name' => 'required|min:2|max:50',
                'slug' => 'nullable|min:2|max:50|alpha_dash',
                'description' => 'nullable|max:255',
                'parent_id' => 'nullable|exists:categories,id',
                'status' => 'required|in:active,inactive'
            ];

            $data = $this->validate($this->post(), $rules);

            if ($data === false) {
                $this->setFlash('error', 'Please check your input');
                return $this->redirect('category/edit/' . $id);
            }

            // Validate parent category
            if (isset($data['parent_id'])) {
                if (!$this->categoryModel->validateParent($id, $data['parent_id'])) {
                    $this->setFlash('error', 'Invalid parent category');
                    return $this->redirect('category/edit/' . $id);
                }
            }

            // Update category
            if ($this->categoryModel->update($id, $data)) {
                $this->setFlash('success', 'Category updated successfully');
                return $this->redirect('category/edit/' . $id);
            } else {
                $this->setFlash('error', 'No changes made to category');
                return $this->redirect('category/edit/' . $id);
            }
        }

        // Get parent categories for form
        $parentCategories = $this->categoryModel->getParents();

        // Render view
        $this->view('category/edit', [
            'title' => 'Edit Category',
            'category' => $category,
            'parentCategories' => $parentCategories
        ]);
    }

    public function delete($id) {
        // Check permission
        $this->requirePermission('manage_categories');

        // Get category
        $category = $this->categoryModel->getById($id);
        if (!$category) {
            $this->setFlash('error', 'Category not found');
            return $this->redirect('category');
        }

        // Delete category
        if ($this->categoryModel->delete($id)) {
            $this->setFlash('success', 'Category deleted successfully');
        } else {
            $this->setFlash('error', 'Cannot delete category. It may have posts or subcategories.');
        }

        return $this->redirect('category');
    }

    public function status($id, $status) {
        // Check permission
        $this->requirePermission('manage_categories');

        // Get category
        $category = $this->categoryModel->getById($id);
        if (!$category) {
            $this->setFlash('error', 'Category not found');
            return $this->redirect('category');
        }

        // Update status
        if ($this->categoryModel->updateStatus($id, $status)) {
            $this->setFlash('success', 'Category status updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update category status');
        }

        return $this->redirect('category');
    }

    public function view($slug) {
        // Get category
        $category = $this->categoryModel->getBySlug($slug);
        if (!$category) {
            $this->setFlash('error', 'Category not found');
            return $this->redirect('category');
        }

        // Get posts in category
        $page = $this->get('page', 1);
        $posts = $this->postModel->getByCategory($category['id'], $page);

        // Get subcategories
        $subcategories = $this->categoryModel->getChildren($category['id']);

        // Render view
        $this->view('category/view', [
            'title' => $category['name'],
            'category' => $category,
            'posts' => $posts['data'],
            'pagination' => [
                'total' => $posts['total'],
                'per_page' => $posts['per_page'],
                'current_page' => $posts['current_page'],
                'last_page' => $posts['last_page']
            ],
            'subcategories' => $subcategories
        ]);
    }

    public function tree() {
        // Check permission
        $this->requirePermission('manage_categories');

        // Get category tree
        $tree = $this->categoryModel->getTree();

        // Render view
        $this->view('category/tree', [
            'title' => 'Category Tree',
            'tree' => $tree
        ]);
    }
} 