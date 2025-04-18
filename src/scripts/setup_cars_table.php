<?php

require_once __DIR__ . '/../config/database.php';

$csvFile = __DIR__ . '/../public/assets/data/cars.csv';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS cars (
            id SERIAL PRIMARY KEY,
            brand VARCHAR(50) NOT NULL,
            model VARCHAR(50) NOT NULL,
            year INTEGER NOT NULL,
            color VARCHAR(30) NOT NULL
        )
    ");

    if (!file_exists($csvFile)) {
        throw new Exception("Файл $csvFile не найден");
    }

    if (($handle = fopen($csvFile, 'r')) !== false) {
        fgetcsv($handle, 1000, ';');

        $stmt = $pdo->prepare("
            INSERT INTO cars (brand, model, year, color)
            VALUES (:brand, :model, :year, :color)
        ");

        while (($row = fgetcsv($handle, 1000, ';')) !== false) {
            $stmt->execute([
                ':brand' => $row[0],
                ':model' => $row[1],
                ':year' => (int)$row[2],
                ':color' => $row[3]
            ]);
        }

        fclose($handle);
        echo "Миграция завершена!\n";
    }
} catch (PDOException $e) {
    die("Ошибка миграции: " . $e->getMessage());
}
