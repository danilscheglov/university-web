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

        $this->twig->addFunction(new TwigFunction('getColorHex', [Helpers::class, 'getColorHex']));

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

            echo $this->twig->render('car/list.twig', [
                'cars' => $cars
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

        header('Location: /');
        exit;
    }
}
