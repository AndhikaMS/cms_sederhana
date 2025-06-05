<?php
namespace App\Helpers;

class Functions {
    /**
     * Clean input data
     */
    public static function clean($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Check if user has permission
     */
    public static function hasPermission($permission) {
        if (!self::isLoggedIn()) {
            return false;
        }

        $userModel = new \App\Models\UserModel();
        return $userModel->hasPermission($_SESSION['user_id'], $permission);
    }

    /**
     * Get current user data
     */
    public static function getCurrentUser() {
        if (!self::isLoggedIn()) {
            return null;
        }

        $userModel = new \App\Models\UserModel();
        return $userModel->getById($_SESSION['user_id']);
    }

    /**
     * Format date
     */
    public static function formatDate($date, $format = 'd M Y H:i') {
        return date($format, strtotime($date));
    }

    /**
     * Generate slug from string
     */
    public static function generateSlug($string) {
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
        $string = preg_replace('/[\s-]+/', ' ', $string);
        $string = preg_replace('/\s/', '-', $string);
        return $string;
    }

    /**
     * Truncate text
     */
    public static function truncate($text, $length = 100, $append = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }

        $text = substr($text, 0, $length);
        $text = substr($text, 0, strrpos($text, ' '));
        return $text . $append;
    }

    /**
     * Get file extension
     */
    public static function getFileExtension($filename) {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Check if file is image
     */
    public static function isImage($filename) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        return in_array(self::getFileExtension($filename), $allowed);
    }

    /**
     * Generate random string
     */
    public static function generateRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Get client IP address
     */
    public static function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Get client user agent
     */
    public static function getClientUserAgent() {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    /**
     * Check if request is AJAX
     */
    public static function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * Get current URL
     */
    public static function getCurrentUrl() {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
               "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }

    /**
     * Get base URL
     */
    public static function getBaseUrl() {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
               "://$_SERVER[HTTP_HOST]";
    }

    /**
     * Redirect to URL
     */
    public static function redirect($url) {
        header("Location: $url");
        exit();
    }

    /**
     * Set flash message
     */
    public static function setFlash($type, $message) {
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Get flash message
     */
    public static function getFlash($type) {
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        return null;
    }

    /**
     * Check if flash message exists
     */
    public static function hasFlash($type) {
        return isset($_SESSION['flash'][$type]);
    }

    /**
     * Get all flash messages
     */
    public static function getAllFlash() {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }

    /**
     * Validate email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validate URL
     */
    public static function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    /**
     * Validate IP address
     */
    public static function validateIP($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP);
    }

    /**
     * Validate integer
     */
    public static function validateInt($int) {
        return filter_var($int, FILTER_VALIDATE_INT);
    }

    /**
     * Validate float
     */
    public static function validateFloat($float) {
        return filter_var($float, FILTER_VALIDATE_FLOAT);
    }

    /**
     * Validate boolean
     */
    public static function validateBool($bool) {
        return filter_var($bool, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Sanitize string
     */
    public static function sanitizeString($string) {
        return filter_var($string, FILTER_SANITIZE_STRING);
    }

    /**
     * Sanitize email
     */
    public static function sanitizeEmail($email) {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize URL
     */
    public static function sanitizeUrl($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    /**
     * Sanitize integer
     */
    public static function sanitizeInt($int) {
        return filter_var($int, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitize float
     */
    public static function sanitizeFloat($float) {
        return filter_var($float, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }
} 