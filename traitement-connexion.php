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

// Vérifier que le pseudonyme est valide
if ($username === "" || mb_strlen($username, "UTF-8") > 30) {
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
            "INSERT INTO user (username)
             VALUES (:username)"
        );

        $query->execute([
            "username" => $username
        ]);

        // Récupérer l'identifiant du nouvel utilisateur
        $id = $mysqlClient->lastInsertId();

    } else {
        // L'utilisateur existe déjà
        $id = $user["id"];
        $username = $user["username"];
    }

    $ancienneIdentite = $_SESSION["id"] ?? null;

    // Enregistrer l'identité de l'utilisateur
    $_SESSION["id"] = (int) $id;
    $_SESSION["username"] = $username;

    header("Location: index.php");
    exit;

} catch (PDOException $e) {
    error_log("Erreur pendant la connexion utilisateur : " . $e->getMessage());
    http_response_code(500);
    exit("Une erreur est survenue lors de la connexion.");
}
