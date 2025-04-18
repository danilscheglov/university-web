<?php
session_start();

require_once __DIR__ . '/config/database.php';

$errors = $_SESSION['errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];
$success = $_SESSION['success'] ?? false;

unset($_SESSION['errors'], $_SESSION['form_data'], $_SESSION['success']);
?>

<!doctype html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/assets/css/main.css">
    <title>Добавить автомобиль</title>
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
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="form-container">
                    <div class="form-header">
                        <h3 class="text-center mb-0">Добавить автомобиль</h3>
                    </div>

                    <form action="form_handler.php" method="POST" class="p-4 compact-form" id="carForm" novalidate>
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                Данные успешно сохранены!
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="brand" class="form-label">Марка</label>
                            <input type="text"
                                class="form-control shadow-sm <?= !empty($errors['brand']) ? 'is-invalid' : '' ?>"
                                id="brand"
                                name="brand"
                                required
                                placeholder="Например, Toyota"
                                maxlength="50"
                                pattern="[\p{L}0-9\s\-]+"
                                title="Только буквы, цифры и дефисы (макс. 50 символов)"
                                value="<?= htmlspecialchars($formData['brand'] ?? '') ?>">
                            <?php if (!empty($errors['brand'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['brand']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="model" class="form-label">Модель</label>
                            <input type="text"
                                class="form-control shadow-sm <?= !empty($errors['model']) ? 'is-invalid' : '' ?>"
                                id="model"
                                name="model"
                                required
                                placeholder="Например, Camry XV70"
                                maxlength="50"
                                pattern="[\p{L}0-9\s\-\.]+"
                                title="Только буквы, цифры, точки и дефисы (макс. 50 символов)"
                                value="<?= htmlspecialchars($formData['model'] ?? '') ?>">
                            <?php if (!empty($errors['model'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['model']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="year" class="form-label">Год выпуска</label>
                            <input type="number"
                                class="form-control shadow-sm <?= !empty($errors['year']) ? 'is-invalid' : '' ?>"
                                id="year"
                                name="year"
                                min="1900"
                                max="<?= date('Y') + 1 ?>"
                                placeholder="Например, 2025"
                                value="<?= htmlspecialchars($formData['year'] ?? '') ?>">
                            <?php if (!empty($errors['year'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['year']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="color" class="form-label">Цвет</label>
                            <select class="form-select <?= !empty($errors['color']) ? 'is-invalid' : '' ?>"
                                id="color"
                                name="color"
                                required>
                                <option value="" disabled <?= empty($formData['color']) ? 'selected' : '' ?>>Выберите цвет...</option>
                                <?php foreach (
                                    [
                                        'Основные цвета' => ['Белый', 'Чёрный', 'Серый', 'Красный', 'Синий', 'Зелёный'],
                                        'Металлики' => ['Серебристый', 'Золотистый', 'Графитовый', 'Бронзовый', 'Платиновый'],
                                        'Пастельные тона' => ['Голубой', 'Розовый', 'Мятный', 'Лавандовый', 'Персиковый'],
                                        'Эксклюзивные цвета' => ['Хамелеон', 'Карбоновый', 'Жемчужный', 'Ультрамарин', 'Коралловый'],
                                        'Двухцветные комбинации' => ['Чёрно-белый', 'Красно-чёрный', 'Сине-серебристый', 'Оранжево-графитовый']
                                    ] as $group => $colors
                                ): ?>
                                    <optgroup label="<?= $group ?>">
                                        <?php foreach ($colors as $color): ?>
                                            <option value="<?= $color ?>"
                                                <?= ($formData['color'] ?? '') === $color ? 'selected' : '' ?>>
                                                <?= $color ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!empty($errors['color'])): ?>
                                <div class="invalid-feedback"><?= $errors['color'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg shadow-lg py-2">
                                Сохранить
                            </button>
                        </div>
                        <div id="message" class="mt-3 alert d-none"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>