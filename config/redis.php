<?php
// Configuration du gestionnaire de sessions sur Redis
ini_set('session.save_handler', 'redis');
ini_set('session.save_path', 'tcp://127.0.0.1:6379');
ini_set('session.gc_maxlifetime', 3600); // TTL de 3600s

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Connexion à Redis pour le stockage des utilisateurs
$redis = new Redis();
try {
    $redis->connect('127.0.0.1', 6379);
} catch (Exception $e) {
    die("Erreur de connexion à Redis : " . htmlspecialchars($e->getMessage()));
}