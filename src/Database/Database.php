<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

class Database
{
    private static ?Database $instance = null;
    private readonly PDO $pdo;

    private function __construct()
    {
        $dsn = "pgsql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'];
        $this->pdo = new PDO(
            $dsn,
            $_ENV['DB_USER'],
            $_ENV['DB_PASSWORD']
        );
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPDO(): PDO
    {
        return $this->pdo;
    }
}
