<?php

namespace App\Controllers;

use App\Models\UserModel;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class AuthController
{
    private $twig;
    private $userModel;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../views');
        $this->twig = new Environment($loader, [
            'cache' => false,
            'debug' => true,
        ]);

        $this->twig->addGlobal('session', $_SESSION);

        $this->userModel = new UserModel();
    }

    public function showRegistrationForm(): void
    {
        $errors = $_SESSION['errors'] ?? [];
        $formData = $_SESSION['form_data'] ?? [];
        $success = $_SESSION['success'] ?? false;

        unset($_SESSION['errors'], $_SESSION['form_data'], $_SESSION['success']);

        echo $this->twig->render('auth/register.twig', [
            'errors' => $errors,
            'formData' => $formData,
            'success' => $success
        ]);
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim(htmlspecialchars($_POST['name'] ?? ''));
            $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';

            $errors = $this->validateRegistration($name, $email, $password, $passwordConfirm);
            $_SESSION['form_data'] = compact('name', 'email');

            if (empty($errors)) {
                try {
                    $this->userModel->createUser($name, $email, $password);
                    $_SESSION['success'] = true;
                    header('Location: /register');
                    exit;
                } catch (\Exception $e) {
                    $errors['database'] = $e->getMessage();
                }
            }

            $_SESSION['errors'] = $errors;
            header('Location: /register');
            exit;
        }
    }

    public function showLogin(): void
    {
        if (isset($_SESSION['user'])) {
            header('Location: /');
            exit;
        }

        $error = $_SESSION['error'] ?? null;
        $formData = $_SESSION['form_data'] ?? [];

        unset($_SESSION['error'], $_SESSION['form_data']);

        echo $this->twig->render('auth/login.twig', [
            'error' => $error,
            'formData' => $formData
        ]);
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
            $password = $_POST['password'] ?? '';

            $_SESSION['form_data'] = compact('email');

            if (empty($email) || empty($password)) {
                $_SESSION['error'] = 'Пожалуйста, заполните все поля';
                header('Location: /login');
                exit;
            }

            $user = $this->userModel->getUserByEmail($email);

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $_SESSION['error'] = 'Неверный email или пароль';
                header('Location: /login');
                exit;
            }

            unset($user['password_hash']);
            $_SESSION['user'] = $user;

            header('Location: /');
            exit;
        }
    }

    public function logout(): void
    {
        unset($_SESSION['user']);
        session_destroy();

        header('Location: /login');
        exit;
    }

    private function validateRegistration($name, $email, $password, $passwordConfirm): array
    {
        $errors = [];

        if (empty($name) || !preg_match('/^[\p{L}\s\-]{2,50}$/u', $name)) {
            $errors['name'] = 'Имя должно содержать только буквы, пробелы и дефисы (от 2 до 50 символов)';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Пожалуйста, введите корректный email';
        } elseif ($this->userModel->userExists($email)) {
            $errors['email'] = 'Пользователь с таким email уже существует';
        }

        if (empty($password) || strlen($password) < 8) {
            $errors['password'] = 'Пароль должен содержать минимум 8 символов';
        } elseif ($password !== $passwordConfirm) {
            $errors['password_confirm'] = 'Пароли не совпадают';
        }

        return $errors;
    }

    public function checkAuth(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
    }
}
