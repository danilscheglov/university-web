<?php
session_start();

require_once __DIR__ . '/config/database.php';

$allowedColors = [
    'Белый',
    'Чёрный',
    'Серый',
    'Красный',
    'Синий',
    'Зелёный',
    'Серебристый',
    'Золотистый',
    'Графитовый',
    'Бронзовый',
    'Платиновый',
    'Голубой',
    'Розовый',
    'Мятный',
    'Лавандовый',
    'Персиковый',
    'Хамелеон',
    'Карбоновый',
    'Жемчужный',
    'Ультрамарин',
    'Коралловый',
    'Чёрно-белый',
    'Красно-чёрный',
    'Сине-серебристый',
    'Оранжево-графитовый'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [
        'brand' => '',
        'model' => '',
        'year' => '',
        'color' => ''
    ];

    $brand = trim(htmlspecialchars($_POST['brand'] ?? ''));
    $model = trim(htmlspecialchars($_POST['model'] ?? ''));
    $year = isset($_POST['year']) ? (int)$_POST['year'] : 0;
    $color = trim(htmlspecialchars($_POST['color'] ?? ''));

    if (empty($brand) || !preg_match('/^[\p{L}0-9\s\-]{1,50}$/u', $brand)) {
        $errors['brand'] = 'Некорректное название марки';
    }

    if (empty($model) || !preg_match('/^[\p{L}0-9\s\-\.]{1,50}$/u', $model)) {
        $errors['model'] = 'Некорректное название модели';
    }

    $currentYear = date('Y');
    if ($year < 1900 || $year > $currentYear + 1 || strlen((string)$year) !== 4) {
        $errors['year'] = "Год должен быть 4-значным (1900-" . ($currentYear + 1) . ")";
    }

    if (empty($color) || !in_array($color, $allowedColors, true)) {
        $errors['color'] = 'Выберите цвет из списка';
    }

    $_SESSION['form_data'] = compact('brand', 'model', 'year', 'color');
    $_SESSION['errors'] = array_filter($errors);

    if (empty(array_filter($errors))) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO cars (brand, model, year, color)
                VALUES (:brand, :model, :year, :color)
            ");
            $stmt->execute([
                ':brand' => $brand,
                ':model' => $model,
                ':year' => $year,
                ':color' => $color
            ]);

            $_SESSION['success'] = true;
        } catch (PDOException $e) {
            $_SESSION['errors']['database'] = 'Ошибка сохранения: ' . $e->getMessage();
        }
    }

    header('Location: form.php');
    exit;
}

header('Location: form.php');
