<?php

session_start();

require_once 'connexion.php';

header('Content-Type: application/json; charset=utf-8');

function repondreRetraitPlaylist(int $code, array $donnees): never
{
    http_response_code($code);
    echo json_encode($donnees, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    repondreRetraitPlaylist(405, [
        'erreur' => 'Méthode non autorisée.'
    ]);
}

$userId = filter_var(
    $_SESSION['id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
$playlistId = filter_var(
    $_POST['playlist_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
$songId = filter_var(
    $_POST['song_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($userId === false) {
    repondreRetraitPlaylist(401, [
        'erreur' => 'Connecte-toi pour retirer une musique d’une playlist.'
    ]);
}

if ($playlistId === false || $songId === false) {
    repondreRetraitPlaylist(400, [
        'erreur' => 'La playlist ou la musique sélectionnée est invalide.'
    ]);
}

try {
    $mysqlClient->beginTransaction();

    $requete = $mysqlClient->prepare(
        'SELECT id
         FROM playlist
         WHERE id = :playlist_id
           AND user_id = :user_id
         FOR UPDATE'
    );
    $requete->execute([
        'playlist_id' => $playlistId,
        'user_id' => $userId
    ]);

    if (!$requete->fetchColumn()) {
        $mysqlClient->rollBack();
        repondreRetraitPlaylist(404, [
            'erreur' => 'Cette playlist est introuvable.'
        ]);
    }

    $requete = $mysqlClient->prepare(
        'DELETE FROM pivot_playlist
         WHERE playlist_id = :playlist_id
           AND song_id = :song_id'
    );
    $requete->execute([
        'playlist_id' => $playlistId,
        'song_id' => $songId
    ]);

    if ($requete->rowCount() === 0) {
        $mysqlClient->rollBack();
        repondreRetraitPlaylist(404, [
            'erreur' => 'Cette musique ne figure pas dans cette playlist.'
        ]);
    }

    $requete = $mysqlClient->prepare(
        'SELECT song_id
         FROM pivot_playlist
         WHERE playlist_id = :playlist_id
         ORDER BY position ASC, song_id ASC'
    );
    $requete->execute(['playlist_id' => $playlistId]);
    $musiquesRestantes = $requete->fetchAll(PDO::FETCH_COLUMN);

    $requete = $mysqlClient->prepare(
        'UPDATE pivot_playlist
         SET position = :position
         WHERE playlist_id = :playlist_id
           AND song_id = :song_id'
    );

    foreach ($musiquesRestantes as $index => $idMusique) {
        $requete->execute([
            'position' => $index + 1,
            'playlist_id' => $playlistId,
            'song_id' => $idMusique
        ]);
    }

    $mysqlClient->commit();

    repondreRetraitPlaylist(200, [
        'message' => 'La musique a été retirée de la playlist.'
    ]);
} catch (Throwable $erreur) {
    if ($mysqlClient->inTransaction()) {
        $mysqlClient->rollBack();
    }

    error_log('Erreur lors du retrait d’une musique d’une playlist : ' . $erreur->getMessage());

    repondreRetraitPlaylist(500, [
        'erreur' => 'Une erreur est survenue pendant le retrait de la musique.'
    ]);
}
