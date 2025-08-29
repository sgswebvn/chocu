<?php

namespace App\Config;

class Database
{
    private $pdo;

    public function __construct()
    {
        // $host = $_ENV['DB_HOST'];
        // $dbname = $_ENV['DB_NAME'];
        // $user = $_ENV['DB_USER'];
        // $pass = $_ENV['DB_PASS'];

        //         DB_HOST=localhost
        // DB_NAME=c2c_marketplace
        // DB_USER=root
        // DB_PASS=
        // APP_URL=http://localhost:8080

        $host = 'localhost';
        $dbname = 'c2c_marketplace';
        $user = 'root';
        $pass = '';


        // Kết nối đến cơ sở dữ liệu


        try {
            $this->pdo = new \PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->pdo;
    }
}
