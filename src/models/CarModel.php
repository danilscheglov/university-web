<?php

namespace App\Models;

use PDOException;
use App\Core\Database;
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
        $this->initializeTable();
    }

    private function initializeTable(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS cars (
            id SERIAL PRIMARY KEY,
            brand VARCHAR(50) NOT NULL,
            model VARCHAR(50) NOT NULL,
            year INT NOT NULL,
            color VARCHAR(50) NOT NULL,
            user_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->pdo->exec($sql);
    }

    public function getAllCars(): array
    {
        try {
            if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
                $stmt = $this->pdo->query("
                    SELECT c.*, u.username as owner_name 
                    FROM cars c 
                    LEFT JOIN users u ON c.user_id = u.id 
                    ORDER BY c.year DESC
                ");
            } else {
                $stmt = $this->pdo->prepare("
                    SELECT c.*, u.username as owner_name 
                    FROM cars c 
                    LEFT JOIN users u ON c.user_id = u.id 
                    WHERE c.user_id = :user_id
                    ORDER BY c.year DESC
                ");
                $stmt->execute([':user_id' => $_SESSION['user']['id'] ?? 0]);
            }
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Ошибка получения данных: " . $e->getMessage());
        }
    }

    public function addCar($brand, $model, $year, $color): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO cars (brand, model, year, color, user_id)
                VALUES (:brand, :model, :year, :color, :user_id)
            ");

            return $stmt->execute([
                ':brand' => $brand,
                ':model' => $model,
                ':year' => $year,
                ':color' => $color,
                ':user_id' => $_SESSION['user']['id'] ?? null
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

    public function deleteCarById(int $id): bool
    {
        try {
            if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
                $stmt = $this->pdo->prepare("DELETE FROM cars WHERE id = :id");
                return $stmt->execute([':id' => $id]);
            } else {
                $stmt = $this->pdo->prepare("DELETE FROM cars WHERE id = :id AND user_id = :user_id");
                return $stmt->execute([
                    ':id' => $id,
                    ':user_id' => $_SESSION['user']['id'] ?? 0
                ]);
            }
        } catch (PDOException $e) {
            throw new Exception("Ошибка удаления: " . $e->getMessage());
        }
    }
}
