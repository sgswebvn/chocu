<?php

namespace App\Config;

class Database
{
    private $pdo;

    public function __construct()
{
    $host = 'localhost';
    $dbname = 'c2c_marketplace';
    $user = 'root';
    $pass = '';

    try {
        $this->pdo = new \PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $user,
            $pass,
            [
                
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]
        );

        // DÒNG QUAN TRỌNG NHẤT – BẮT BUỘC CÓ
        $this->pdo->exec("SET time_zone = '+07:00'");

    } catch (\PDOException $e) {
        die("Kết nối thất bại: " . $e->getMessage());
    }
}

    public function getConnection()
    {
        return $this->pdo;
    }
}