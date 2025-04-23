<?php

namespace App;

use App\Controllers\CarController;

class Router
{
    private array $routes = [];
    private string $basePath;

    public function __construct()
    {
        $this->basePath = dirname($_SERVER['SCRIPT_NAME']);
        $this->initializeRoutes();
    }

    private function initializeRoutes(): void
    {
        $this->addRoute('GET', '/', [CarController::class, 'showForm']);
        $this->addRoute('GET', '/cars', [CarController::class, 'listCars']);
        $this->addRoute('POST', '/car/add', [CarController::class, 'addCar']);
    }

    public function addRoute(string $method, string $path, array $handler): void
    {
        $normalizedPath = $this->normalizePath($path);
        $this->routes[$method][$normalizedPath] = $handler;
    }

    private function normalizePath(string $path): string
    {
        $path = trim($path, '/');
        return $path === '' ? '/' : "/{$path}";
    }

    private function getRequestPath(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = substr($path, strlen($this->basePath));
        return $this->normalizePath($path);
    }

    private function dispatch(array $handler): void
    {
        [$controllerClass, $action] = $handler;

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller {$controllerClass} not found");
        }

        if (!method_exists($controllerClass, $action)) {
            throw new \RuntimeException("Method {$action} not found in {$controllerClass}");
        }

        $controller = new $controllerClass();
        $controller->$action();
    }

    public function handleRequest(): void
    {
        session_start();

        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestPath = $this->getRequestPath();

        foreach ($this->routes[$requestMethod] as $routePath => $handler) {
            if ($routePath === $requestPath) {
                $this->dispatch($handler);
                return;
            }
        }
    }
}
