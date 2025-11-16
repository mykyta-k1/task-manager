<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Database;
use DateTime;
use DateTimeImmutable;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class User
{
    public function __construct(
        public readonly ?UuidInterface $id = null,
        public string $username = '',
        public string $email = '',
        public string $password = '',
        public DateTimeImmutable $createdAt = new DateTimeImmutable('now'),
        public DateTime $updatedAt = new DateTime('now'),
    ) {
    }


    /**
     * @throws Exception
     */
    public function register(?string $username, string $email, string $password): User
    {
        $pdo = Database::getInstance()->getPDO();
        $password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            "INSERT INTO users (username, email, password) 
                        VALUES (:username, :email, :password) RETURNING *"
        );
        $stmt->execute(['username' => $username, 'email' => $email, 'password' => $password]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception("User not created");
        }

        return self::mapFromDb($user);
    }

    /**
     * @throws Exception
     */
    public function login(string $email, string $password): User
    {
        $pdo = Database::getInstance()->getPDO();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception("Invalid credentials");
        }

        return self::mapFromDb($user);
    }

    public function logout(): void
    {

    }

    /**
     * @throws Exception
     */
    public function changePassword(string $email, string $lastPassword, string $newPassword): void
    {
        $pdo = Database::getInstance()->getPDO();

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($lastPassword, $user['password'])) {
            throw new Exception("Invalid credentials");
        }

        $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
        $stmt->execute(['password' => $hashedNewPassword, 'email' => $email]);
    }

    /**
     * @throws \DateMalformedStringException
     */
    public static function mapFromDb(array $data): User
    {
        return new User(
            id: $data['id'] ? Uuid::fromString($data['id']) : null,
            username: $data['username'],
            email: $data['email'],
            password: $data['password'],
            createdAt: new DateTimeImmutable('now'),
            updatedAt: new DateTime($data['updated_at'])
        );
    }
}
