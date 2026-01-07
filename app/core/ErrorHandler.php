<?php
/**
 * Global Error and Exception Handler
 */
class ErrorHandler
{
    /**
     * Register self as error/exception handler
     */
    public static function register()
    {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleFatalError']);
    }

    /**
     * Handle uncaught exceptions
     */
    public static function handleException(Throwable $e)
    {
        self::log($e);
        self::respond($e->getCode() ?: 500, $e->getMessage(), $e);
    }

    /**
     * Handle PHP errors
     */
    public static function handleError($level, $message, $file, $line)
    {
        if (error_reporting() !== 0) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Handle fatal errors
     */
    public static function handleFatalError()
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $e = new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
            self::log($e);
            self::respond(500, 'A fatal error occurred');
        }
    }

    /**
     * Log error details
     */
    private static function log(Throwable $e)
    {
        $logDir = dirname(dirname(__DIR__)) . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $message = "[" . date('Y-m-d H:i:s') . "] " . get_class($e) . ": " . $e->getMessage() .
            " in " . $e->getFile() . " on line " . $e->getLine() . "\n" .
            $e->getTraceAsString() . "\n\n";

        error_log($message, 3, $logDir . '/error.log');
    }

    /**
     * Send appropriate response to user
     */
    private static function respond($code, $message, $e = null)
    {
        if (!headers_sent()) {
            http_response_code((int) $code);
        }

        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => APP_DEBUG ? $message : 'A server error occurred. Please try again later.'
            ]);
        } else {
            // Check if we can show a nice error page
            $viewPath = dirname(__DIR__) . '/views/errors/' . $code . '.php';
            if (!file_exists($viewPath)) {
                $viewPath = dirname(__DIR__) . '/views/errors/500.php';
            }

            if (file_exists($viewPath)) {
                require_once $viewPath;
            } else {
                echo "<h1>Error $code</h1>";
                echo "<p>" . (APP_DEBUG ? $message : 'A server error occurred.') . "</p>";
            }
        }
        exit;
    }
}
