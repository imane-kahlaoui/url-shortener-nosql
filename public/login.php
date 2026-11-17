<?php
require_once __DIR__ . '/../config/redis.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $userData = $redis->hGetAll("user:$email");

    if (!empty($userData) && password_verify($password, $userData['password'])) {
        $_SESSION['user'] = $email;
        $token = session_id();
        $redis->setex("session:$token", 3600, $email);

        if ($email === 'admin@gmail.com') {
            header('Location: /admin/admin.php');
        } else {
            header('Location: /public/dashboard.php');
        }
        exit;
    } else {
        $error = "Identifiants incorrects.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - ShortLink NoSQL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4">Connexion</h3>
                    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="/public/register.php">Créer un compte</a> | <a href="/public/index.php">Accueil</a>
                    </div>
                </div>
            </div