<?php

session_start();

require_once 'connexion.php';

header('Content-Type: application/json; charset=utf-8');

function repondreAjoutPlaylist(int $code, array $donnees): never
{
    http_response_code($code);
    echo json_encode($donnees, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    repondreAjoutPlaylist(405, [
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
    repondreAjoutPlaylist(401, [
        'erreur' => 'Connecte-toi pour ajouter une musique à une playlist.'
    ]);
}

if ($playlistId === false || $songId === false) {
    repondreAjoutPlaylist(400, [
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
        repondreAjoutPlaylist(404, [
            'erreur' => 'Cette playlist est introuvable.'
        ]);
    }

    $requete = $mysqlClient->prepare(
        'SELECT id FROM song WHERE id = :song_id'
    );
    $requete->execute(['song_id' => $songId]);

    if (!$requete->fetchColumn()) {
        $mysqlClient->rollBack();
        repondreAjoutPlaylist(404, [
            'erreur' => 'Cette musique est introuvable.'
        ]);
    }

    $requete = $mysqlClient->prepare(
        'SELECT 1
         FROM pivot_playlist
         WHERE playlist_id = :playlist_id
           AND song_id = :song_id'
    );
    $requete->execute([
        'playlist_id' => $playlistId,
        'song_id' => $songId
    ]);

    if ($requete->fetchColumn()) {
        $mysqlClient->rollBack();
        repondreAjoutPlaylist(409, [
            'erreur' => 'Cette musique est déjà dans cette playlist.'
        ]);
    }

    $requete = $mysqlClient->prepare(
        'SELECT COALESCE(MAX(position), 0) + 1
         FROM pivot_playlist
         WHERE playlist_id = :playlist_id'
    );
    $requete->execute(['playlist_id' => $playlistId]);
    $position = (int) $requete->fetchColumn();

    $requete = $mysqlClient->prepare(
        'INSERT INTO pivot_playlist (playlist_id, song_id, position)
         VALUES (:playlist_id, :song_id, :position)'
    );
    $requete->execute([
        'playlist_id' => $playlistId,
        'song_id' => $songId,
        'position' => $position
    ]);

    $mysqlClient->commit();

    repondreAjoutPlaylist(201, [
        'message' => 'La musique a été ajoutée à la playlist.'
    ]);
} catch (Throwable $erreur) {
    if ($mysqlClient->inTransaction()) {
        $mysqlClient->rollBack();
    }

    error_log('Erreur lors de l’ajout d’une musique à une playlist : ' . $erreur->getMessage());

    repondreAjoutPlaylist(500, [
        'erreur' => 'Une erreur est survenue pendant l’ajout de la musique.'
    ]);
}
