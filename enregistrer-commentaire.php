
<?php

session_start();

header("Content-Type: application/json; charset=utf-8");

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION["id"])) {
    http_response_code(401);

    echo json_encode([
        "erreur" => "Tu dois être connecté pour publier un commentaire."
    ]);

    exit;
}

require_once("connexion.php");

// Vérifier la méthode d'envoi
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "erreur" => "Méthode non autorisée."
    ]);

    exit;
}

// Récupérer les données envoyées
$songId = filter_input(INPUT_POST, "song_id", FILTER_VALIDATE_INT);
$texte = trim($_POST["text"] ?? "");

// Vérifier les données
if (!$songId || $songId < 1 || $texte === "" || mb_strlen($texte) > 2000) {
    http_response_code(400);

    echo json_encode([
        "erreur" => "Le commentaire ou la musique est invalide."
    ]);

    exit;
}

try {
    // Vérifier que la musique existe
    $verification = $mysqlClient->prepare(
        "SELECT id FROM song WHERE id = :song_id"
    );

    $verification->execute([
        "song_id" => $songId
    ]);

    if (!$verification->fetch()) {
        http_response_code(404);

        echo json_encode([
            "erreur" => "Cette musique n'existe pas."
        ]);

        exit;
    }

    // Enregistrer le commentaire
    $requete = $mysqlClient->prepare(
        "INSERT INTO comments (user_id, song_id, text)
         VALUES (:user_id, :song_id, :text)"
    );

    $requete->execute([
        "user_id" => $_SESSION["id"],
        "song_id" => $songId,
        "text" => $texte
    ]);

    echo json_encode([
        "succes" => true,
        "message" => "Commentaire publié."
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("Erreur lors de l'enregistrement du commentaire : " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "erreur" => "Impossible d'enregistrer le commentaire."
    ], JSON_UNESCAPED_UNICODE);
}
