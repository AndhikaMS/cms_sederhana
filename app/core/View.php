<?php
namespace App\Core;

class View {
    /**
     * Render view
     */
    public function render($view, $data = []) {
        // Extract data to make variables available in view
        extract($data);
        
        // Start output buffering for the view content
        ob_start();
        
        // Include view file
        $view_file = $this->getViewFile($view);
        if (file_exists($view_file)) {
            require $view_file;
        } else {
            throw new \Exception("View file not found: {$view_file}");
        }
        
        // Get contents of the view and clean buffer
        $content = ob_get_clean();
        
        // Include the main layout file, passing the $content variable
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
        // Default layout file. If you have different layouts (e.g., admin, public),
        // you might want to extend this logic (e.g., pass layout name to render method)
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
        
        foreach ($items as $label => $url) {
            if ($url) {
                $html .= '<li class="breadcrumb-item"><a href="' . $url . '">' . $this->escape($label) . '</a></li>';
            } else {
                $html .= '<li class="breadcrumb-item active" aria-current="page">' . $this->escape($label) . '</li>';
            }
        }
        
        $html .= '</ol>';
        $html .= '</nav>';
        
        return $html;
    }

    /**
     * Display a simple alert message.
     * @param string $message The message to display.
     * @param string $type The type of alert (e.g., 'success', 'danger', 'warning', 'info').
     */
    public function alert($message, $type = 'info') {
        echo '<div class="alert alert-' . $this->escape($type) . ' alert-dismissible fade show" role="alert">';
        echo $this->escape($message);
        echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
        echo '<span aria-hidden="true">&times;</span>';
        echo '</button>';
        echo '</div>';
    }

    /**
     * Generate a Bootstrap badge.
     * @param string $text The text inside the badge.
     * @param string $type The badge type (e.g., 'primary', 'secondary', 'success').
     * @return string The HTML for the badge.
     */
    public function badge($text, $type = 'secondary') {
        return '<span class="badge badge-' . $this->escape($type) . '">' . $this->escape($text) . '</span>';
    }

    /**
     * Generate a Bootstrap button.
     * @param string $text The button text.
     * @param string|null $url The URL for the button (if it's a link).
     * @param string $type The button type (e.g., 'primary', 'secondary', 'danger').
     * @param string|null $size The button size (e.g., 'sm', 'lg').
     * @param string|null $icon Font Awesome icon class (e.g., 'fas fa-plus').
     * @return string The HTML for the button.
     */
    public function button($text, $url = null, $type = 'primary', $size = null, $icon = null) {
        $class = "btn btn-{$type}";
        if ($size) {
            $class .= " btn-{$size}";
        }
        
        $iconHtml = $icon ? "<i class=\"{$this->escape($icon)}\"></i> " : '';
        
        if ($url) {
            return "<a href=\"{$this->escape($url)}\" class=\"{$this->escape($class)}\">{$iconHtml}{$this->escape($text)}</a>";
        } else {
            return "<button type=\"submit\" class=\"{$this->escape($class)}\">{$iconHtml}{$this->escape($text)}</button>";
        }
    }

    /**
     * Generate a Font Awesome icon.
     * @param string $name The icon name (e.g., 'fas fa-home').
     * @param string|null $class Additional CSS classes.
     * @return string The HTML for the icon.
     */
    public function icon($name, $class = null) {
        $fullClass = $this->escape($name);
        if ($class) {
            $fullClass .= ' ' . $this->escape($class);
        }
        return '<i class="' . $fullClass . '"></i>';
    }

    /**
     * Generate a Bootstrap label (similar to badge).
     * @param string $text The label text.
     * @param string $type The label type (e.g., 'default', 'primary').
     * @return string The HTML for the label.
     */
    public function label($text, $type = 'default') {
        // Bootstrap 4 uses badges, so this might be an old helper.
        // For compatibility, we'll map it to badges.
        return $this->badge($text, $type);
    }

    /**
     * Generate a Bootstrap progress bar.
     * @param int $percent The percentage of progress.
     * @param string $type The progress bar type (e.g., 'primary', 'success').
     * @param bool $striped Whether the progress bar is striped.
     * @param bool $animated Whether the progress bar is animated.
     * @return string The HTML for the progress bar.
     */
    public function progress($percent, $type = 'primary', $striped = false, $animated = false) {
        $class = "progress-bar bg-{$type}";
        if ($striped) {
            $class .= " progress-bar-striped";
        }
        if ($animated) {
            $class .= " progress-bar-animated";
        }
        
        return '<div class="progress" style="height: 20px;">' .
               '<div class="' . $this->escape($class) . '" role="progressbar" style="width: ' . $percent . '%" aria-valuenow="' . $percent . '" aria-valuemin="0" aria-valuemax="100"></div>' .
               '</div>';
    }
}