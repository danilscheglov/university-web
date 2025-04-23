<?php

namespace App\Controllers;

use App\Models\UserModel;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extra\Intl\IntlExtension;

class AdminController
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

        $this->checkAdminAccess();
    }

    private function checkAdminAccess(): void
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            $this->renderAccessDenied();
            exit;
        }
    }

    private function renderAccessDenied(): void
    {
        echo $this->twig->render('error.twig', [
            'message' => 'Доступ запрещен. Необходимы права администратора.'
        ]);
    }

    public function listUsers(): void
    {
        $users = $this->userModel->getAllUsers();

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;

        // Очищаем сообщения после их получения
        unset($_SESSION['success'], $_SESSION['error']);

        echo $this->twig->render('admin/users.twig', [
            'users' => $users,
            'success' => $success,
            'error' => $error,
            'session' => $_SESSION
        ]);
    }

    public function deleteUser(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
            $userId = (int)$_POST['user_id'];

            try {
                $this->userModel->deleteUser($userId);
                $_SESSION['success'] = 'Пользователь успешно удален';
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Ошибка при удалении пользователя: ' . $e->getMessage();
            }
        }

        header('Location: /admin/users');
        exit;
    }

    public function changeUserRole(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['role'])) {
            $userId = (int)$_POST['user_id'];
            $role = $_POST['role'];

            try {
                $this->userModel->updateUserRole($userId, $role);
                $_SESSION['success'] = 'Роль пользователя успешно изменена';
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Ошибка при изменении роли: ' . $e->getMessage();
            }
        }

        header('Location: /admin/users');
        exit;
    }
}
