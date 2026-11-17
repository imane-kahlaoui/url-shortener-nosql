<?php
require_once __DIR__ . '/../config/redis.php';
require_once __DIR__ . '/../functions/riak.php';

if (!isset($_SESSION['user'])) {
    header('Location: /public/login.php');
    exit;
}

$userEmail = $_SESSION['user'];
$userLinksDoc = riak_get('user_links', $userEmail);
$codes = $userLinksDoc['codes'] ?? [];

$links = [];
foreach ($codes as $code) {
    $link = riak_get('links', $code);
    if ($link !== null) {
        $links[] = $link;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - ShortLink NoSQL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/public/index.php">ShortLink NoSQL</a>
        <div>
            <span class="text-white me-3"><?= htmlspecialchars($userEmail) ?></span>
            <?php if ($userEmail === 'admin@gmail.com'): ?>
                <a href="/admin/admin.php" class="btn btn-warning btn-sm me-2">Admin</a>
            <?php endif; ?>
            <a href="/public/index.php" class="btn btn-light btn-sm me-2">+ Nouveau lien</a>
            <a href="/public/logout.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <h3 class="mb-4">Mes liens raccourcis (<?= count($links) ?>)</h3>

    <div class="card shadow-sm">
        <div class="mb-3">
            <input type="text" id="tableSearch" class="form-control" placeholder="Rechercher un code, une URL ou un utilisateur...">
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code court</th>
                        <th>URL originale</th>
                        <th>Clics</th>
                        <th>Expiration</th>
                        <th>Dernier accès</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($links)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">Aucun lien créé pour le moment.</td></tr>
                <?php else: ?>
                    <?php foreach ($links as $l): ?>
                        <tr>
                            <td>
                                <span class="badge bg-primary"><?= htmlspecialchars($l['code_court']) ?></span>
                                <?php if (!empty($l['password'])): ?>
                                    <span class="badge bg-secondary">Protégé</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-truncate" style="max-width: 250px;">
                                <?= htmlspecialchars($l['original_url']) ?>
                            </td>
                            <td><strong><?= (int)($l['clicks'] ?? 0) ?></strong></td>
                            <td><?= $l['expiration'] ? htmlspecialchars($l['expiration']) : '<span class="text-success">Jamais</span>' ?></td>
                            <td class="small text-muted"><?= htmlspecialchars($l['last_ip'] ?? '-') ?></td>
                            <td>
                                <a href="/public/redirect.php?code=<?= urlencode($l['code_court']) ?>" target="_blank" class="btn btn-sm btn-outline-success">Ouvrir</a>
                                <a href="/public/edit.php?code=<?= urlencode($l['code_court']) ?>" class="btn btn-sm btn-outline-primary">Modifier</a>
                                <a href="/public/delete.php?code=<?= urlencode($l['code_court']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ce lien ?');">Supprimer</a>
      