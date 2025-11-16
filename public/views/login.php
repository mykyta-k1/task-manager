<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Models\User;

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user = new User();
        $loggedUser = $user->login($_POST['email'], $_POST['password']);

        // TODO: Зберегти user в сесії
        session_start();
        $_SESSION['user_id'] = $loggedUser->id->toString();
        $_SESSION['username'] = $loggedUser->username;

        header('Location: /tasks');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вхід - Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="/">Task Manager</a>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center" style="min-height: 80vh; align-items: center;">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Вхід</h2>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Пароль</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Увійти</button>
                    </form>

                    <div class="text-center mt-3">
                        <p>Немає акаунту? <a href="/register">Зареєструватися</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>