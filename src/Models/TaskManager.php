<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Database;
use Exception;
use Ramsey\Uuid\UuidInterface;

class TaskManager
{
    public function __construct(
        public readonly ?UuidInterface $id = null,
        public ?UuidInterface $userId = null,
        public ?UuidInterface $taskId = null,
    ) {
    }

    public function addTask(UuidInterface $userId, UuidInterface $taskId): void
    {
        $pdo = Database::getInstance()->getPdo();
        $stmt = $pdo->prepare("INSERT INTO task_managers (user_id, task_id) VALUES (:userId, :taskId)");
        $stmt->execute(['userId' => $userId->toString(), 'taskId' => $taskId->toString()]);
    }

    public function updateTask(UuidInterface $targetId, UuidInterface $taskId, UuidInterface $userId): void
    {
        $pdo = Database::getInstance()->getPdo();
        $stmt = $pdo->prepare("UPDATE task_managers SET user_id = :userId, task_id = :taskId WHERE id = :id");
        $stmt->execute(['taskId' => $taskId->toString(), 'userId' => $userId->toString(), 'id' => $targetId->toString()]);
    }

    public function deleteTask(UuidInterface $taskId): void
    {
        $pdo = Database::getInstance()->getPdo();
        $stmt = $pdo->prepare("DELETE FROM task_managers WHERE id = :id");
        $stmt->execute(['id' => $taskId->toString()]);
    }

    /**
     * @throws Exception
     */
    public function getTasksByUser(UuidInterface $userId): array
    {
        $pdo = Database::getInstance()->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM task_managers WHERE user_id = :userId");
        $stmt->execute(['userId' => $userId->toString()]);
        $tasks = $stmt->fetchAll();

        if (empty($tasks)) {
            throw new Exception("No tasks found");
        }

        return $tasks;
    }
}
