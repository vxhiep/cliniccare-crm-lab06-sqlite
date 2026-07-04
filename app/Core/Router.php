<?php
class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, array $handler): void
    {
        $this->routes[$method][$this->normalizePath($path)] = $handler;
    }

    public function dispatch(string $method, string $path): void
    {
        $method = strtoupper($method);
        $path = $this->normalizePath($path);

        if (isset($this->routes[$method][$path])) {
            $this->callHandler($this->routes[$method][$path]);
            return;
        }

        $allowedMethods = $this->allowedMethods($path);
        if (!empty($allowedMethods)) {
            header('Allow: ' . implode(', ', $allowedMethods));
            http_response_code(405);
            render('errors/405', [
                'title' => '405 Method Not Allowed',
                'allowedMethods' => $allowedMethods,
            ]);
        }

        http_response_code(404);
        render('errors/404', ['title' => '404 Not Found']);
    }

    private function callHandler(array $handler): void
    {
        [$controllerClass, $action] = $handler;
        $container = $GLOBALS['container'] ?? [];

        if (!class_exists($controllerClass)) {
            throw new RuntimeException('Controller not found.');
        }

        $controller = isset($container[$controllerClass])
            ? $container[$controllerClass]()
            : new $controllerClass();

        if (!method_exists($controller, $action)) {
            throw new RuntimeException('Action not found.');
        }

        $controller->$action();
    }

    private function allowedMethods(string $path): array
    {
        $allowed = [];
        foreach ($this->routes as $method => $paths) {
            if (isset($paths[$path])) {
                $allowed[] = $method;
            }
        }
        return $allowed;
    }

    private function normalizePath(string $path): string
    {
        $path = parse_url($path, PHP_URL_PATH) ?: '/';
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }
        return $path;
    }
}
