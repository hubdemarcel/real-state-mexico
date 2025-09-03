<?php
/**
 * Base Controller Class
 *
 * Provides common functionality for all controllers
 */
abstract class BaseController {
    protected $conn;
    protected $user;

    public function __construct($conn = null) {
        $this->conn = $conn ?: getDatabaseConnection();

        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Get current user if logged in
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
            $this->user = [
                'id' => $_SESSION['id'],
                'username' => $_SESSION['username'],
                'user_type' => $_SESSION['user_type']
            ];
        }
    }

    /**
     * Check if user is authenticated
     */
    protected function requireAuth() {
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            $this->redirect('/login.html?status=error&message=Debes iniciar sesión para acceder a esta página.');
        }
    }

    /**
     * Check if user has specific role
     */
    protected function requireRole($role) {
        $this->requireAuth();
        if ($_SESSION['user_type'] !== $role) {
            $this->redirect('/user_dashboard.php?status=error&message=No tienes permisos para acceder a esta página.');
        }
    }

    /**
     * Redirect to URL
     */
    protected function redirect($url) {
        header('Location: ' . $url);
        exit();
    }

    /**
     * Render a view with data
     */
    protected function render($view, $data = []) {
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($viewPath)) {
            extract($data);
            require $viewPath;
        } else {
            throw new Exception("View file not found: $view");
        }
    }

    /**
     * Get POST data with optional sanitization
     */
    protected function getPostData($key = null, $sanitize = true) {
        if ($key === null) {
            $data = $_POST;
        } else {
            $data = $_POST[$key] ?? null;
        }

        if ($sanitize && is_string($data)) {
            return $this->sanitizeInput($data);
        } elseif ($sanitize && is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }

        return $data;
    }

    /**
     * Sanitize input data
     */
    protected function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate required fields
     */
    protected function validateRequired($data, $requiredFields) {
        $errors = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $errors[] = "El campo '$field' es requerido.";
            }
        }
        return $errors;
    }

    /**
     * Handle errors gracefully
     */
    protected function handleError($message, $redirectUrl = null) {
        error_log("Application Error: " . $message);

        if ($redirectUrl) {
            $this->redirect($redirectUrl . '?status=error&message=' . urlencode($message));
        } else {
            // Show user-friendly error page
            http_response_code(500);
            echo "<h1>Error</h1><p>Ha ocurrido un error. Por favor, inténtelo de nuevo más tarde.</p>";
            exit();
        }
    }
}
?>