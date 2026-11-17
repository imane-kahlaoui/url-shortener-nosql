<?php
require_once __DIR__ . '/../functions/riak.php';

$code = trim($_GET['code'] ?? '');
if ($code === '') {
    http_response_code(400);
    die("Code court manquant.");
}

$link = riak_get('links', $code);
if (!$link) {
    http_response_code(404);
    die("Lien introuvable.");
}

// Vérification de l'expiration
if (!empty($link['expiration']) && strtotime($link['expiration']) < time()) {
    http_response_code(410);
    die("Ce lien a expiré.");
}

// Vérification de la protection par mot de passe
if (!empty($link['password'])) {
    $passError = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $inputPass = $_POST['password'] ?? '';
        if (!password_verify($inputPass, $link['password'])) {
            $passError = "Mot de passe incorrect.";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $passError !== '') {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>Lien protégé</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light d-flex align-items-center" style="height: 100vh;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <div class="card shadow-sm p-4">
                        <h5 class="mb-3">Ce lien est protégé</h5>
                        <?php if ($passError): ?><div class="alert alert-danger"><?= htmlspecialchars($passError) ?></div><?php endif; ?>
                        <form method="POST">
                            <input type="password" name="password" class="form-control mb-3" placeholder="Mot de passe" required>
                            <button type="submit" class="btn btn-primary w-100">Accéder au lien</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Enregistrement des statistiques du clic
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$browser = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$timestamp = time();

$link['clicks'] = ($link['clicks'] ?? 0) + 1;
$link['last_ip'] = $ip;
$link['browser'] = $browser;
riak_set('links', $code, $link);

riak_set('clicks', "$code:$timestamp", [
    'date'       => gmdate('Y-m-d\TH:i:s\Z', $timestamp),
    'ip'         => $ip,
    'navigateur' => $browser,
    'id_lien'    => $code
]);

// Redirection vers l'URL d'origine
header('Location: ' . $link['original_url']);
exit;