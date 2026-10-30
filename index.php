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

$couleurPseudoSession = $_SESSION['username_color'] ?? '#a855f7';

if (
    !is_string($couleurPseudoSession)
    || !preg_match('/\A#[0-9a-fA-F]{6}\z/', $couleurPseudoSession)
) {
    $couleurPseudoSession = '#a855f7';
}

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
    
        <?php
        // Récupérer les playlists de l'utilisateur connecté
        $playlists = [];

        if (isset($_SESSION['id'])) {
            $query = $mysqlClient->prepare(
                'SELECT id, name
                FROM playlist
                WHERE user_id = :user_id
                ORDER BY id DESC'
            );

            $query->execute([
                'user_id' => $_SESSION['id']
            ]);

            $playlists = $query->fetchAll(PDO::FETCH_ASSOC);
        }
        ?>
    
        <!-- Hello je sert à afficher les playlists de mon utilisateur -->
        <section class="pr-6">

            <h2 class="mb-4 mt-3 ml-3 text-xl font-bold text-white">Mes playlists</h2>
            <?php if (isset($_GET['erreur']) && $_GET['erreur'] === 'connexion'): ?>
                <p class="mb-3 text-sm text-red-400">Connecte-toi pour créer une playlist.</p>
            <?php endif; ?>
            <?php if (!empty($messagePlaylist)): ?>
                <p class="mb-3 text-sm text-blue-300">
                    <?= htmlspecialchars($messagePlaylist, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <form method="POST" action="index.php" class="flex flex-wrap gap-3">
                <button type="button" onclick="openPlaylist()" class="min-w-0 flex-1 overflow-hidden rounded-r-full border-b-2 border-r-2 border-[#004FD9] bg-[#00050F] p-2 text-left transition duration-200 hover:border-blue-400 hover:bg-[#000E26] text-white">
                    Ajouter une nouvelle playlist
                </button>
            </form>

            <!-- Liste des playlists de l'utilisateur -->
            <div class="mt-4 flex flex-col gap-2">

                <?php if (!empty($playlists)): ?>

                    <?php foreach ($playlists as $playlist): ?>

                        <div class="cartePlaylist group flex h-16 min-w-0 items-center overflow-hidden rounded-r-full border-b-2 border-r-2 border-[#004FD9] bg-[#00050F] transition hover:bg-[#000E26]" >

                            <button type="button" data-playlist-id="<?= (int) $playlist['id'] ?>" class="h-full min-w-0 flex-1 overflow-hidden p-3 text-left text-white">
                                <span class="block truncate font-semibold">
                                    <?= htmlspecialchars(
                                        $playlist['name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </span>
                            </button>

                            <button type="button" data-supprimer-playlist-id="<?= (int) $playlist['id'] ?>" data-playlist-name="<?= htmlspecialchars($playlist['name'], ENT_QUOTES, 'UTF-8') ?>" class="mr-3 flex h-11 w-11 shrink-0 items-center justify-center rounded-full transition hover:bg-[#10264A]" aria-label="Supprimer la playlist <?= htmlspecialchars($playlist['name'], ENT_QUOTES, 'UTF-8') ?>" title="Supprimer cette playlist">
                                <img src="icon/circle-minus-solid-full.svg" alt="" class="h-6 w-6 object-contain">
                            </button>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <p id="messageAucunePlaylist" class="px-2 text-sm text-gray-400"> Tu n'as pas encore de playlist.</p>

                <?php endif; ?>

            </div>

        </section>

        <!-- Fenêtre pop-up de playlist -->
        <section id="fenetrePlaylist" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/60 p-4">

            <div class="flex max-h-[90vh] w-full max-w-md flex-col rounded-xl border-2 border-[#0055FF] bg-[#202A36] p-6">

                <!-- En-tête -->
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-white" name="créer">Créer une playlist</h2>

                    <button type="button" onclick="closePlaylist()" class="text-2xl text-white" aria-label="Fermer">
                        &times;
                    </button>

                </div>

                <!-- Un seul formulaire pour les musiques ET le nom -->
                <form action="traitement-playlist.php" method="POST" class="flex min-h-0 flex-col">

                    <p class="mb-4 text-gray-300">Sélectionne les musiques à ajouter.</p>

                    <div class="mb-4 flex h-12 w-full items-center rounded-[20px] border-b-2 border-r-2 border-[#004FD9] bg-[#00050F] px-4">

                        <label for="rechercheMusiquePlaylist" class="sr-only">Rechercher une musique</label>
                        <input type="search" id="rechercheMusiquePlaylist" placeholder="Rechercher une musique..." autocomplete="off" class="h-full min-w-0 flex-1 bg-transparent text-white outline-none">

                        <button id="boutonRechercherMusiquePlaylist" type="button" aria-label="Rechercher une musique" class="ml-3 flex shrink-0 items-center justify-center">
                            <img src="icon/magnifying-glass-solid-full.svg" alt="" class="h-6 w-6 max-h-6 max-w-6 object-contain">
                        </button>
                    </div>

                    <!-- Liste des musiques -->
                    <div class="min-h-0 flex-1 overflow-y-auto pr-2">

                        <div id="listeMusiquesPlaylist" class="flex flex-col gap-2">

                            <?php foreach ($musiques as $musique): ?>

                                <label class="carte-selection flex h-16 w-full min-w-0 cursor-pointer items-center gap-4 overflow-hidden rounded-r-full border-b-2 border-r-2 border-[#004FD9] bg-[#00050F] p-2 transition duration-200 hover:bg-[#000E26] data-[select=true]:border-blue-400 data-[select=true]:bg-[#10264A]">

                                    <!-- Checkbox cachée -->
                                    <input type="checkbox" value="<?= (int) $musique['id'] ?>" class="hidden" data-song-checkbox>

                                    <!-- Pochette -->
                                    <?php if (!empty($musique['coverage'])): ?>

                                        <img src="<?= htmlspecialchars($musique['coverage'], ENT_QUOTES, 'UTF-8') ?>" alt="" class="h-12 w-12 shrink-0 object-cover">

                                    <?php else: ?>

                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center bg-[#101D35]">
                                            <span class="text-2xl text-white">♫</span>
                                        </div>

                                    <?php endif; ?>

                                    <!-- Nom de la musique -->
                                    <span class="min-w-0 flex-1 truncat font-semibold text-white">
                                        <?= htmlspecialchars($musique['name'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>

                                </label>

                            <?php endforeach; ?>

                        </div>

                        <p id="aucunResultatRecherchePlaylist" class="hidden py-4 text-gray-400">Aucune musique ne correspond à cette recherche.</p>

                        <?php if (empty($musiques)): ?>
                            <p class="py-4 text-gray-400">Aucune musique disponible.</p>
                        <?php endif; ?>

                    </div>

                    <div id="ordreMusiques"></div>
                    
                    <!-- Nom de la playlist -->
                    <div class="mt-5">
                        <label for="playlist_name" class="mb-2 block text-white">
                            Nom de la playlist
                        </label>

                        <input type="text" id="playlist_name" name="playlist_name" maxlength="30" required class="mb-4 w-full rounded-md border border-[#2577D9] bg-[#151D28] p-2 text-white" placeholder="Le nom de la playlist">

                        <button type="submit" class="w-full rounded-md bg-[#2577D9] p-2 text-white hover:bg-blue-600">
                            Enregistrer la playlist
                        </button>
                    </div>

                </form>
            </div>
        </section>

        <!-- Fenêtre d'ajout d'une musique à une playlist -->
        <section id="fenetreAjoutPlaylist" class="hidden fixed inset-0 z-[60] items-center justify-center bg-black/60 p-4" aria-labelledby="titreAjoutPlaylist" aria-modal="true" role="dialog">
            
            <div class="w-full max-w-md rounded-xl border-2 border-[#0055FF] bg-[#202A36] p-6">
                
                <div class="mb-4 flex items-center justify-between gap-4">

                    <h2 id="titreAjoutPlaylist" class="text-xl font-bold text-white">Ajouter à une playlist</h2>
                    <button type="button" onclick="fermerAjoutPlaylist()" class="text-2xl text-gray-400 hover:text-white" aria-label="Fermer">
                        &times;
                    </button>

                </div>

                <p id="musiqueAjoutPlaylist" class="mb-4 truncate text-gray-300"></p>

                <div id="choixPlaylistAjout" class="flex max-h-64 flex-col gap-2 overflow-y-auto">

                    <?php if (!isset($_SESSION['id'])): ?>

                        <p class="text-gray-300">Connecte-toi pour ajouter une musique à une playlist.</p>
                        <button id="boutonConnexionAjoutPlaylist" type="button" onclick="fermerAjoutPlaylist(); openConnexion()" class="rounded-lg bg-[#2577D9] p-2 text-white hover:bg-blue-600">
                            Se connecter
                        </button>

                    <?php elseif (empty($playlists)): ?>

                        <p class="text-gray-300">Tu n'as pas encore de playlist.</p>
                        <button type="button" onclick="fermerAjoutPlaylist(); openPlaylist()" class="rounded-lg bg-[#2577D9] p-2 text-white hover:bg-blue-600">
                            Créer une playlist
                        </button>

                    <?php else: ?>

                        <?php foreach ($playlists as $playlist): ?>

                            <button type="button" data-ajouter-a-playlist="<?= (int) $playlist['id'] ?>" data-playlist-name="<?= htmlspecialchars($playlist['name'], ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-lg border border-[#004FD9] bg-[#000A1C] p-3 text-left text-white transition hover:bg-[#10264A]">
                                <?= htmlspecialchars($playlist['name'], ENT_QUOTES, 'UTF-8') ?>
                            </button>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>

                <p id="statutAjoutPlaylist" class="mt-4 hidden text-sm" role="status" aria-live="polite"></p>
            
            </div>

        </section>

        <!-- Hello je sert à connecter mon utilisateur et afficher son pseudo quand il est
        connecté -->
        <section class="mt-auto w-75 h-45 border-r-2 border-t-2 border-[#0055FF] rounded-[20px]">

            <p class="text-white mt-5 ml-5">Session actuelle:</p>
            <?php if (isset($_SESSION['username'])): ?>

                <p class="mt-5 ml-5" style="color: <?= htmlspecialchars($couleurPseudoSession, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($_SESSION['username']) ?>
                </p>

                <!-- Bouton pour changer de session -->
                <div class="flex justify-end mt-auto ">

                    <button type="button" onclick="openConnexion()" class="pr-5 pt-2 text-blue-400 hover:text-blue-300 underline text-sm">
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

                    <h2 class="text-white text-xl font-bold">Connexion</h2>

                    <button type="button" onclick="closeConnexion()" class="text-white text-2xl">x</button>

                </div>

                <p class="text-gray-300 mb-4">Choisis ton pseudonyme pour te connecter.</p>

                <form action="traitement-connexion.php" method="POST">

                    <label for="username" class="text-white block mb-2">
                        Pseudonyme
                    </label>

                    <input type="text" id="username" name="username" maxlength="30" required class="w-full rounded-md p-2 mb-4 bg-[#151D28] text-white border border-[#2577D9]" placeholder="Ton pseudonyme">

                    <label for="usernameColor" class="text-white block mb-2">Couleur du pseudonyme</label>

                    <input type="color" id="usernameColor" name="username_color" value="<?= htmlspecialchars($couleurPseudoSession, ENT_QUOTES, 'UTF-8') ?>" class="mb-4 h-10 w-full cursor-pointer rounded-md border border-[#2577D9] bg-[#151D28] p-1">

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
            
            <form id="formulaireRecherchePrincipale" method="POST" action="" class="flex h-10 w-4/5 items-center rounded-[20px] border-b-2 border-r-2 border-[#004FD9] bg-[#00050F] px-4">

                <input type="text" id="recherchePrincipale" name="recherche" value="<?= htmlspecialchars($recherche, ENT_QUOTES, 'UTF-8') ?>" placeholder="Rechercher..." class="h-full min-w-0 flex-1 bg-transparent text-white outline-none">

                <button type="button" id="boutonEffacerRecherche" aria-label="Effacer la recherche et afficher toutes les musiques" title="Effacer la recherche" class="ml-3 hidden shrink-0 items-center justify-center">
                    <img src="icon/xmark-solid-full.svg" alt="" class="h-5 w-5 max-h-5 max-w-5 object-contain">
                </button>

                <button type="submit" aria-label="Lancer la recherche" class="ml-3 flex shrink-0 items-center justify-center">
                    <img src="icon/magnifying-glass-solid-full.svg" alt="" class="h-5 w-5 max-h-5 max-w-5 object-contain">
                </button>

            </form>

        </header>

        
        <!-- Zone d'affichage des musiques -->
        <section class="min-h-0 flex-1 overflow-y-auto py-6 pr-6">

            <div class="mb-5 flex items-center justify-between gap-4">

                <h2 id="titreListeMusiques" class="min-w-0 truncate text-2xl font-bold text-white">Toutes les musiques</h2>

            </div>

            <div id="listeMusiques" class="flex flex-col gap-3">

                <?php foreach ($musiques as $musique): ?>
                
                    <div data-song-id="<?= (int) $musique['id'] ?>" data-song-audio="<?= htmlspecialchars($musique['audio'], ENT_QUOTES, 'UTF-8') ?>" data-song-name="<?= htmlspecialchars($musique['name'], ENT_QUOTES, 'UTF-8') ?>" data-song-coverage="<?= htmlspecialchars($musique['coverage'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="group flex h-16 w-full min-w-0 items-center gap-2 overflow-hidden rounded-r-full border-b-2 border-r-2 border-[#004FD9] bg-[#00050F] p-2 transition duration-200 hover:bg-[#000E26] hover:border-blue-400 data-[playing=true]:bg-[#10264A] data-[playing=true]:border-blue-400">

                        <button type="button" class="flex h-full min-w-0 flex-1 items-center gap-4 text-left" aria-label="Lire <?= htmlspecialchars($musique['name'], ENT_QUOTES, 'UTF-8') ?>">
                            <?php if (!empty($musique['coverage'])): ?>

                                <img src="<?= htmlspecialchars($musique['coverage'], ENT_QUOTES, 'UTF-8') ?>" alt="" class="h-12 w-12 shrink-0 object-cover">

                            <?php else: ?>

                                <div class="flex h-12 w-12 shrink-0 items-center justify-center bg-[#101D35]">
                                    <span class="text-2xl text-white">♫</span>
                                </div>

                            <?php endif; ?>

                            <span class="min-w-0 flex-1 truncate font-semibold text-white">
                                <?= htmlspecialchars(
                                    $musique['name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </span>
                        </button>

                        <button type="button" class="boutonAjouterPlaylist flex h-10 w-10 shrink-0 items-center justify-center rounded-full hover:bg-[#10264A]" aria-label="Ajouter <?= htmlspecialchars($musique['name'], ENT_QUOTES, 'UTF-8') ?> à une playlist" title="Ajouter à une playlist">
                            <img src="icon/bars-solid-full.svg" alt="" class="h-5 w-5 object-contain">
                        </button>

                        <button type="button" class="boutonRetirerMusiquePlaylist hidden h-10 w-10 shrink-0 items-center justify-center rounded-full hover:bg-[#10264A]" aria-label="Retirer <?= htmlspecialchars($musique['name'], ENT_QUOTES, 'UTF-8') ?> de la playlist" title="Retirer de la playlist">
                            <img src="icon/circle-minus-solid-full.svg" alt="" class="h-6 w-6 object-contain">
                        </button>

                    </div>

                <?php endforeach; ?>

            </div>

            <?php if (empty($musiques)): ?>

                <p id="messageListeMusiques" class="mt-6 text-gray-400">Aucune musique n'est disponible pour le moment.</p>

            <?php else: ?>

                <p id="messageListeMusiques" class="mt-6 hidden text-gray-400"></p>

            <?php endif; ?>

        </section>


        
        <!-- Lecteur audio -->
        <section class="mt-auto flex h-45 w-full shrink-0 items-center justify-center rounded-t-[20px] border-t border-[#004FD9] bg-[#000C21]">
            
            <div id="lecteurAudio" class="contenu-cache flex h-30 w-9/10 items-center gap-4 rounded-[25px] border-b-2 border-r-2 border-[#0055FF] bg-[#00050F] px-4">

                <!-- Informations sur le morceau (Persona 5 Placeholder)-->
                <div class="flex min-w-0 flex-1 items-center gap-3">

                    <div class="flex h-16 w-16 shrink-0 -translate-y-1 items-center justify-center overflow-hidden rounded-lg bg-[#101D35]">
                        <img id="pochetteMusique" src="song/coverage/Persona 5 song coverage.jpg" alt="Pochette du morceau" class="h-full w-full object-cover">
                    </div>

                    <div class="min-w-0">
                        <p id="titreMusique" class="truncate font-semibold text-white">Beneath the Mask</p>
                    </div>

                </div>

                <!-- Commandes et progression -->
                <div class="flex w-3/4 min-w-0 flex-col items-center gap-3">

                    <!-- Boutons de lecture -->
                    <div class="flex items-center justify-center gap-5">

                        <!-- Répétition -->
                        <button type="button" id="boutonRepetition" aria-label="Répéter la musique" class="flex h-10 w-10 shrink-0 items-center justify-center hover:scale-110">
                            <img src="icon/repeat-solid-full.svg" alt="" class="h-5 w-5 object-contain">
                        </button>

                        <!-- Musique précédente -->
                        <button type="button" id="boutonPrecedent" aria-label="Musique précédente" class="flex h-10 w-10 shrink-0 items-center justify-center hover:scale-110">
                            <img src="icon/forward-solid-full.svg" alt="Précédent" class="h-7 w-7 object-contain rotate-180">
                        </button>

                        <!-- Lecture / pause -->
                        <button type="button" id="boutonLecture" aria-label="Lire la musique" class="flex h-14 w-14 shrink-0 items-center justify-center">
                            <img id="iconeLecture" src="icon/circle-play-solid-full.svg" alt="" class="h-10 w-10 object-contain">
                        </button>

                        <!-- Musique suivante -->
                        <button type="button" id="boutonSuivant" aria-label="Musique suivante" class="flex h-10 w-10 shrink-0 items-center justify-center hover:scale-110">
                            <img src="icon/forward-solid-full.svg" alt="Suivant" class="h-7 w-7 object-contain">
                        </button>

                        <!-- Lecture aléatoire -->
                        <button type="button" id="boutonAleatoire" aria-label="Lecture aléatoire" class="flex h-10 w-10 shrink-0 items-center justify-center hover:scale-110">
                            <img src="icon/shuffle-solid-full.svg" alt="" class="h-5 w-5 object-contain">
                        </button>

                    </div>
       
                    <!-- Barre de progression et bouton de fondu enchaîné -->
                    <div class="flex w-full items-center gap-3">

                        <!-- Zone de progression -->
                        <div class="flex min-w-0 flex-1 items-center gap-2 text-xs text-gray-400">

                            <span id="tempsActuel" class="shrink-0">0:00</span>

                            <input type="range" id="barreProgression" min="0" max="100" value="0" class="barre-progression min-w-0 flex-1 cursor-pointer" aria-label="Position dans le morceau">

                            <span id="dureeTotale" class="shrink-0">0:00</span>

                        </div>

                        <!-- Bouton de fondu enchaîné -->
                        <button type="button" id="boutonFondu" aria-label="Activer le fondu enchaîné" aria-pressed="false" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full hover:scale-110">
                            <img src="icon/mixer-brands-solid-full.svg" alt="" class="h-5 w-5 object-contain">
                        </button>

                    </div>

                </div>

                <!-- Contrôle du volume -->
                <div class="flex flex-1 items-center justify-end gap-2">

                    <img id="iconeVolume" src="icon/volume-solid-full.svg" alt="Volume normal" class="h-5 w-5 cursor-pointer">

                    <input type="range" id="volume" min="0" max="1" step="0.01" value="0.5" class="barre-volume w-20 cursor-pointer" aria-label="Volume">

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

            <img id="couvertureActuelle" src="" alt="Couverture de l'album" class="aspect-square w-full max-w-80 rounded-xl object-cover">

            <h2 id="titreActuel" class="text-center text-xl font-bold text-white"></h2>

        </section>

        <!-- Hello, je sers à afficher les commentaires -->
        <section id="zoneCommentaires" onclick="ouvrirCommentaire()" class="mt-auto ml-5 flex h-90 w-auto shrink-0 flex-col rounded-tr-[20px] rounded-bl-[20px] border-t-2 border-l-2 border-[#0055FF] bg-[#00050F]">

            <!-- Liste des commentaires -->
            <div id="listeCommentaires" class="flex-1 overflow-y-auto p-4"></div>

        </section>

        <!-- Fenêtre de rédaction d'un commentaire -->
        <div id="fenetreCommentaire" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/60 p-4">

            <div class="w-full max-w-lg rounded-xl border-2 border-[#0055FF] bg-[#202A36] p-6">

                <div class="mb-4 flex items-center justify-between">

                    <h2 class="text-xl font-bold text-white">Ajouter un commentaire</h2>

                    <button type="button" onclick="fermerCommentaire()" class="text-2xl text-gray-400 hover:text-white" aria-label="Fermer">
                        &times;
                    </button>

                </div>

                <form id="formulaireCommentaire" class="flex flex-col gap-4">

                    <textarea id="texteCommentaire" name="text" maxlength="2000" placeholder="Écris un nouveau commentaire..." required class="min-h-32 w-full resize-y rounded-lg border border-[#0055FF] bg-[#171E26] p-3 text-white placeholder-gray-400 focus:outline-none"></textarea>

                    <div class="flex justify-end gap-3">

                        <button type="button" onclick="fermerCommentaire()" class="rounded-lg bg-gray-600 px-4 py-2 text-white hover:bg-gray-500">
                            Annuler
                        </button>

                        <button type="submit" class="rounded-lg bg-[#0055FF] px-4 py-2 font-semibold text-white hover:bg-blue-600">
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