<?php

namespace App\Controllers;

use App\Config\Helpers;
use App\Models\CarModel;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class CarController
{
    private $twig;
    private $carModel;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../views');
        $this->twig = new Environment($loader, [
            'cache' => false,
            'debug' => true,
        ]);

        $this->twig->addGlobal('session', $_SESSION);

        $this->twig->addFunction(new TwigFunction('getColorHex', [Helpers::class, 'getColorHex']));
        $this->twig->addGlobal('session', $_SESSION);

        $this->carModel = new CarModel();
    }

    public function showForm(): void
    {
        $errors = $_SESSION['errors'] ?? [];
        $formData = $_SESSION['form_data'] ?? [];
        $success = $_SESSION['success'] ?? false;

        unset($_SESSION['errors'], $_SESSION['form_data'], $_SESSION['success']);

        echo $this->twig->render('car/form.twig', [
            'errors' => $errors,
            'formData' => $formData,
            'success' => $success,
            'colorGroups' => $this->carModel->getColorGroups()
        ]);
    }

    public function listCars(): void
    {
        try {
            $cars = $this->carModel->getAllCars();

            $success = $_SESSION['success'] ?? null;
            $error = $_SESSION['error'] ?? null;

            unset($_SESSION['success'], $_SESSION['error']);

            echo $this->twig->render('car/list.twig', [
                'cars' => $cars,
                'success' => $success,
                'error' => $error
            ]);
        } catch (\Exception $e) {
            echo $this->twig->render('error.twig', [
                'message' => $e->getMessage()
            ]);
        }
    }

    public function addCar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $brand = trim(htmlspecialchars($_POST['brand'] ?? ''));
            $model = trim(htmlspecialchars($_POST['model'] ?? ''));
            $year = isset($_POST['year']) ? (int)$_POST['year'] : 0;
            $color = trim(htmlspecialchars($_POST['color'] ?? ''));

            $errors = $this->carModel->validateCarData($brand, $model, $year, $color);

            $_SESSION['form_data'] = compact('brand', 'model', 'year', 'color');

            if (empty($errors)) {
                try {
                    $this->carModel->addCar($brand, $model, $year, $color);
                    $_SESSION['success'] = true;
                } catch (\Exception $e) {
                    $errors['database'] = $e->getMessage();
                }
            }

            $_SESSION['errors'] = $errors;
        }

        header('Location: /car/add');
        exit;
    }

    public function deleteCar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['car_id'])) {
            $carId = (int)$_POST['car_id'];

            try {
                if ($this->carModel->deleteCarById($carId)) {
                    $_SESSION['success'] = 'Автомобиль успешно удален';
                } else {
                    $_SESSION['error'] = 'Не удалось удалить автомобиль';
                }
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Ошибка при удалении автомобиля: ' . $e->getMessage();
            }
        }

        header('Location: /cars');
        exit;
    }
}
