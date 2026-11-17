<?php
require_once __DIR__ . '/../config/redis.php';
require_once __DIR__ . '/../functions/riak.php';

if (!isset($_SESSION['user'])) {
    header('Location: /public/login.php');
    exit;
}

$userEmail = $_SESSION['user'];
$code = $_GET['code'] ?? '';
$link = riak_get('links', $code);

if ($link && ($link['id_utilisateur'] ?? '') === $userEmail) {
    riak_delete('links', $code);

    $userLinks = riak_get('user_links', $userEmail) ?? ['codes' => []];
    $userLinks['codes'] = array_values(array_diff($userLinks['codes'], [$code]));
    riak_set('user_links', $userEmail, $userLinks);
    $redis->set("user_links:$userEmail", json_encode($userLinks['codes']));
}

header('Location: /public/dashboard.php');
exit;