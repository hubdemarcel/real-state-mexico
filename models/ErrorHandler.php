<?php
/**
 * Error Handler Class
 *
 * Provides comprehensive error handling and logging
 */
class ErrorHandler {
    private static $instance = null;
    private $logFile;
    private $displayErrors;

    private function __construct() {
        $this->logFile = __DIR__ . '/../logs/errors.log';
        $this->displayErrors = getenv('APP_ENV') !== 'production';

        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Set up PHP error handling
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Handle PHP errors
     */
    public function handleError($errno, $errstr, $errfile, $errline) {
        $errorMessage = "PHP Error [$errno]: $errstr in $errfile on line $errline";
        $this->logError($errorMessage, 'ERROR');

        if ($this->displayErrors) {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 4px;'>";
            echo "<strong>PHP Error:</strong> $errstr<br>";
            echo "<strong>File:</strong> $errfile<br>";
            echo "<strong>Line:</strong> $errline";
            echo "</div>";
        } else {
            $this->showUserFriendlyError();
        }

        // Don't execute PHP's internal error handler
        return true;
    }

    /**
     * Handle uncaught exceptions
     */
    public function handleException($exception) {
        $errorMessage = "Uncaught Exception: " . $exception->getMessage() .
                       " in " . $exception->getFile() . " on line " . $exception->getLine();
        $this->logError($errorMessage, 'EXCEPTION');

        if ($this->displayErrors) {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 4px;'>";
            echo "<strong>Uncaught Exception:</strong> " . $exception->getMessage() . "<br>";
            echo "<strong>File:</strong> " . $exception->getFile() . "<br>";
            echo "<strong>Line:</strong> " . $exception->getLine() . "<br>";
            echo "<strong>Stack Trace:</strong><br><pre>" . $exception->getTraceAsString() . "</pre>";
            echo "</div>";
        } else {
            $this->showUserFriendlyError();
        }
    }

    /**
     * Handle shutdown errors
     */
    public function handleShutdown() {
        $error = error_get_last();
        if ($error !== null) {
            $errorMessage = "Shutdown Error: " . $error['message'] .
                           " in " . $error['file'] . " on line " . $error['line'];
            $this->logError($errorMessage, 'SHUTDOWN');

            if (!$this->displayErrors) {
                $this->showUserFriendlyError();
            }
        }
    }

    /**
     * Log error to file
     */
    private function logError($message, $type = 'ERROR') {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$type] $message" . PHP_EOL;

        // Don't log sensitive information
        $logMessage = $this->sanitizeLogMessage($logMessage);

        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    /**
     * Sanitize log messages to remove sensitive data
     */
    private function sanitizeLogMessage($message) {
        // Remove passwords, tokens, etc.
        $patterns = [
            '/password[^=]*=([^&\s]+)/i' => 'password=***',
            '/token[^=]*=([^&\s]+)/i' => 'token=***',
            '/key[^=]*=([^&\s]+)/i' => 'key=***',
            '/secret[^=]*=([^&\s]+)/i' => 'secret=***'
        ];

        foreach ($patterns as $pattern => $replacement) {
            $message = preg_replace($pattern, $replacement, $message);
        }

        return $message;
    }

    /**
     * Show user-friendly error page
     */
    private function showUserFriendlyError() {
        if (!headers_sent()) {
            http_response_code(500);
        }

        echo "<!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Error - Tierras.mx</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f8f9fa;
                    margin: 0;
                    padding: 0;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                }
                .error-container {
                    background: white;
                    padding: 2rem;
                    border-radius: 10px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    text-align: center;
                    max-width: 500px;
                    width: 90%;
                }
                .error-icon {
                    font-size: 4rem;
                    color: #dc3545;
                    margin-bottom: 1rem;
                }
                .error-title {
                    color: #333;
                    margin-bottom: 1rem;
                }
                .error-message {
                    color: #666;
                    margin-bottom: 2rem;
                }
                .error-actions {
                    display: flex;
                    gap: 1rem;
                    justify-content: center;
                }
                .btn {
                    padding: 0.75rem 1.5rem;
                    border: none;
                    border-radius: 5px;
                    text-decoration: none;
                    font-weight: 500;
                    cursor: pointer;
                    transition: background-color 0.3s;
                }
                .btn-primary {
                    background: #007bff;
                    color: white;
                }
                .btn-primary:hover {
                    background: #0056b3;
                }
                .btn-secondary {
                    background: #6c757d;
                    color: white;
                }
                .btn-secondary:hover {
                    background: #545b62;
                }
            </style>
        </head>
        <body>
            <div class='error-container'>
                <div class='error-icon'>⚠️</div>
                <h1 class='error-title'>¡Oops! Algo salió mal</h1>
                <p class='error-message'>
                    Ha ocurrido un error inesperado. Nuestro equipo ha sido notificado y estamos trabajando para solucionarlo.
                </p>
                <div class='error-actions'>
                    <a href='javascript:history.back()' class='btn btn-secondary'>← Regresar</a>
                    <a href='/' class='btn btn-primary'>Ir al Inicio</a>
                </div>
            </div>
        </body>
        </html>";
        exit();
    }

    /**
     * Handle database errors
     */
    public static function handleDatabaseError($error, $query = null) {
        $instance = self::getInstance();

        $errorMessage = "Database Error: " . $error;
        if ($query) {
            $errorMessage .= " | Query: " . $instance->sanitizeLogMessage($query);
        }

        $instance->logError($errorMessage, 'DATABASE');

        if ($instance->displayErrors) {
            throw new Exception("Database Error: " . $error);
        } else {
            $instance->showUserFriendlyError();
        }
    }

    /**
     * Handle validation errors
     */
    public static function handleValidationError($errors) {
        $instance = self::getInstance();

        $errorMessage = "Validation Error: " . implode(', ', $errors);
        $instance->logError($errorMessage, 'VALIDATION');

        // Return validation errors to be handled by the controller
        return $errors;
    }

    /**
     * Handle authentication errors
     */
    public static function handleAuthError($message = 'Authentication failed') {
        $instance = self::getInstance();

        $instance->logError("Authentication Error: $message", 'AUTH');

        if (!headers_sent()) {
            header('Location: /login.html?status=error&message=' . urlencode($message));
        }
        exit();
    }

    /**
     * Handle permission errors
     */
    public static function handlePermissionError($message = 'Access denied') {
        $instance = self::getInstance();

        $instance->logError("Permission Error: $message", 'PERMISSION');

        if (!headers_sent()) {
            http_response_code(403);
        }

        echo "<!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Acceso Denegado - Tierras.mx</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 2rem; }
                .error-container { max-width: 500px; margin: 0 auto; }
            </style>
        </head>
        <body>
            <div class='error-container'>
                <h1>🚫 Acceso Denegado</h1>
                <p>$message</p>
                <a href='/'>Regresar al inicio</a>
            </div>
        </body>
        </html>";
        exit();
    }
}

// Initialize error handler
ErrorHandler::getInstance();
?>