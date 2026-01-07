<?php
/**
 * Router Class
 * Handles URL routing and controller dispatching
 */

class Router
{
    private $controller = 'DashboardController';
    private $method = 'index';
    private $params = [];

    /**
     * Constructor - Parse URL and route request
     */
    public function __construct()
    {
        $url = $this->parseUrl();

        // Check for controller
        if (isset($url[0]) && !empty($url[0])) {
            $controllerName = ucfirst($url[0]) . 'Controller';
            $controllerPath = APP_PATH . '/controllers/' . $controllerName . '.php';

            if (file_exists($controllerPath)) {
                $this->controller = $controllerName;
                unset($url[0]);
            }
        }

        // Require controller file
        require_once APP_PATH . '/controllers/' . $this->controller . '.php';

        // Instantiate controller
        $this->controller = new $this->controller;

        // Check for method
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            }
        }

        // Get params
        $this->params = $url ? array_values($url) : [];

        // Check if method exists and is callable
        if (method_exists($this->controller, $this->method) && is_callable([$this->controller, $this->method])) {
            call_user_func_array([$this->controller, $this->method], $this->params);
        } else {
            // Method not found - could show a 404 but for now we'll just redirect home
            header('Location: ' . APP_URL);
            exit;
        }
    }

    /**
     * Parse URL from request
     * 
     * @return array URL segments
     */
    private function parseUrl()
    {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
        return [];
    }
}
