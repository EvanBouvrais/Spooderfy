<?php

session_start();

require_once 'connexion.php';

header('Content-Type: application/json; charset=utf-8');

function repondreSuppressionPlaylist(int $code, array $donnees): never
{
    http_response_code($code);
    echo json_encode($donnees, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    repondreSuppressionPlaylist(405, [
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

if ($userId === false) {
    repondreSuppressionPlaylist(401, [
        'erreur' => 'Connecte-toi pour supprimer une playlist.'
    ]);
}

if ($playlistId === false) {
    repondreSuppressionPlaylist(400, [
        'erreur' => 'La playlist sélectionnée est invalide.'
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
        repondreSuppressionPlaylist(404, [
            'erreur' => 'Cette playlist est introuvable.'
        ]);
    }

    $requete = $mysqlClient->prepare(
        'DELETE FROM pivot_playlist WHERE playlist_id = :playlist_id'
    );
    $requete->execute(['playlist_id' => $playlistId]);

    $requete = $mysqlClient->prepare(
        'DELETE FROM playlist
         WHERE id = :playlist_id
           AND user_id = :user_id'
    );
    $requete->execute([
        'playlist_id' => $playlistId,
        'user_id' => $userId
    ]);

    $mysqlClient->commit();

    repondreSuppressionPlaylist(200, [
        'message' => 'La playlist a été supprimée.'
    ]);
} catch (Throwable $erreur) {
    if ($mysqlClient->inTransaction()) {
        $mysqlClient->rollBack();
    }

    error_log('Erreur lors de la suppression d’une playlist : ' . $erreur->getMessage());

    repondreSuppressionPlaylist(500, [
        'erreur' => 'Une erreur est survenue pendant la suppression de la playlist.'
    ]);
}
