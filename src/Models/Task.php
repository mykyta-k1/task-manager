<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Database;
use App\Enums\Status;
use DateMalformedStringException;
use DateTime;
use DateTimeImmutable;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Task
{
    public function __construct(
        public readonly ?UuidInterface $id = null,
        public string $title = '',
        public string $description = '',
        public Status $status = Status::NOT_COMPLETED,
        public readonly ?UuidInterface $creatorId = null,
        public ?UuidInterface $assignedToId = null,
        public DateTimeImmutable $createdAt = new DateTimeImmutable('now'),
        public DateTime $updatedAt = new DateTime('now'),
    ) {
    }

    /**
     * @throws Exception
     */
    public function create(Task $taskCreate): Task
    {
        $pdo = Database::getInstance()->getPDO();
        $stmt = $pdo->prepare(
            "INSERT INTO tasks (title, description, status, creator_id, assigned_to_id) 
            VALUES (:title, :description, :status, :creatorId, :assignedToId) RETURNING *"
        );
        $stmt->execute([
            'title' => $taskCreate->title,
            'description' => $taskCreate->description,
            'status' => $taskCreate->status->value,
            'creatorId' => $taskCreate->creatorId?->toString(),
            'assignedToId' => $taskCreate->assignedToId?->toString()
        ]);
        $task = $stmt->fetch();

        if (!$task) {
            throw new Exception("Task not created");
        }

        return self::mapFromDb($task);
    }

    /**
     * @throws Exception
     */
    public function update(UuidInterface $targetId, Task $taskUpdated): Task
    {
        $pdo = Database::getInstance()->getPDO();

        $stmt = $pdo->prepare(
            "UPDATE tasks 
         SET title = :title, 
             description = :description, 
             status = :status, 
             assigned_to_id = :assignedToId,
             updated_at = NOW()
         WHERE id = :id 
         RETURNING *"
        );
        $stmt->execute([
            'title' => $taskUpdated->title,
            'description' => $taskUpdated->description,
            'status' => $taskUpdated->status->value,
            'assignedToId' => $taskUpdated->assignedToId?->toString(),
            'id' => $targetId->toString()
        ]);

        $task = $stmt->fetch();

        if (!$task) {
            throw new Exception("Task not found");
        }

        return self::mapFromDb($task);
    }

    public function delete(UuidInterface $targetId): void
    {
        $pdo = Database::getInstance()->getPDO();
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$targetId->toString()]);
    }

    /**
     * @throws Exception
     */
    public function assignTo(UuidInterface $taskId, UuidInterface $userId): Task
    {
        $pdo = Database::getInstance()->getPDO();
        $stmt = $pdo->prepare("UPDATE tasks SET assigned_to_id = :assignedToId WHERE id = :id RETURNING *");
        $stmt->execute(['assignedToId' => $userId->toString(), 'id' => $taskId->toString()]);
        $task = $stmt->fetch();

        if (!$task) {
            throw new Exception("Task is not updated");
        }

        return self::mapFromDb($task);
    }

    /**
     * @throws Exception
     */
    public function changeStatus(UuidInterface $taskId, Status $status): Task
    {
        $pdo = Database::getInstance()->getPDO();
        $stmt = $pdo->prepare("UPDATE tasks SET status = :status WHERE id = :id RETURNING *");
        $stmt->execute(['status' => $status->value, 'id' => $taskId->toString()]);
        $task = $stmt->fetch();

        if (!$task) {
            throw new Exception("Task is not updated");
        }

        return self::mapFromDb($task);
    }

    /**
     * @throws DateMalformedStringException
     */
    public static function findById(UuidInterface $taskId): ?Task
    {
        $pdo = Database::getInstance()->getPDO();
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = :id");
        $stmt->execute(['id' => $taskId->toString()]);
        $task = $stmt->fetch();

        if (!$task) {
            return null;
        }

        return self::mapFromDb($task);
    }

    /**
     * @throws DateMalformedStringException
     */
    public static function mapFromDb(array $data): Task
    {
        return new Task(
            id: Uuid::fromString($data['id']),
            title: $data['title'],
            description: $data['description'],
            status: Status::from($data['status']),
            creatorId: $data['creator_id'] ? Uuid::fromString($data['creator_id']) : null,
            assignedToId: $data['assigned_to_id'] ? Uuid::fromString($data['assigned_to_id']) : null,
            createdAt: new DateTimeImmutable($data['created_at']),
            updatedAt: new DateTime($data['updated_at'])
        );
    }
}
