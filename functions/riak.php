<?php
define('RIAK_HOST', 'http://127.0.0.1:8098');

/**
 * Récupère un document JSON depuis un bucket Riak
 */
function riak_get(string $bucket, string $key): ?array {
    $url = RIAK_HOST . "/buckets/" . urlencode($bucket) . "/keys/" . urlencode($key);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/json']
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //curl_close($ch);

    if ($httpCode === 200 && $response !== false) {
        return json_decode($response, true);
    }
    return null;
}

/**
 * Enregistre ou met à jour un document JSON dans Riak
 */
function riak_set(string $bucket, string $key, array $data): bool {
    $url = RIAK_HOST . "/buckets/" . urlencode($bucket) . "/keys/" . urlencode($key);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => "PUT",
        CURLOPT_POSTFIELDS     => json_encode($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json']
    ]);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //curl_close($ch);

    return in_array($httpCode, [200, 201, 204]);
}

/**
 * Supprime une clé d'un bucket Riak
 */
function riak_delete(string $bucket, string $key): bool {
    $url = RIAK_HOST . "/buckets/" . urlencode($bucket) . "/keys/" . urlencode($key);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => "DELETE",
        CURLOPT_RETURNTRANSFER => true
    ]);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //curl_close($ch);

    return in_array($httpCode, [200, 204, 404]);
}

/**
 * Liste toutes les clés d'un bucket Riak
 */
function riak_list_keys(string $bucket): array {
    $url = RIAK_HOST . "/buckets/" . urlencode($bucket) . "/keys?keys=true";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/json']
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //curl_close($ch);

    if ($httpCode === 200 && $response !== false) {
        $decoded = json_decode($response, true);
        return $decoded['keys'] ?? [];
    }
    return [];
}

/**
 * Génère un code court aléatoire unique de 6 caractères
 */
function generate_short_code(int $length = 6): string {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    do {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
    } while (riak_get('links', $code) !== null);

    return 