<?php
// Auth routes
$router->get('/login', 'App\Controllers\AuthController@loginForm');
$router->post('/login', 'App\Controllers\AuthController@login');
$router->get('/register', 'App\Controllers\AuthController@registerForm');
$router->post('/register', 'App\Controllers\AuthController@register');
$router->get('/logout', 'App\Controllers\AuthController@logout');
$router->get('/forgot-password', 'App\Controllers\AuthController@forgotPassword');
$router->post('/forgot-password', 'App\Controllers\AuthController@forgotPassword');
$router->get('/reset-password/{token}', 'App\Controllers\AuthController@resetPassword');
$router->post('/reset-password/{token}', 'App\Controllers\AuthController@resetPassword');

// Dashboard routes
$router->get('/', 'App\Controllers\DashboardController@index');
$router->get('/dashboard', 'App\\Controllers\\DashboardController@index'); 

// Post routes
$router->get('/posts', 'App\Controllers\PostController@index');
$router->get('/posts/create', 'App\Controllers\PostController@create');
$router->post('/posts', 'App\Controllers\PostController@store');
$router->get('/posts/{id}', 'App\Controllers\PostController@show');
$router->get('/posts/{id}/edit', 'App\Controllers\PostController@edit');
$router->post('/posts/{id}', 'App\Controllers\PostController@update');
$router->post('/posts/{id}/delete', 'App\Controllers\PostController@delete');
$router->post('/posts/{id}/publish', 'App\Controllers\PostController@publish');
$router->post('/posts/{id}/unpublish', 'App\Controllers\PostController@unpublish');

// Category routes
$router->get('/categories', 'App\Controllers\CategoryController@index');
$router->get('/categories/create', 'App\Controllers\CategoryController@create');
$router->post('/categories', 'App\Controllers\CategoryController@store');
$router->get('/categories/{id}', 'App\Controllers\CategoryController@show');
$router->get('/categories/{id}/edit', 'App\Controllers\CategoryController@edit');
$router->post('/categories/{id}', 'App\Controllers\CategoryController@update');
$router->post('/categories/{id}/delete', 'App\Controllers\CategoryController@delete');

// User routes
$router->get('/users', 'App\Controllers\UserController@index');
$router->get('/users/create', 'App\Controllers\UserController@create');
$router->post('/users', 'App\Controllers\UserController@store');
$router->get('/users/{id}', 'App\Controllers\UserController@show');
$router->get('/users/{id}/edit', 'App\Controllers\UserController@edit');
$router->post('/users/{id}', 'App\Controllers\UserController@update');
$router->post('/users/{id}/delete', 'App\Controllers\UserController@delete');
$router->post('/users/{id}/activate', 'App\Controllers\UserController@activate');
$router->post('/users/{id}/deactivate', 'App\Controllers\UserController@deactivate');
$router->post('/users/change-role', 'App\Controllers\UserController@changeRole');
$router->get('/users/invite-codes', 'App\Controllers\UserController@inviteCodes');
$router->post('/users/generate-invite-code', 'App\Controllers\UserController@generateInviteCode');
$router->post('/users/delete-invite-code/{id}', 'App\Controllers\UserController@deleteInviteCode');

// Profile routes
$router->get('/profile', 'App\Controllers\ProfileController@index');
$router->post('/profile/update', 'App\Controllers\ProfileController@update');
$router->post('/profile/change-password', 'App\Controllers\ProfileController@changePassword');

// Invite code routes
$router->get('/invite-codes', 'App\Controllers\InviteCodeController@index');
$router->get('/invite-codes/create', 'App\Controllers\InviteCodeController@create');
$router->post('/invite-codes', 'App\Controllers\InviteCodeController@store');
$router->post('/invite-codes/{id}/delete', 'App\Controllers\InviteCodeController@delete');
$router->post('/invite-codes/{id}/revoke', 'App\Controllers\InviteCodeController@revoke');

// Activity log routes
$router->get('/activity-logs', 'App\Controllers\ActivityLogController@index');
$router->get('/activity-logs/{id}', 'App\Controllers\ActivityLogController@show');
$router->post('/activity-logs/clear', 'App\Controllers\ActivityLogController@clear');

// API routes
$router->get('/api/posts', 'App\Controllers\Api\PostController@index');
$router->get('/api/posts/{id}', 'App\Controllers\Api\PostController@show');
$router->get('/api/categories', 'App\Controllers\Api\CategoryController@index');
$router->get('/api/categories/{id}', 'App\Controllers\Api\CategoryController@show');
$router->get('/api/users', 'App\Controllers\Api\UserController@index');
$router->get('/api/users/{id}', 'App\Controllers\Api\UserController@show');

// File upload routes
$router->post('/upload/image', 'App\Controllers\UploadController@image');
$router->post('/upload/file', 'App\Controllers\UploadController@file');
$router->post('/upload/delete', 'App\Controllers\UploadController@delete');

// Search routes
$router->get('/search', 'App\Controllers\SearchController@index'); 