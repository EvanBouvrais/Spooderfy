
<?php

require_once("connexion.php");

header("Content-Type: application/json; charset=utf-8");

$songId = filter_input(INPUT_GET, "song_id", FILTER_VALIDATE_INT);

if (!$songId || $songId < 1) {
    http_response_code(400);
    echo json_encode([
        "erreur" => "Identifiant de musique invalide."
    ]);
    exit;
}

try {
    $query = $mysqlClient->prepare(
        "SELECT comments.text, user.username, user.username_color
         FROM comments
         INNER JOIN user ON comments.user_id = user.id
         WHERE comments.song_id = :song_id
         ORDER BY comments.id DESC"
    );

    $query->execute([
        "song_id" => $songId
    ]);

    $commentaires = $query->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(
        $commentaires,
        JSON_UNESCAPED_UNICODE
    );

} catch (PDOException $e) {
    error_log("Erreur lors du chargement des commentaires : " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "erreur" => "Impossible de charger les commentaires."
    ]);
}
