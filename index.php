<?php

session_start();

require_once("connexion.php");

// Récupérer le texte saisi dans la barre de recherche
$recherche = trim($_POST["recherche"] ?? "");

// Récupérer les musiques correspondant à la recherche
$query = $mysqlClient->prepare(
    "SELECT id, name, audio, coverage
     FROM song
     WHERE name LIKE :recherche
     ORDER BY name ASC"
);

$query->execute([
    "recherche" => "%" . $recherche . "%"
]);

$musiques = $query->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="./output.css" rel="stylesheet">
    <link href="./style.css" rel="stylesheet">
</head>


<body class="h-screen w-screen flex flex-row bg-[#000817]">
    
    <!-- Hello, je suis la zone qui sert à afficher tout ce qui se trouve a gauche de l'écran
    soit les playlist et la connexion utilisateur -->
    <aside class="flex h-screen w-75 shrink-0 flex-col border-r-2 border-t-2 rounded-[20px] border-[#004FD9] bg-[#000A1C]">
        <!-- Hello je sert à afficher les playlists de mon utilisateur -->
        <section>

        </section>

        <!-- Hello je sert à connecter mon utilisateur et afficher son pseudo quand il est
        connecté -->
        <section class="mt-auto w-75 h-45 border-r-2 border-t-2 border-[#0055FF] rounded-[20px]">
            <p class="text-white mt-5 ml-5">Session actuelle:</p>
            <?php
            if (isset($_SESSION['username'])): ?>

                    <p class="text-purple-500 mt-5 ml-5">
                        <?= htmlspecialchars($_SESSION['username']) ?>
                    </p>

                    <!-- Bouton pour changer de session -->
                    <div class="flex justify-end mt-auto">
                        <button type="button" onclick="openConnexion()" class="ml-5 text-blue-400 hover:text-blue-300 underline text-sm">
                            Changer de session
                        </button>
                    </div>

                <?php else: ?>

                    <button type="button" onclick="openConnexion()" class="text-blue-400 hover:text-blue-300 underline ml-5">
                        Se connecter
                    </button>

                <?php endif; ?>
        </section>

        <!-- Fenêtre pop-up de connexion -->
        <section id="fenetreConnexion" class="hidden fixed inset-0 z-50 bg-black/60 items-center justify-center p-4">

            <div class="bg-[#202A36] border-[#0055FF] border-2 rounded-xl p-6 w-full max-w-md">

                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-white text-xl font-bold">
                        Connexion
                    </h2>

                    <button type="button" onclick="closeConnexion()" class="text-white text-2xl">
                        x
                    </button>
                </div>

                <p class="text-gray-300 mb-4">
                        Choisis ton pseudonyme pour te connecter.
                </p>

                <form action="traitement-connexion.php" method="POST">

                    <label for="username" class="text-white block mb-2">
                        Pseudonyme
                    </label>

                    <input type="text" id="username" name="username" maxlength="30" required class="w-full rounded-md p-2 mb-4 bg-[#151D28] text-white border border-[#2577D9]" placeholder="Ton pseudonyme">

                    <button type="submit" class="w-full bg-[#2577D9] hover:bg-blue-600 text-white rounded-md p-2">
                        Se connecter
                    </button>

                </form>
            </div>
        </section>

    </aside>



    <!-- Hello, je sert à afficher tout ce qui se trouve au centre de l'écran soit la barre 
    de recherche, les musiques et le lecteur audio -->
    <main class="flex min-w-0 flex-1 flex-col">

        <!-- Hello, je sert à afficher la barre de recherche -->
        <header class="flex h-20 w-full shrink-0 items-center justify-center rounded-b-[15px] border-b-2 border-[#004FD9] bg-[#000C21]">
            <form method="POST" action="" class="flex h-10 w-4/5 items-center rounded-[20px] border-b-2 border-r-2 border-[#004FD9] bg-[#00050F] px-4">

                <input
                    type="text"
                    name="recherche"
                    placeholder="Rechercher..."
                    class="h-full min-w-0 flex-1 bg-transparent text-white outline-none"
                >

                <button type="submit" aria-label="Lancer la recherche" class="ml-3 flex shrink-0 items-center justify-center">
                    <img
                        src="icon/magnifying-glass-solid-full.svg"
                        alt=""
                        class="h-5 w-5 max-h-5 max-w-5 object-contain"
                    >
                </button>

            </form>
        </header>

        
        <!-- Zone d'affichage des musiques -->
        <section class="min-h-0 flex-1 overflow-y-auto py-6 pr-6">

            <h2 class="mb-5 text-2xl font-bold text-white">
                Toutes les musiques
            </h2>

            <div class="flex flex-col gap-3">

                <?php foreach ($musiques as $musique): ?>
                
                    <button
                        type="button"
                        data-song-id="<?= (int) $musique['id'] ?>"
                        data-song-audio="<?= htmlspecialchars($musique['audio'], ENT_QUOTES, 'UTF-8') ?>"
                        data-song-name="<?= htmlspecialchars($musique['name'], ENT_QUOTES, 'UTF-8') ?>"
                        data-song-coverage="<?= htmlspecialchars($musique['coverage'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        class="group flex h-16 w-full min-w-0 items-center gap-4
                            overflow-hidden rounded-r-full border-b-2 border-r-2 border-[#004FD9]
                            bg-[#00050F] p-2 text-left
                            transition duration-200 hover:bg-[#000E26]
                            hover:border-blue-400
                            data-[playing=true]:bg-[#10264A]
                            data-[playing=true]:border-blue-400"
                    >

                        <?php if (!empty($musique['coverage'])): ?>
                            
                            <img
                                src="<?= htmlspecialchars(
                                    $musique['coverage'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                alt=""
                                class="h-12 w-12 shrink-0 object-cover"
                            >

                        <?php else: ?>

                            <div class="flex h-12 w-12 shrink-0 items-center justify-center
                                        bg-[#101D35]">
                                <span class="text-2xl text-white">♫</span>
                            </div>

                        <?php endif; ?>

                        <p class="min-w-0 flex-1 truncate font-semibold text-white">
                            <?= htmlspecialchars(
                                $musique['name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </p>

                    </button>

                <?php endforeach; ?>

            </div>

            <?php if (empty($musiques)): ?>

                <p class="mt-6 text-gray-400">
                    Aucune musique n'est disponible pour le moment.
                </p>

            <?php endif; ?>

        </section>


        
        <!-- Lecteur audio -->
        <section class="mt-auto flex h-45 w-full shrink-0 items-center justify-center rounded-t-[20px] border-t border-[#004FD9] bg-[#000C21]">
            <div id="lecteurAudio" class="contenu-cache flex h-30 w-9/10 items-center gap-4 rounded-[25px] border-b-2 border-r-2 border-[#0055FF] bg-[#00050F] px-4">

                <!-- Informations sur le morceau -->
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-[#101D35]">
                        <img
                            id="pochetteMusique"
                            src="song/coverage/Persona 5 song coverage.jpg"
                            alt="Pochette du morceau"
                            class="h-full w-full object-cover"
                        >
                    </div>

                    <div class="min-w-0">
                        <p id="titreMusique" class="truncate font-semibold text-white">
                            Beneath the Mask
                        </p>
                    </div>
                </div>

                <!-- Commandes et progression -->
                <div class="flex w-3/4 min-w-0 flex-col items-center gap-3">

                    <!-- Boutons de lecture -->
                    <div class="flex items-center justify-center gap-5">

                        <!-- Répétition -->
                        <button
                            type="button"
                            id="boutonRepetition"
                            aria-label="Répéter la musique"
                            class="flex h-10 w-10 shrink-0 items-center justify-center hover:scale-110"
                        >
                            <img
                                src="icon/repeat-solid-full.svg"
                                alt=""
                                class="h-5 w-5 object-contain"
                            >
                        </button>

                        <!-- Musique précédente -->
                        <button
                            type="button"
                            id="boutonPrecedent"
                            aria-label="Musique précédente"
                            class="flex h-10 w-10 shrink-0 items-center justify-center hover:scale-110"
                        >
                            <img
                                src="icon/forward-solid-full.svg"
                                alt="Précédent"
                                class="h-7 w-7 object-contain rotate-180"
                            >
                        </button>

                        <!-- Lecture / pause -->
                        <button
                            type="button"
                            id="boutonLecture"
                            aria-label="Lire la musique"
                            class="flex h-14 w-14 shrink-0 items-center justify-center"
                        >
                            <img
                                id="iconeLecture"
                                src="icon/circle-play-solid-full.svg"
                                alt=""
                                class="h-10 w-10 object-contain"
                            >
                        </button>

                        <!-- Musique suivante -->
                        <button
                            type="button"
                            id="boutonSuivant"
                            aria-label="Musique suivante"
                            class="flex h-10 w-10 shrink-0 items-center justify-center hover:scale-110"
                        >
                            <img
                                src="icon/forward-solid-full.svg"
                                alt="Suivant"
                                class="h-7 w-7 object-contain"
                            >
                        </button>

                        <!-- Lecture aléatoire -->
                        <button
                            type="button"
                            id="boutonAleatoire"
                            aria-label="Lecture aléatoire"
                            class="flex h-10 w-10 shrink-0 items-center justify-center hover:scale-110"
                        >
                            <img
                                src="icon/shuffle-solid-full.svg"
                                alt=""
                                class="h-5 w-5 object-contain"
                            >
                        </button>

                    </div>
       
                    <!-- Barre de progression et bouton de fondu enchaîné -->
                    <div class="flex w-full items-center gap-3">

                        <!-- Zone de progression -->
                        <div class="flex min-w-0 flex-1 items-center gap-2 text-xs text-gray-400">
                            <span id="tempsActuel" class="shrink-0">0:00</span>

                            <input
                                type="range"
                                id="barreProgression"
                                min="0"
                                max="100"
                                value="0"
                                class="barre-progression min-w-0 flex-1 cursor-pointer"
                                aria-label="Position dans le morceau"
                            >

                            <span id="dureeTotale" class="shrink-0">0:00</span>
                        </div>

                        <!-- Bouton de fondu enchaîné -->
                        <button
                            type="button"
                            id="boutonFondu"
                            aria-label="Activer le fondu enchaîné"
                            aria-pressed="false"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full hover:scale-110"
                        >
                            <img
                                src="icon/mixer-brands-solid-full.svg"
                                alt=""
                                class="h-5 w-5 object-contain"
                            >
                        </button>

                    </div>

                </div>

                <!-- Contrôle du volume -->
                <div class="flex flex-1 items-center justify-end gap-2">
                    <img
                        id="iconeVolume"
                        src="icon/volume-solid-full.svg"
                        alt="Volume normal"
                        class="h-5 w-5 cursor-pointer"
                    >

                    <input
                        type="range"
                        id="volume"
                        min="0"
                        max="1"
                        step="0.01"
                        value="0.5"
                        class="barre-volume w-20 cursor-pointer"
                        aria-label="Volume"
                    >
                </div>

            </div>
        </section>

        <!-- Fichier audio temporaire -->
        <audio id="audio" preload="metadata"></audio>
        <audio id="audioSuivant" preload="metadata"></audio>

    </main>




    <!-- Hello, je sert à afficher tout ce qui est à droite de l'écran,
    soit la musique actuelle et les commentaires -->
    <aside class="flex h-screen w-105 shrink-0 ml-auto flex-col border-l-2 border-t-2 rounded-[20px] border-[#004FD9] bg-[#000A1C]">

        <!-- Hello, je sers à afficher la musique actuelle -->
        <section id="musiqueActuelle" class="contenu-cache flex flex-col items-center gap-4 p-6">
            <img
                id="couvertureActuelle"
                src=""
                alt="Couverture de l'album"
                class="aspect-square w-full max-w-80 rounded-xl object-cover"
            >

            <h2
                id="titreActuel"
                class="text-center text-xl font-bold text-white"
            ></h2>
        </section>

        <!-- Hello, je sers à afficher les commentaires -->
        <section id="zoneCommentaires" onclick="ouvrirCommentaire()" class="mt-auto ml-5 flex h-90 w-auto shrink-0 flex-col rounded-tr-[20px] rounded-bl-[20px] border-t-2 border-l-2 border-[#0055FF] bg-[#00050F]">

            <!-- Liste des commentaires -->
            <div id="listeCommentaires" class="flex-1 overflow-y-auto p-4">

            </div>

        </section>

        <!-- Fenêtre de rédaction d'un commentaire -->
        <div id="fenetreCommentaire" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/60 p-4">

            <div class="w-full max-w-lg rounded-xl border-2 border-[#0055FF] bg-[#202A36] p-6">

                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-white">Ajouter un commentaire</h2>

                    <button
                        type="button"
                        onclick="fermerCommentaire()"
                        class="text-2xl text-gray-400 hover:text-white"
                        aria-label="Fermer"
                    >
                        &times;
                    </button>
                </div>

                <form id="formulaireCommentaire" class="flex flex-col gap-4">

                    <textarea
                        id="texteCommentaire"
                        name="text"
                        maxlength="2000"
                        placeholder="Écris un nouveau commentaire..."
                        required
                        class="min-h-32 w-full resize-y rounded-lg border border-[#0055FF] bg-[#171E26] p-3 text-white placeholder-gray-400 focus:outline-none"
                    ></textarea>

                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            onclick="fermerCommentaire()"
                            class="rounded-lg bg-gray-600 px-4 py-2 text-white hover:bg-gray-500"
                        >
                            Annuler
                        </button>

                        <button
                            type="submit"
                            class="rounded-lg bg-[#0055FF] px-4 py-2 font-semibold text-white hover:bg-blue-600"
                        >
                            Envoyer
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </aside>

    <script src="script.js"></script>
</body>
</html>