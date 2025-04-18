<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

try {
    $stmt = $pdo->query("SELECT * FROM cars ORDER BY year DESC");
    $cars = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Ошибка получения данных: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/assets/css/main.css">
    <title>Список автомобилей</title>
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Автосалон</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="form.php">Добавить автомобиль</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cars.php">Список автомобилей</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container py-5 mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <div class="card-header border-0 pt-4">
                    <h3 class="card-title text-center mb-3">
                        Список автомобилей
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Марка</th>
                                    <th>Модель</th>
                                    <th>Год</th>
                                    <th>Цвет</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cars as $car): ?>
                                    <tr class="position-relative">
                                        <td><?= htmlspecialchars($car['brand']) ?></td>
                                        <td><?= htmlspecialchars($car['model']) ?></td>
                                        <td>
                                            <span class="badge bg-primary rounded-pill">
                                                <?= $car['year'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="color-preview"
                                                style="background-color: <?= getColorHex($car['color']) ?>">
                                            </div>
                                            <?= htmlspecialchars($car['color']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>