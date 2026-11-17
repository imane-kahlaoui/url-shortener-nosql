<?php
require_once __DIR__ . '/../config/redis.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || strlen($password) < 6) {
        $error = "Email invalide ou mot de passe trop court (minimum 6 caractères).";
    } elseif ($redis->exists("user:$email")) {
        $error = "Un compte existe déjà avec cet email.";
    } else {
        $redis->hMSet("user:$email", [
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);
        $success = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - ShortLink NoSQL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4">Créer un compte</h3>
                    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="/public/login.php">Déjà inscrit ? Se connecter</a> | <a href="/public/index.php">Accueil</a>
                    </div>
                </div>
            </div