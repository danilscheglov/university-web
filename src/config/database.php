<?php

namespace App\Config;

use PDO;
use PDOException;
use Exception;

class Database
{
    private static $pdo = null;

    public static function getConnection()
    {
        if (self::$pdo === null) {
            try {
                $dbHost = getenv('DB_HOST');
                $dbName = getenv('DB_NAME');
                $dbUser = getenv('DB_USER');
                $dbPassword = getenv('DB_PASSWORD');

                $dsn = "pgsql:host=$dbHost;dbname=$dbName";

                self::$pdo = new PDO($dsn, $dbUser, $dbPassword, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $exception) {
                throw new Exception("Database connection error: " . $exception->getMessage());
            }
        }

        return self::$pdo;
    }
}
