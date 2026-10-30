<?php

session_start();

// Vérifier que le formulaire a été envoyé
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}
require_once("connexion.php");

// Récupérer et nettoyer le pseudonyme
$usernameInput = $_POST["username"] ?? "";
$username = is_string($usernameInput) ? trim($usernameInput) : "";
$couleurInput = $_POST["username_color"] ?? "#a855f7";
$couleur = is_string($couleurInput) ? strtolower($couleurInput) : "";

// Vérifier que le pseudonyme est valide
if (
    $username === ""
    || mb_strlen($username, "UTF-8") > 30
    || !preg_match('/\A#[0-9a-f]{6}\z/', $couleur)
) {
    header("Location: index.php");
    exit;
}

try {
    // Rechercher un utilisateur ayant ce pseudonyme
    $query = $mysqlClient->prepare(
        "SELECT id, username
         FROM user
         WHERE username = :username"
    );

    $query->execute([
        "username" => $username
    ]);

    $user = $query->fetch(PDO::FETCH_ASSOC);

    // Si le pseudonyme n'existe pas, créer un utilisateur
    if (!$user) {
        $query = $mysqlClient->prepare(
            "INSERT INTO user (username, username_color)
             VALUES (:username, :username_color)"
        );

        $query->execute([
            "username" => $username,
            "username_color" => $couleur
        ]);

        // Récupérer l'identifiant du nouvel utilisateur
        $id = $mysqlClient->lastInsertId();

    } else {
        // L'utilisateur existe déjà
        $id = $user["id"];
        $username = $user["username"];

        $query = $mysqlClient->prepare(
            "UPDATE user
             SET username_color = :username_color
             WHERE id = :id"
        );
        $query->execute([
            "username_color" => $couleur,
            "id" => $id
        ]);
    }

    $ancienneIdentite = $_SESSION["id"] ?? null;

    // Enregistrer l'identité de l'utilisateur
    $_SESSION["id"] = (int) $id;
    $_SESSION["username"] = $username;
    $_SESSION["username_color"] = $couleur;

    header("Location: index.php");
    exit;

} catch (PDOException $e) {
    error_log("Erreur pendant la connexion utilisateur : " . $e->getMessage());
    http_response_code(500);
    exit("Une erreur est survenue lors de la connexion.");
}
