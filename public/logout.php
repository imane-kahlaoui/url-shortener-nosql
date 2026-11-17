<?php
require_once __DIR__ . '/../config/redis.php';

$token = session_id();
$redis->del("session:$token");

$_SESSION = [];
session_destroy();

header('Location: /public/index.php');
exit;