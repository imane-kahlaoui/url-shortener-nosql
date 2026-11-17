<?php
require_once __DIR__ . '/../config/redis.php';
require_once __DIR__ . '/../functions/riak.php';

if (($_SESSION['user'] ?? '') !== 'admin@gmail.com') {
    http_response_code(403);
    die("Accès réservé à l'administrateur.");
}

// Récupération des utilisateurs depuis Redis
$userKeys = $redis->keys('user:*');
$users = [];
foreach ($userKeys as $k) {
    $users[] = $redis->hGetAll($k);
}

// Récupération de tous les liens depuis Riak
$linkKeys = riak_list_keys('links');
$allLinks = [];
$totalClicks = 0;

foreach ($linkKeys as $code) {
    $doc = riak_get('links', $code);
    if ($doc) {
        $allLinks[] = $doc;
        $totalClicks += (int)($doc['clicks'] ?? 0);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Panneau Administrateur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/admin/admin.php">Administration - ShortLink</a>
        <div>
            <a href="/public/index.php" class="btn btn-outline-light btn-sm me-2">Accueil</a>
            <a href="/public/logout.php" class="btn btn-danger btn-sm">Déconnexion</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <!-- Cartes de statistiques globales -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card text-bg-primary shadow-sm">
                <div class="card-body">
                    <h6>Utilisateurs inscrits (Redis)</h6>
                    <h2><?= count($users) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-success shadow-sm">
                <div class="card-body">
                    <h6>Total des liens (Riak)</h6>
                    <h2><?= count($allLinks) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-warning shadow-sm">
                <div class="card-body">
                    <h6>Total des clics</h6>
                    <h2><?= $totalClicks ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des liens -->
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold">Tous les liens de la plateforme</div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>URL Originale</th>
                        <th>Auteur</th>
                        <th>Clics</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($allLinks as $l): ?>
                    <tr>
                        <td><span class="badge bg-dark"><?= htmlspecialchars($l['code_court']) ?></span></td>
                        <td class="text-truncate" style="max-width: 300px;"><?= htmlspecialchars($l['original_url']) ?></td>
                        <td><?= htmlspecialchars($l['id_utilisateur'] ?? 'Anonyme') ?></td>
                        <td><?= (int)($l['clicks'] ?? 0) ?></td>
                        <td>
                            <a href="/admin/admin_delete.php?code=<?= urlencode($l['code_court']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce lien ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Liste des utilisateurs -->
    <div class="card shadow-sm">
        <div class="mb-3">
            <input type="text" id="tableSearch" class="form-control" placeholder="Rechercher un code, une URL ou un utilisateur...">
        </div>
        <div class="card-header fw-bold">Utilisateurs enregistrés (Redis)</div>
        <ul class="list-group list-group-