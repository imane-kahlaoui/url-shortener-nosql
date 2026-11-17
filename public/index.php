<?php
require_once __DIR__ . '/../config/redis.php';
require_once __DIR__ . '/../functions/riak.php';

$error = '';
$shortUrl = '';
$userEmail = $_SESSION['user'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $originalUrl = trim($_POST['original_url'] ?? '');
    $customAlias = trim($_POST['custom_alias'] ?? '');
    $expirationInput = trim($_POST['expiration'] ?? '');
    $linkPassword = $_POST['link_password'] ?? '';

    if (!filter_var($originalUrl, FILTER_VALIDATE_URL)) {
        $error = "Veuillez saisir une URL valide (ex: https://www.google.com).";
    } else {
        // Gestion du code court (personnalisé uniquement si connecté)
        if ($userEmail && $customAlias !== '') {
            if (!preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $customAlias)) {
                $error = "L'alias doit contenir entre 3 et 20 caractères alphanumériques.";
            } elseif (riak_get('links', $customAlias) !== null) {
                $error = "Cet alias est déjà utilisé.";
            } else {
                $code = $customAlias;
            }
        } else {
            $code = generate_short_code(6);
        }

        if ($error === '') {
            // Gestion de l'expiration : 30 jours par défaut pour les anonymes
            if (!$userEmail) {
                $expiration = gmdate('Y-m-d\TH:i:s\Z', strtotime('+30 days'));
            } elseif ($expirationInput !== '') {
                $expiration = gmdate('Y-m-d\TH:i:s\Z', strtotime($expirationInput));
            } else {
                $expiration = null;
            }

            $linkData = [
                'original_url'   => $originalUrl,
                'code_court'     => $code,
                'date_creation'  => gmdate('Y-m-d\TH:i:s\Z'),
                'id_utilisateur' => $userEmail,
                'expiration'     => $expiration,
                'clicks'         => 0,
                'last_ip'        => null,
                'browser'        => null,
                'password'       => ($linkPassword !== '') ? password_hash($linkPassword, PASSWORD_DEFAULT) : null
            ];

            if (riak_set('links', $code, $linkData)) {
                // Si connecté, associer le lien à l'utilisateur dans Riak et Redis
                if ($userEmail) {
                    $userLinks = riak_get('user_links', $userEmail) ?? ['codes' => []];
                    if (!in_array($code, $userLinks['codes'])) {
                        $userLinks['codes'][] = $code;
                    }
                    riak_set('user_links', $userEmail, $userLinks);
                    $redis->set("user_links:$userEmail", json_encode($userLinks['codes']));
                }

                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
                $shortUrl = "$protocol://$host/public/redirect.php?code=" . urlencode($code);
            } else {
                $error = "Erreur lors de l'enregistrement dans Riak.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raccourcisseur de Liens - NoSQL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/public/index.php">ShortLink NoSQL</a>
        <div class="ms-auto">
            <?php if ($userEmail): ?>
                <?php if ($userEmail === 'admin@gmail.com'): ?>
                    <a href="/admin/admin.php" class="btn btn-warning btn-sm me-2">Admin</a>
                <?php endif; ?>
                <a href="/public/dashboard.php" class="btn btn-light btn-sm me-2">Dashboard</a>
                <a href="/public/logout.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
            <?php else: ?>
                <a href="/public/login.php" class="btn btn-light btn-sm me-2">Connexion</a>
                <a href="/public/register.php" class="btn btn-outline-light btn-sm">Inscription</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="card-title text-center mb-4">Raccourcir un lien</h2>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">URL longue *</label>
                            <input type="url" name="original_url" class="form-control form-control-lg" placeholder="https://exemple.com/ma-page-tres-longue" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <?php if ($userEmail): ?>
                                <div class="col-md-4">
                                    <label class="form-label">Alias personnalisé (opt.)</label>
                                    <input type="text" name="custom_alias" class="form-control" placeholder="mon-lien">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date d'expiration (opt.)</label>
                                    <input type="datetime-local" name="expiration" class="form-control">
                                </div>
                            <?php endif; ?>
                            <div class="<?= $userEmail ? 'col-md-4' : 'col-md-12' ?>">
                                <label class="form-label">Mot de passe du lien (opt.)</label>
                                <input type="password" name="link_password" class="form-control" placeholder="Protéger par mot de passe">
                            </div>
                        </div>

                        <?php if (!$userEmail): ?>
                            <p class="text-muted small">Mode anonyme : le lien expirera automatiquement après 30 jours. Connectez-vous pour personnaliser vos liens.</p>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary btn-lg w-100">Générer le lien court</button>
                    </form>
<?php if ($shortUrl): ?>
    <hr class="my-4">
    <div class="alert alert-success text-center p-4">
        <h5 class="mb-3">Votre lien court est prêt :</h5>
        
        <!-- Bloc interactif avec le bouton Copier -->
        <div class="short-url-box d-flex justify-content-between align-items-center mb-3 bg-white p-3 rounded shadow-sm">
            <a id="generatedShortUrl" href="<?= htmlspecialchars($shortUrl) ?>" target="_blank" class="fw-bold text-decoration-none fs-5">
                <?= htmlspecialchars($shortUrl) ?>
            </a>
            <button type="button" class="btn btn-sm btn-outline-primary btn-copy-link px-3" data-target="generatedShortUrl">
                Copier
            </button>
        </div>

        <!-- Bloc du QR Code avec le bouton de téléchargement -->
        <div class="mt-3">
            <p class="mb-2 small text-muted">QR Code de partage 