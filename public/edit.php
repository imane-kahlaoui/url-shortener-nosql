<?php
require_once __DIR__ . '/../config/redis.php';
require_once __DIR__ . '/../functions/riak.php';

if (!isset($_SESSION['user'])) {
    header('Location: /public/login.php');
    exit;
}

$code = $_GET['code'] ?? '';
$link = riak_get('links', $code);

if (!$link || ($link['id_utilisateur'] ?? '') !== $_SESSION['user']) {
    die("Lien introuvable ou accès refusé.");
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUrl = trim($_POST['original_url'] ?? '');
    $newExp = trim($_POST['expiration'] ?? '');
    $newPass = $_POST['link_password'] ?? '';

    if (!filter_var($newUrl, FILTER_VALIDATE_URL)) {
        $error = "URL invalide.";
    } else {
        $link['original_url'] = $newUrl;
        $link['expiration'] = ($newExp !== '') ? gmdate('Y-m-d\TH:i:s\Z', strtotime($newExp)) : null;
        if ($newPass !== '') {
            $link['password'] = password_hash($newPass, PASSWORD_DEFAULT);
        } elseif (isset($_POST['remove_password'])) {
            $link['password'] = null;
        }

        riak_set('links', $code, $link);
        header('Location: /public/dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le lien</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h4>Modifier le lien : <?= htmlspecialchars($code) ?></h4>
                    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">URL originale</label>
                            <input type="url" name="original_url" class="form-control" value="<?= htmlspecialchars($link['original_url']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nouvelle date d'expiration (laisser vide pour illimité)</label>
                            <input type="datetime-local" name="expiration" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nouveau mot de passe (optionnel)</label>
                            <input type="password" name="link_password" class="form-control">
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" name="remove_password" class="form-check-input" id="rmPass">
                            <label class="form-check-label" for="rmPass">Supprimer la protection par mot de passe</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <a href="/public/dashboard.php" class="btn btn-secondary">Annuler</a>
                    </form>
                </div>
            </div