<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Enums\Status;
use App\Models\Task;
use App\Models\TaskManager;
use Ramsey\Uuid\Uuid;

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    try {
        $task = new Task();
        $createdTask = $task->create(new Task(
                title: $_POST['title'],
                description: $_POST['description'] ?? '',
                status: Status::NOT_COMPLETED,
                creatorId: Uuid::fromString($_SESSION['user_id'])
        ));

        $taskManager = new TaskManager();
        $taskManager->addTask(
                Uuid::fromString($_SESSION['user_id']),
                $createdTask->id
        );

        $success = "Завдання успішно створено!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_status') {
    try {
        $task = new Task();
        $task->changeStatus(
                Uuid::fromString($_POST['task_id']),
                Status::from($_POST['status'])
        );
        $success = "Статус змінено!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    try {
        $task = new Task();
        $task->delete(Uuid::fromString($_POST['task_id']));

        $taskManager = new TaskManager();
        $taskManager->deleteTask(Uuid::fromString($_POST['task_id']));

        $success = "Завдання видалено!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$tasks = [];
try {
    $taskManager = new TaskManager();
    $taskManagerRecords = $taskManager->getTasksByUser(Uuid::fromString($_SESSION['user_id']));

    foreach ($taskManagerRecords as $record) {
        $task = Task::findById(Uuid::fromString($record['task_id']));
        if ($task) {
            $tasks[] = $task;
        }
    }
} catch (Exception $e) {
    // Немає завдань
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мої завдання - Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="/">Task Manager</a>
        <div class="d-flex">
                <span class="navbar-text text-white me-3">
                    Привіт, <?= htmlspecialchars($_SESSION['username']) ?>!
                </span>
            <a href="/logout" class="btn btn-outline-light btn-sm">Вийти</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Мої завдання</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                    + Нове завдання
                </button>
            </div>

            <?php if (empty($tasks)): ?>
                <div class="alert alert-info">
                    У вас поки немає завдань. Створіть перше завдання!
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($tasks as $task): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($task->title) ?></h5>
                                    <p class="card-text"><?= htmlspecialchars($task->description) ?></p>

                                    <div class="mb-2">
                                        <?php
                                        $badgeClass = match($task->status) {
                                            Status::COMPLETED => 'bg-success',
                                            Status::ACCEPT => 'bg-primary',
                                            Status::NOT_ACCEPT => 'bg-warning',
                                            Status::NOT_COMPLETED => 'bg-secondary',
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                                <?= $task->status->value ?>
                                            </span>
                                    </div>

                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#statusModal<?= $task->id->toString() ?>">
                                            Змінити статус
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="if(confirm('Видалити завдання?')) document.getElementById('deleteForm<?= $task->id->toString() ?>').submit()">
                                            Видалити
                                        </button>
                                    </div>

                                    <form id="deleteForm<?= $task->id->toString() ?>" method="POST" style="display: none;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="task_id" value="<?= $task->id->toString() ?>">
                                    </form>
                                </div>

                                <div class="modal fade" id="statusModal<?= $task->id->toString() ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Змінити статус</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="action" value="change_status">
                                                    <input type="hidden" name="task_id" value="<?= $task->id->toString() ?>">

                                                    <select name="status" class="form-select">
                                                        <?php foreach (Status::cases() as $status): ?>
                                                            <option value="<?= $status->value ?>"
                                                                    <?= $task->status === $status ? 'selected' : '' ?>>
                                                                <?= $status->value ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                                                    <button type="submit" class="btn btn-primary">Зберегти</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Нове завдання</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Назва</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Опис</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                    <button type="submit" class="btn btn-primary">Створити</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>