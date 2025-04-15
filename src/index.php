<?php

try {
    $dbHost = getenv('DB_HOST');
    $dbName = getenv('DB_NAME');
    $dbUser = getenv('DB_USER');
    $dbPassword = getenv('DB_PASSWORD');

    $dsn = "pgsql:host=$dbHost;dbname=$dbName";

    $pdo = new PDO($dsn, $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "<h2>✅ Соединение с PostgreSQL установлено успешно!</h2>";
    echo "<p>Подключен к базе данных: <strong>{$dbName}</strong></p>";
} catch (PDOException $exception) {
    echo "<h2>❌ Не удалось установить соединение: " . $exception->getMessage() . "</h2>";
}

phpinfo();
