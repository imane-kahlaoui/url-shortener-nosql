<?php
require_once __DIR__ . '/../config/redis.php';
require_once __DIR__ . '/../functions/riak.php';

if (($_SESSION['user'] ?? '') !== 'admin@gmail.com') {
    http_response_code(403);
    die("Accès refusé.");
}

$code = $_GET['code'] ?? '';
$link = riak_get('links', $code);

if ($link) {
    riak_delete('links', $code);
    if (!empty($link['id_utilisateur'])) {
        $owner = $link['id_utilisateur'];
        $userLinks = riak_get('user_links', $owner) ?? ['codes' => []];
        $userLinks['codes'] = array_values(array_diff($userLinks['codes'], [$code]));
        riak_set('user_links', $owner, $userLinks);
        $redis->set("user_links:$owner", json_encode($userLinks['codes']));
    }
}

header('Location: /admin/admin.php');
exit;