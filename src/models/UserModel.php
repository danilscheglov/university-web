<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class UserModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->initializeTable();
    }

    private function initializeTable(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(20) NOT NULL DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CHECK (role IN ('user', 'admin'))
        )";

        $this->db->exec($sql);

        $stmt = $this->db->query("SELECT COUNT(*) FROM users");
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            $this->createUser('Данил Щеглов', 'danilscheglov@admin.com', 'admin', 'admin');
        }
    }

    public function createUser($username, $email, $password, $role = 'user')
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password_hash, role)
                VALUES (:username, :email, :password, :role)
            ");

            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':role' => $role
            ]);

            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            throw new Exception("Ошибка при создании пользователя: " . $e->getMessage());
        }
    }

    public function getUserByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function userExists($email)
    {
        return (bool)$this->getUserByEmail($email);
    }

    public function getAllUsers()
    {
        $stmt = $this->db->query("SELECT id, username, email, role, created_at FROM users ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteUser($id)
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id AND role != 'admin'");
        return $stmt->execute([':id' => $id]);
    }

    public function updateUserRole($id, $role)
    {
        if (!in_array($role, ['user', 'admin'])) {
            throw new Exception("Недопустимая роль");
        }

        $stmt = $this->db->prepare("UPDATE users SET role = :role WHERE id = :id");
        return $stmt->execute([
            ':id' => $id,
            ':role' => $role
        ]);
    }
}
