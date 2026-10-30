
<?php

session_start();

require_once 'connexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Utiliser la même clé de session que dans index.php.
$userId = $_SESSION['id'] ?? null;

if (!$userId) {
    header('Location: index.php?erreur=connexion');
    exit;
}

$nom = trim($_POST['playlist_name'] ?? '');
$songIds = $_POST['song_ids'] ?? [];

if ($nom === '' || mb_strlen($nom) > 30) {
    exit('Le nom de la playlist est invalide.');
}

if (!is_array($songIds)) {
    exit('La sélection des musiques est invalide.');
}

// Conserver l'ordre reçu et supprimer les doublons.
$songIds = array_values(array_unique(array_filter(
    $songIds,
    fn($id) => is_string($id)
        && ctype_digit($id)
        && (int) $id > 0
)));

if (empty($songIds)) {
    exit('Sélectionne au moins une musique.');
}

try {
    $mysqlClient->beginTransaction();

    // Créer la playlist.
    $requete = $mysqlClient->prepare(
        'INSERT INTO playlist (name, user_id)
         VALUES (:name, :user_id)'
    );

    $requete->execute([
        'name' => $nom,
        'user_id' => $userId
    ]);

    $playlistId = $mysqlClient->lastInsertId();

    // Vérifier que les musiques existent réellement.
    $placeholders = implode(
        ',',
        array_fill(0, count($songIds), '?')
    );

    $requete = $mysqlClient->prepare(
        "SELECT id FROM song WHERE id IN ($placeholders)"
    );

    $requete->execute($songIds);

    $idsExistants = array_map(
        'intval',
        $requete->fetchAll(PDO::FETCH_COLUMN)
    );

    $musiquesExistantes = array_fill_keys(
        $idsExistants,
        true
    );

    // Insérer les morceaux en conservant l'ordre reçu.
    $ajout = $mysqlClient->prepare(
        'INSERT INTO pivot_playlist
            (playlist_id, song_id, position)
         VALUES
            (:playlist_id, :song_id, :position)'
    );

    $position = 1;

    foreach ($songIds as $songId) {
        $songId = (int) $songId;

        if (!isset($musiquesExistantes[$songId])) {
            continue;
        }

        $ajout->execute([
            'playlist_id' => $playlistId,
            'song_id' => $songId,
            'position' => $position
        ]);

        $position++;
    }

    $mysqlClient->commit();

    header('Location: index.php?playlist=creee');
    exit;

} catch (Throwable $e) {
    if ($mysqlClient->inTransaction()) {
        $mysqlClient->rollBack();
    }

    error_log($e->getMessage());

    http_response_code(500);
    echo 'Une erreur est survenue lors de la création de la playlist.';
}
