<?php
namespace App\Core;

class View {
    /**
     * Render view
     */
    public function render($view, $data = []) {
        // Extract data to make variables available in view
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include view file
        $view_file = $this->getViewFile($view);
        if (file_exists($view_file)) {
            require $view_file;
        } else {
            throw new \Exception("View file not found: {$view_file}");
        }
        
        // Get contents and clean buffer
        $content = ob_get_clean();
        
        // Include layout if exists
        $layout_file = $this->getLayoutFile();
        if (file_exists($layout_file)) {
            require $layout_file;
        } else {
            // If no layout, just output content
            echo $content;
        }
    }

    /**
     * Get view file path
     */
    protected function getViewFile($view) {
        // Convert dot notation to directory separator
        $view = str_replace('.', '/', $view);
        
        // Add .php extension if not present
        if (!preg_match('/\.php$/', $view)) {
            $view .= '.php';
        }
        
        // Return full path
        return dirname(__DIR__) . '/views/' . $view;
    }

    /**
     * Get layout file path
     */
    protected function getLayoutFile() {
        return dirname(__DIR__) . '/views/layouts/main.php';
    }

    /**
     * Escape HTML
     */
    public function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Format date
     */
    public function formatDate($date, $format = 'Y-m-d H:i:s') {
        if (!$date) {
            return '';
        }
        
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        return date($format, $timestamp);
    }

    /**
     * Format number
     */
    public function formatNumber($number, $decimals = 0) {
        return number_format($number, $decimals, '.', ',');
    }

    /**
     * Format file size
     */
    public function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Truncate text
     */
    public function truncate($text, $length = 100, $append = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        $text = substr($text, 0, $length);
        $text = substr($text, 0, strrpos($text, ' '));
        
        return $text . $append;
    }

    /**
     * Generate pagination
     */
    public function pagination($total, $per_page, $current_page, $url_pattern) {
        $total_pages = ceil($total / $per_page);
        
        if ($total_pages <= 1) {
            return '';
        }
        
        $html = '<ul class="pagination">';
        
        // Previous page
        if ($current_page > 1) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . sprintf($url_pattern, $current_page - 1) . '">Previous</a>';
            $html .= '</li>';
        } else {
            $html .= '<li class="page-item disabled">';
            $html .= '<span class="page-link">Previous</span>';
            $html .= '</li>';
        }
        
        // Page numbers
        $start = max(1, $current_page - 2);
        $end = min($total_pages, $current_page + 2);
        
        if ($start > 1) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . sprintf($url_pattern, 1) . '">1</a>';
            $html .= '</li>';
            
            if ($start > 2) {
                $html .= '<li class="page-item disabled">';
                $html .= '<span class="page-link">...</span>';
                $html .= '</li>';
            }
        }
        
        for ($i = $start; $i <= $end; $i++) {
            if ($i == $current_page) {
                $html .= '<li class="page-item active">';
                $html .= '<span class="page-link">' . $i . '</span>';
                $html .= '</li>';
            } else {
                $html .= '<li class="page-item">';
                $html .= '<a class="page-link" href="' . sprintf($url_pattern, $i) . '">' . $i . '</a>';
                $html .= '</li>';
            }
        }
        
        if ($end < $total_pages) {
            if ($end < $total_pages - 1) {
                $html .= '<li class="page-item disabled">';
                $html .= '<span class="page-link">...</span>';
                $html .= '</li>';
            }
            
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . sprintf($url_pattern, $total_pages) . '">' . $total_pages . '</a>';
            $html .= '</li>';
        }
        
        // Next page
        if ($current_page < $total_pages) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . sprintf($url_pattern, $current_page + 1) . '">Next</a>';
            $html .= '</li>';
        } else {
            $html .= '<li class="page-item disabled">';
            $html .= '<span class="page-link">Next</span>';
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        
        return $html;
    }

    /**
     * Generate breadcrumbs
     */
    public function breadcrumbs($items) {
        if (empty($items)) {
            return '';
        }
        
        $html = '<nav aria-label="breadcrumb">';
        $html .= '<ol class="breadcrumb">';
        
        foreach ($items as $i => $item) {
            $is_last = $i === count($items) - 1;
            
            if ($is_last) {
                $html .= '<li class="breadcrumb-item active" aria-current="page">';
                $html .= $item['label'];
                $html .= '</li>';
            } else {
                $html .= '<li class="breadcrumb-item">';
                $html .= '<a href="' . $item['url'] . '">' . $item['label'] . '</a>';
                $html .= '</li>';
            }
        }
        
        $html .= '</ol>';
        $html .= '</nav>';
        
        return $html;
    }

    /**
     * Generate alert
     */
    public function alert($message, $type = 'info') {
        $html = '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
        $html .= $message;
        $html .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
        $html .= '<span aria-hidden="true">&times;</span>';
        $html .= '</button>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Generate badge
     */
    public function badge($text, $type = 'secondary') {
        return '<span class="badge badge-' . $type . '">' . $text . '</span>';
    }

    /**
     * Generate button
     */
    public function button($text, $url = null, $type = 'primary', $size = null, $icon = null) {
        $classes = ['btn', 'btn-' . $type];
        
        if ($size) {
            $classes[] = 'btn-' . $size;
        }
        
        $html = '<a href="' . ($url ?: '#') . '" class="' . implode(' ', $classes) . '">';
        
        if ($icon) {
            $html .= '<i class="' . $icon . '"></i> ';
        }
        
        $html .= $text;
        $html .= '</a>';
        
        return $html;
    }

    /**
     * Generate icon
     */
    public function icon($name, $class = null) {
        $classes = ['fas', 'fa-' . $name];
        
        if ($class) {
            $classes[] = $class;
        }
        
        return '<i class="' . implode(' ', $classes) . '"></i>';
    }

    /**
     * Generate label
     */
    public function label($text, $type = 'default') {
        return '<span class="label label-' . $type . '">' . $text . '</span>';
    }

    /**
     * Generate progress bar
     */
    public function progress($percent, $type = 'primary', $striped = false, $animated = false) {
        $classes = ['progress-bar'];
        
        if ($type) {
            $classes[] = 'bg-' . $type;
        }
        
        if ($striped) {
            $classes[] = 'progress-bar-striped';
        }
        
        if ($animated) {
            $classes[] = 'progress-bar-animated';
        }
        
        $html = '<div class="progress">';
        $html .= '<div class="' . implode(' ', $classes) . '" role="progressbar" style="width: ' . $percent . '%" aria-valuenow="' . $percent . '" aria-valuemin="0" aria-valuemax="100">';
        $html .= $percent . '%';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
} 