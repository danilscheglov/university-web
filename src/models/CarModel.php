<?php

namespace App\Models;

use PDO;
use PDOException;
use App\Config\Database;
use Exception;

class CarModel
{
    private $pdo;

    private $allowedColors = [
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

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function getAllCars(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM cars ORDER BY year DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Ошибка получения данных: " . $e->getMessage());
        }
    }

    public function addCar($brand, $model, $year, $color): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO cars (brand, model, year, color)
                VALUES (:brand, :model, :year, :color)
            ");

            return $stmt->execute([
                ':brand' => $brand,
                ':model' => $model,
                ':year' => $year,
                ':color' => $color
            ]);
        } catch (PDOException $e) {
            throw new Exception("Ошибка сохранения: " . $e->getMessage());
        }
    }

    public function validateCarData($brand, $model, $year, $color): array
    {
        $errors = [];

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

        if (empty($color) || !in_array($color, $this->allowedColors, true)) {
            $errors['color'] = 'Выберите цвет из списка';
        }

        return $errors;
    }

    public function getColorGroups(): array
    {
        return [
            'Основные цвета' => ['Белый', 'Чёрный', 'Серый', 'Красный', 'Синий', 'Зелёный'],
            'Металлики' => ['Серебристый', 'Золотистый', 'Графитовый', 'Бронзовый', 'Платиновый'],
            'Пастельные тона' => ['Голубой', 'Розовый', 'Мятный', 'Лавандовый', 'Персиковый'],
            'Эксклюзивные цвета' => ['Хамелеон', 'Карбоновый', 'Жемчужный', 'Ультрамарин', 'Коралловый'],
            'Двухцветные комбинации' => ['Чёрно-белый', 'Красно-чёрный', 'Сине-серебристый', 'Оранжево-графитовый']
        ];
    }
}
