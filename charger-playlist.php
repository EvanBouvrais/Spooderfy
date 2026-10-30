<?php

session_start();

require_once 'connexion.php';

header('Content-Type: application/json; charset=utf-8');

$userId = $_SESSION['id'] ?? null;
$playlistId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$userId || !$playlistId || $playlistId < 1) {
    http_response_code(400);

    echo json_encode([
        'erreur' => 'Requête invalide ou utilisateur non connecté.'
    ]);

    exit;
}

$requete = $mysqlClient->prepare(
    'SELECT
        s.id,
        s.name,
        s.audio,
        s.coverage
     FROM playlist AS p
     INNER JOIN pivot_playlist AS pp
        ON pp.playlist_id = p.id
     INNER JOIN song AS s
        ON s.id = pp.song_id
     WHERE p.id = :playlist_id
       AND p.user_id = :user_id
     ORDER BY pp.position ASC, pp.song_id ASC'
);

$requete->execute([
    'playlist_id' => $playlistId,
    'user_id' => $userId
]);

$musiques = $requete->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(
    $musiques,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);