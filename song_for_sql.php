<?php
require_once("connexion.php");

// Afficher les erreurs SQL sous forme d'exceptions
$mysqlClient->setAttribute(
    PDO::ATTR_ERRMODE,
    PDO::ERRMODE_EXCEPTION
);

$dossierSong = __DIR__ . "/song/all_song/";
$dossierCover = __DIR__ . "/song/coverage/";

// Vérifier que le dossier audio existe
if (!is_dir($dossierSong)) {
    die("Erreur : dossier audio introuvable : " . $dossierSong);
}

// Insérer ou actualiser les informations de la chanson
$sql = "INSERT INTO song (name, audio, coverage)
        VALUES (:name, :audio, :coverage)
        ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            audio = VALUES(audio),
            coverage = VALUES(coverage)";

$stmt = $mysqlClient->prepare($sql);

$nombreAjouts = 0;
$nombreErreurs = 0;

foreach (new DirectoryIterator($dossierSong) as $fileSong) {

    // Ignorer les dossiers et les entrées spéciales
    if (!$fileSong->isFile()) {
        continue;
    }

    // Ne conserver que les fichiers MP3
    if (strtolower($fileSong->getExtension()) !== "mp3") {
        continue;
    }

    $nomSong = $fileSong->getFilename();
    $nomSansExtension = pathinfo(
        $nomSong,
        PATHINFO_FILENAME
    );

    // Chemin relatif du fichier audio
    $cheminSong = "song/all_song/" . $nomSong;

    // Rechercher la pochette correspondante
    $nomCover = $nomSansExtension . ".jpg";
    $cheminCoverAbsolu = $dossierCover . $nomCover;

    $cheminCover = is_file($cheminCoverAbsolu)
        ? "song/coverage/" . $nomCover
        : null;

    try {
        $stmt->execute([
            ":name" => $nomSong,
            ":audio" => $cheminSong,
            ":coverage" => $cheminCover
        ]);

        $nombreAjouts++;

        echo "Enregistré : "
            . htmlspecialchars($nomSong)
            . "<br>";

    } catch (PDOException $e) {
        $nombreErreurs++;

        echo "Erreur pour "
            . htmlspecialchars($nomSong)
            . " : "
            . htmlspecialchars($e->getMessage())
            . "<br>";
    }
}