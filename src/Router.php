<?php

namespace App;

use App\Controllers\CarController;
use App\Controllers\AuthController;
use App\Controllers\AdminController;

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
        $this->addRoute('GET', '/register', [AuthController::class, 'showRegistrationForm']);
        $this->addRoute('POST', '/register', [AuthController::class, 'register']);
        $this->addRoute('GET', '/login', [AuthController::class, 'showLogin']);
        $this->addRoute('POST', '/login', [AuthController::class, 'login']);
        $this->addRoute('GET', '/logout', [AuthController::class, 'logout']);

        $this->addRoute('GET', '/', [CarController::class, 'listCars']);
        $this->addRoute('GET', '/cars', [CarController::class, 'listCars']);

        $this->addRoute('GET', '/car/add', [CarController::class, 'showForm']);
        $this->addRoute('POST', '/car/add', [CarController::class, 'addCar']);

        $this->addRoute('POST', '/car/delete', [CarController::class, 'deleteCar']);

        $this->addRoute('GET', '/admin/users', [AdminController::class, 'listUsers']);
        $this->addRoute('POST', '/admin/user/delete', [AdminController::class, 'deleteUser']);
        $this->addRoute('POST', '/admin/user/role', [AdminController::class, 'changeUserRole']);
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

    private function dispatch(array $handler, $requireAuth = true): void
    {
        [$controllerClass, $action] = $handler;

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller {$controllerClass} not found");
        }

        if (!method_exists($controllerClass, $action)) {
            throw new \RuntimeException("Method {$action} not found in {$controllerClass}");
        }

        if (
            $requireAuth && $controllerClass !== AuthController::class &&
            $action !== 'showLogin' && $action !== 'login' && $action !== 'showRegistrationForm' && $action !== 'register'
        ) {
            $authController = new AuthController();
            $authController->checkAuth();
        }

        $controller = new $controllerClass();
        $controller->$action();
    }

    public function handleRequest(): void
    {
        session_start();

        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestPath = $this->getRequestPath();

        if (isset($this->routes[$requestMethod][$requestPath])) {
            $handler = $this->routes[$requestMethod][$requestPath];

            $requireAuth = true;
            if (($handler[0] === AuthController::class && in_array($handler[1], ['showLogin', 'login', 'showRegistrationForm', 'register']))) {
                $requireAuth = false;
            }

            $this->dispatch($handler, $requireAuth);
            return;
        }

        header('HTTP/1.1 404 Not Found');
        echo $this->renderNotFound();
    }

    private function renderNotFound(): string
    {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/views');
        $twig = new \Twig\Environment($loader, ['cache' => false]);

        return $twig->render('error.twig', [
            'message' => 'Страница не найдена'
        ]);
    }
}
