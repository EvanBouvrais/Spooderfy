function openConnexion() {
const fenetre = document.getElementById('fenetreConnexion');
fenetre.classList.remove('hidden');
fenetre.classList.add('flex');
}

function closeConnexion() {
const fenetre = document.getElementById('fenetreConnexion');
fenetre.classList.remove('flex');
fenetre.classList.add('hidden');
}

const audio = document.getElementById("audio");
const audioSuivant = document.getElementById("audioSuivant");
const boutonFondu = document.getElementById("boutonFondu");
let fonduActif = false;
let fonduEnCours = false;
let lectureEnPause = false;
const dureeFondu = 5;
const boutonLecture = document.getElementById("boutonLecture");
const boutonRepetition = document.getElementById("boutonRepetition");
let repetitionActive = false;
const boutonAleatoire = document.getElementById("boutonAleatoire");
let aleatoireActif = false;
let historiqueMusiques = [];
const barreProgression = document.getElementById("barreProgression");
const tempsActuel = document.getElementById("tempsActuel");
const dureeTotale = document.getElementById("dureeTotale");
const volume = document.getElementById("volume");
let volumePrecedent = 0.5;
let facteurVolumeActuel = 1;
let facteurVolumeSuivant = 0;
let fonduPrepare = false;

// Fondu enchainé
boutonFondu.addEventListener("click", () => {
    fonduActif = !fonduActif;

    boutonFondu.classList.toggle("bg-blue-600", fonduActif);
    boutonFondu.classList.toggle("rounded-full", fonduActif);

    boutonFondu.setAttribute("aria-pressed", String(fonduActif));

    boutonFondu.setAttribute(
        "aria-label",
        fonduActif
            ? "Désactiver le fondu enchaîné"
            : "Activer le fondu enchaîné"
    );
});

// Convertit les secondes en minutes:secondes
function formaterTemps(secondes) {
    if (!Number.isFinite(secondes)) {
        return "0:00";
    }

    const minutes = Math.floor(secondes / 60);
    const secondesRestantes = Math.floor(secondes % 60);

    return `${minutes}:${String(secondesRestantes).padStart(2, "0")}`;
}

// Lecture et pause
boutonLecture.addEventListener("click", async () => {
    if (audio.paused) {
        lectureEnPause = false;
        try {
            await audio.play();

            if (fonduEnCours) {
                await audioSuivant.play();
            }
        } catch (erreur) {
            console.error(
                "Impossible de reprendre la lecture :",
                erreur
            );
        }
    } else {
        lectureEnPause = true;
        audio.pause();

        if (fonduEnCours) {
            audioSuivant.pause();
        }
    }
});

// Met à jour l'icône du bouton
function actualiserBouton() {
    const iconeLecture = document.getElementById("iconeLecture");

    if (audio.paused) {
        iconeLecture.src = "icon/circle-play-solid-full.svg";
        boutonLecture.setAttribute("aria-label", "Lire la musique");
    } else {
        iconeLecture.src = "icon/circle-pause-solid-full.svg";
        boutonLecture.setAttribute("aria-label", "Mettre en pause");
    }
}

audio.addEventListener("play", actualiserBouton);
audio.addEventListener("pause", actualiserBouton);
audio.addEventListener("ended", actualiserBouton);


audio.addEventListener("ended", () => {
    // Le fondu gère déjà le passage au morceau suivant.
    if (fonduEnCours) {
        return;
    }

    if (cartesMusiques.length === 0) {
        return;
    }

    // La répétition est prioritaire sur le mode aléatoire
    if (repetitionActive) {
        audio.currentTime = 0;
        audio.play().catch((erreur) => {
            console.error("Impossible de relancer la musique :", erreur);
        });
        return;
    }

    // En mode aléatoire, choisir un morceau au hasard
    if (aleatoireActif) {
        let nouvelIndex;

        if (cartesMusiques.length === 1) {
            nouvelIndex = 0;
        } else {
            do {
                nouvelIndex = Math.floor(
                    Math.random() * cartesMusiques.length
                );
            } while (nouvelIndex === indexMusiqueActuelle);
        }

        selectionnerMusique(nouvelIndex);
        return;
    }

    // Sinon, passer au morceau suivant dans l'ordre normal
    const nouvelIndex =
        (indexMusiqueActuelle + 1) % cartesMusiques.length;

    selectionnerMusique(nouvelIndex);
});


// Affiche la durée totale
audio.addEventListener("loadedmetadata", () => {
    dureeTotale.textContent = formaterTemps(audio.duration);
});

// Actualise la progression pendant la lecture
audio.addEventListener("timeupdate", () => {
    tempsActuel.textContent = formaterTemps(audio.currentTime);

    if (Number.isFinite(audio.duration) && audio.duration > 0) {
        barreProgression.value =
            (audio.currentTime / audio.duration) * 100;
    }
    barreProgression.style.setProperty(
    "--progression",
    `${barreProgression.value}%`
    );
    // Détecter les cinq dernières secondes du morceau
    if (
        fonduActif &&
        !repetitionActive &&
        !fonduPrepare &&
        !fonduEnCours &&
        Number.isFinite(audio.duration) &&
        audio.duration - audio.currentTime <= dureeFondu
    ) {
        fonduPrepare = true;
        lancerFonduEnchaine();
    }
});

// Permet de changer de position dans le morceau
barreProgression.addEventListener("input", () => {
    if (Number.isFinite(audio.duration) && audio.duration > 0) {
        audio.currentTime =
            (Number(barreProgression.value) / 100) * audio.duration;
    }
});

// Contrôle du volume
function actualiserVolume() {
    const volumeChoisi = Number(volume.value);

    // Appliquer le volume choisi, modulé par le fondu
    audio.volume = volumeChoisi * facteurVolumeActuel;
    audioSuivant.volume = volumeChoisi * facteurVolumeSuivant;

    const pourcentage = volumeChoisi * 100;

    volume.style.setProperty("--volume", `${pourcentage}%`);

    const iconeVolume = document.getElementById("iconeVolume");

    if (pourcentage === 0) {
        iconeVolume.src = "icon/volume-xmark-solid-full.svg";
        iconeVolume.alt = "Volume coupé";
    } else if (pourcentage <= 30) {
        iconeVolume.src = "icon/volume-low-solid-full.svg";
        iconeVolume.alt = "Volume faible";
    } else if (pourcentage <= 70) {
        iconeVolume.src = "icon/volume-solid-full.svg";
        iconeVolume.alt = "Volume normal";
    } else {
        iconeVolume.src = "icon/volume-high-solid-full.svg";
        iconeVolume.alt = "Volume élevé";
    }

    localStorage.setItem("volumeSpooderfy", volume.value);
}

function basculerVolume() {
    if (Number(volume.value) > 0) {
        // Sauvegarder le volume actuel avant de couper le son
        volumePrecedent = Number(volume.value);

        volume.value = 0;
    } else {
        // Restaurer le volume précédent
        volume.value = volumePrecedent > 0 ? volumePrecedent : 0.5;
    }

    actualiserVolume();
}

volume.addEventListener("input", actualiserVolume);

const volumeSauvegarde = localStorage.getItem("volumeSpooderfy");

if (volumeSauvegarde !== null) {
    const valeur = Number(volumeSauvegarde);

    if (Number.isFinite(valeur) && valeur >= 0 && valeur <= 1) {
        volume.value = valeur;
    }

    if (valeur > 0) {
        volumePrecedent = valeur;
    }
}

actualiserVolume();

const toutesLesCartesMusiques = Array.from(
    document.querySelectorAll("[data-song-audio]")
);

// File de lecture actuelle : toutes les musiques ou une playlist
let cartesMusiques = [...toutesLesCartesMusiques];
let nomPlaylistActuelle = null;
let playlistIdActuelle = null;
let carteSelectionneePourPlaylist = null;
let indexMusiqueActuelle = -1;
let retourHistoriqueEnCours = false;
const recherchePrincipale = document.getElementById("recherchePrincipale");
const boutonEffacerRecherche = document.getElementById(
    "boutonEffacerRecherche"
);
const formulaireRecherchePrincipale = document.getElementById(
    "formulaireRecherchePrincipale"
);

function actualiserBoutonEffacerRecherche() {
    const afficherBouton =
        recherchePrincipale.value.length > 0 || playlistIdActuelle !== null;

    boutonEffacerRecherche.classList.toggle("hidden", !afficherBouton);
    boutonEffacerRecherche.classList.toggle("flex", afficherBouton);
}

recherchePrincipale.addEventListener(
    "input",
    actualiserBoutonEffacerRecherche
);

boutonEffacerRecherche.addEventListener("click", () => {
    recherchePrincipale.value = "";

    if (playlistIdActuelle !== null) {
        afficherCatalogueMusiques();
    }

    formulaireRecherchePrincipale.requestSubmit();
});

actualiserBoutonEffacerRecherche();

function actualiserBoutonsActionsMusique() {
    toutesLesCartesMusiques.forEach((carte) => {
        const boutonAjout = carte.querySelector(".boutonAjouterPlaylist");
        const boutonRetrait = carte.querySelector(
            ".boutonRetirerMusiquePlaylist"
        );
        const afficherRetrait =
            playlistIdActuelle !== null && cartesMusiques.includes(carte);

        boutonAjout.classList.toggle("hidden", playlistIdActuelle !== null);
        boutonAjout.classList.toggle("flex", playlistIdActuelle === null);
        boutonRetrait.classList.toggle("hidden", !afficherRetrait);
        boutonRetrait.classList.toggle("flex", afficherRetrait);
    });
}

function afficherCatalogueMusiques() {
    cartesMusiques = [...toutesLesCartesMusiques];
    nomPlaylistActuelle = null;
    playlistIdActuelle = null;
    historiqueMusiques = [];
    indexMusiqueActuelle = -1;

    toutesLesCartesMusiques.forEach((carte) => {
        carte.classList.remove("hidden");
    });

    document.getElementById("titreListeMusiques").textContent =
        "Toutes les musiques";
    document.getElementById("messageListeMusiques").classList.add("hidden");
    actualiserBoutonsActionsMusique();
    actualiserBoutonEffacerRecherche();
}

function obtenirIndexSuivant() {
    if (cartesMusiques.length === 0) {
        return -1;
    }

    if (aleatoireActif) {
        if (cartesMusiques.length === 1) {
            return 0;
        }

        let nouvelIndex;

        do {
            nouvelIndex = Math.floor(
                Math.random() * cartesMusiques.length
            );
        } while (nouvelIndex === indexMusiqueActuelle);

        return nouvelIndex;
    }

    return (indexMusiqueActuelle + 1) % cartesMusiques.length;
}

let animationFondu = null;
let identifiantFondu = 0;

function annulerFonduEnchaine() {
    // Invalider toute animation de fondu en cours
    identifiantFondu++;

    if (animationFondu !== null) {
        cancelAnimationFrame(animationFondu);
        animationFondu = null;
    }

    // Arrêter le lecteur secondaire
    audioSuivant.pause();
    audioSuivant.removeAttribute("src");
    audioSuivant.load();

    // Restaurer le volume normal du lecteur principal
    facteurVolumeActuel = 1;
    facteurVolumeSuivant = 0;

    fonduEnCours = false;
    fonduPrepare = false;

    actualiserVolume();
}

async function lancerFonduEnchaine() {
    if (
        fonduEnCours ||
        !fonduActif ||
        repetitionActive ||
        audio.paused
    ) {
        return;
    }

    const nouvelIndex = obtenirIndexSuivant();
    const carteSuivante = cartesMusiques[nouvelIndex];

    if (!carteSuivante) {
        return;
    }

    fonduEnCours = true;

    const volumeChoisi = Number(volume.value);
    const volumeDepart = audio.volume;
    const cheminSuivant = carteSuivante.dataset.songAudio;

    audioSuivant.pause();
    audioSuivant.src = cheminSuivant;
    audioSuivant.load();
    audioSuivant.volume = 0;

    const identifiantEnCours = identifiantFondu;

    try {
        await audioSuivant.play();

        if (identifiantEnCours !== identifiantFondu) {
            return;
        }

        function progresserFondu() {
            if (identifiantEnCours !== identifiantFondu) {
                return;
            }

            const progression = Math.min(
                audioSuivant.currentTime / dureeFondu,
                1
            );

            facteurVolumeActuel = 1 - progression;
            facteurVolumeSuivant = progression;

            actualiserVolume();

            if (progression < 1) {
                animationFondu = requestAnimationFrame(progresserFondu);
                return;
            }

            audio.pause();

            const cheminAudioSuivant = audioSuivant.currentSrc || audioSuivant.src;

            async function reprendreLecture() {
                if (identifiantEnCours !== identifiantFondu) {
                    audio.removeEventListener("loadedmetadata", reprendreLecture);
                    return;
                }

                if (
                    audio.currentSrc !== audio.src ||
                    audio.readyState < HTMLMediaElement.HAVE_METADATA
                ) {
                    return;
                }

                audio.removeEventListener("loadedmetadata", reprendreLecture);

                const positionSuivante = audioSuivant.currentTime;
                let terminerRecherche;
                const rechercheTerminee = new Promise((resolve) => {
                    terminerRecherche = resolve;
                    audio.addEventListener("seeked", resolve, { once: true });
                });

                audio.currentTime = positionSuivante;
                if (audio.seeking) {
                    await rechercheTerminee;
                } else {
                    audio.removeEventListener("seeked", terminerRecherche);
                }

                if (identifiantEnCours !== identifiantFondu) {
                    return;
                }

                facteurVolumeActuel = 0;
                facteurVolumeSuivant = 1;
                actualiserVolume();

                indexMusiqueActuelle = nouvelIndex;
                actualiserInformationsMusique(nouvelIndex);
                chargerCommentaires(carteSuivante.dataset.songId);
                barreProgression.value =
                    (audio.currentTime / audio.duration) * 100;
                tempsActuel.textContent = formaterTemps(audio.currentTime);
                dureeTotale.textContent = formaterTemps(audio.duration);

                if (!lectureEnPause) {
                    try {
                        await audio.play();
                    } catch (erreur) {
                        console.error(
                            "Impossible de reprendre la lecture :",
                            erreur
                        );
                        return;
                    }
                }

                audioSuivant.pause();
                audioSuivant.removeAttribute("src");
                audioSuivant.load();

                facteurVolumeActuel = 1;
                facteurVolumeSuivant = 0;
                actualiserVolume();

                fonduEnCours = false;
                fonduPrepare = false;
            }

            audio.addEventListener("loadedmetadata", reprendreLecture);
            audio.src = cheminAudioSuivant;
            audio.load();
    }

    animationFondu = requestAnimationFrame(progresserFondu);

    } catch (erreur) {
        if (identifiantEnCours !== identifiantFondu) {
            return;
        }

        console.error(
            "Impossible de lancer le fondu enchaîné :",
            erreur
        );

        audioSuivant.pause();
        audioSuivant.removeAttribute("src");
        audioSuivant.load();

        facteurVolumeActuel = 1;
        facteurVolumeSuivant = 0;
        actualiserVolume();

        fonduEnCours = false;
        fonduPrepare = false;
    }
}

function actualiserInformationsMusique(index) {
    const carte = cartesMusiques[index];

    if (!carte) {
        return;
    }

    const nomMusique = carte.dataset.songName;
    const cheminPochette = carte.dataset.songCoverage;

    const pochetteMusique = document.getElementById("pochetteMusique");
    const couvertureActuelle = document.getElementById("couvertureActuelle");
    const titreActuel = document.getElementById("titreActuel");

    cartesMusiques.forEach((carte) => {
        carte.removeAttribute("data-playing");
    });

    carte.setAttribute("data-playing", "true");

    if (cheminPochette) {
        pochetteMusique.src = cheminPochette;
        pochetteMusique.alt = "Pochette de " + nomMusique;

        couvertureActuelle.src = cheminPochette;
        couvertureActuelle.alt = "Pochette de " + nomMusique;
    } else {
        pochetteMusique.removeAttribute("src");
        pochetteMusique.alt = "Aucune pochette disponible";

        couvertureActuelle.removeAttribute("src");
        couvertureActuelle.alt = "Aucune pochette disponible";
    }

    document.getElementById("titreMusique").textContent = nomMusique;
    titreActuel.textContent = nomMusique;
}

async function selectionnerMusique(index) {
    annulerFonduEnchaine();
    lectureEnPause = false;

    const carte = cartesMusiques[index];

    if (!carte) {
        return;
    }

    chargerCommentaires(carte.dataset.songId);
    document.getElementById("lecteurAudio").classList.remove("contenu-cache");
    document.getElementById("musiqueActuelle").classList.remove("contenu-cache");

    // Enregistrer le morceau quitté, sauf lors d'un retour en arrière
    if (
        !retourHistoriqueEnCours &&
        indexMusiqueActuelle !== -1 &&
        indexMusiqueActuelle !== index
    ) {
        historiqueMusiques.push(indexMusiqueActuelle);
    }

    retourHistoriqueEnCours = false;

    // Retirer la mise en évidence de toutes les cartes
    toutesLesCartesMusiques.forEach((carte) => {
        carte.removeAttribute("data-playing");
    });

    // Mettre en évidence la carte sélectionnée
    cartesMusiques[index].setAttribute("data-playing", "true");

    // Mémoriser la nouvelle position
    indexMusiqueActuelle = index;

    const cheminAudio = carte.dataset.songAudio;
    const nomMusique = carte.dataset.songName;
    const cheminPochette = carte.dataset.songCoverage;
    const pochetteMusique = document.getElementById("pochetteMusique");
    const couvertureActuelle = document.getElementById("couvertureActuelle");
    const titreActuel = document.getElementById("titreActuel");

    // Mettre à jour la pochette
    if (cheminPochette) {
        pochetteMusique.src = cheminPochette;
        pochetteMusique.alt = "Pochette de " + nomMusique;
        couvertureActuelle.src = cheminPochette;
        couvertureActuelle.alt = "Pochette de " + nomMusique;
    } else {
        pochetteMusique.removeAttribute("src");
        pochetteMusique.alt = "Aucune pochette disponible";

        couvertureActuelle.removeAttribute("src");
        couvertureActuelle.alt = "Aucune pochette disponible";
    }

    // Mettre à jour le titre
    document.getElementById("titreMusique").textContent = nomMusique;
    titreActuel.textContent = nomMusique;

    // Charger le nouveau fichier audio
    audio.src = cheminAudio;
    audio.load();

    // Réinitialiser la barre de progression et les temps
    barreProgression.value = 0;
    tempsActuel.textContent = "0:00";
    dureeTotale.textContent = "0:00";

    // Lancer la lecture
    try {
        await audio.play();
    } catch (erreur) {
        console.error(
            "Impossible de lire la musique sélectionnée :",
            erreur
        );
    }
}

toutesLesCartesMusiques.forEach((carte) => {
    carte.addEventListener("click", async () => {
        if (nomPlaylistActuelle === null) {
            historiqueMusiques = [];
            indexMusiqueActuelle = -1;
        }

        await selectionnerMusique(cartesMusiques.indexOf(carte));
    });
});

const boutonSuivant = document.getElementById("boutonSuivant");

boutonSuivant.addEventListener("click", () => {
    if (cartesMusiques.length === 0) {
        return;
    }

    const nouvelIndex = aleatoireActif
        ? obtenirIndexSuivant()
        : indexMusiqueActuelle === -1
            ? 0
            : (indexMusiqueActuelle + 1) % cartesMusiques.length;

    selectionnerMusique(nouvelIndex);
});

const boutonPrecedent = document.getElementById("boutonPrecedent");

boutonPrecedent.addEventListener("click", () => {
    if (cartesMusiques.length === 0) {
        return;
    }

    // Si le morceau a commencé depuis plus de 3 secondes,
    // revenir au début du morceau actuel
    if (audio.currentTime > 3) {
        audio.currentTime = 0;
        return;
    }

    // Si aucune musique n'est sélectionnée, choisir la dernière
    if (indexMusiqueActuelle === -1) {
        selectionnerMusique(cartesMusiques.length - 1);
        return;
    }
       
    // En mode aléatoire, revenir au morceau précédemment écouté
    if (aleatoireActif && historiqueMusiques.length > 0) {
        const indexPrecedent = historiqueMusiques.pop();

        retourHistoriqueEnCours = true;
        selectionnerMusique(indexPrecedent);
        return;
    }

    // Reculer d'un morceau, en revenant à la fin si nécessaire
    const nouvelIndex =
        (indexMusiqueActuelle - 1 + cartesMusiques.length)
        % cartesMusiques.length;

    selectionnerMusique(nouvelIndex);
});

boutonRepetition.addEventListener("click", () => {
    repetitionActive = !repetitionActive;

    // Changer la couleur de fond du bouton
    boutonRepetition.classList.toggle("bg-blue-600", repetitionActive);
    boutonRepetition.classList.toggle("rounded-full", repetitionActive);

    boutonRepetition.setAttribute(
        "aria-pressed",
        String(repetitionActive)
    );
});

boutonAleatoire.addEventListener("click", () => {
    aleatoireActif = !aleatoireActif;

    // Modifier l'apparence du bouton selon son état
    boutonAleatoire.classList.toggle("bg-blue-600", aleatoireActif);
    boutonAleatoire.classList.toggle("rounded-full", aleatoireActif);

    boutonAleatoire.setAttribute(
        "aria-pressed",
        String(aleatoireActif)
    );
});

function openPlaylist() {
    const fenetre = document.getElementById("fenetrePlaylist");

    fenetre.classList.remove("hidden");
    fenetre.classList.add("flex");
}

function closePlaylist() {
    const fenetre = document.getElementById("fenetrePlaylist");

    fenetre.classList.remove("flex");
    fenetre.classList.add("hidden");
}

function ouvrirAjoutPlaylist(carte) {
    const fenetre = document.getElementById("fenetreAjoutPlaylist");
    const statut = document.getElementById("statutAjoutPlaylist");

    carteSelectionneePourPlaylist = carte;
    document.getElementById("musiqueAjoutPlaylist").textContent =
        carte.dataset.songName;
    statut.textContent = "";
    statut.classList.add("hidden");
    fenetre.classList.remove("hidden");
    fenetre.classList.add("flex");
}

function fermerAjoutPlaylist() {
    const fenetre = document.getElementById("fenetreAjoutPlaylist");

    fenetre.classList.remove("flex");
    fenetre.classList.add("hidden");
}

document.querySelectorAll(".boutonAjouterPlaylist").forEach((bouton) => {
    bouton.addEventListener("click", (evenement) => {
        evenement.stopPropagation();
        ouvrirAjoutPlaylist(bouton.parentElement);
    });
});

document.querySelectorAll("[data-ajouter-a-playlist]").forEach((bouton) => {
    bouton.addEventListener("click", async () => {
        const carte = carteSelectionneePourPlaylist;
        const statut = document.getElementById("statutAjoutPlaylist");

        if (!carte) {
            statut.textContent = "Impossible d’identifier la musique sélectionnée.";
            statut.classList.remove("hidden", "text-green-400");
            statut.classList.add("text-red-400");
            return;
        }

        bouton.disabled = true;
        statut.textContent = "Ajout en cours…";
        statut.classList.remove("hidden", "text-red-400", "text-green-400");
        statut.classList.add("text-gray-300");

        try {
            const reponse = await fetch("ajouter-musique-playlist.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: new URLSearchParams({
                    playlist_id: bouton.dataset.ajouterAPlaylist,
                    song_id: carte.dataset.songId
                })
            });
            const resultat = await reponse.json();

            if (!reponse.ok) {
                throw new Error(
                    resultat.erreur || "Impossible d’ajouter cette musique."
                );
            }

            statut.textContent =
                `Musique ajoutée à « ${bouton.dataset.playlistName} ».`;
            statut.classList.remove("text-gray-300", "text-red-400");
            statut.classList.add("text-green-400");

            if (
                String(playlistIdActuelle) === bouton.dataset.ajouterAPlaylist &&
                !cartesMusiques.includes(carte)
            ) {
                cartesMusiques.push(carte);
                carte.classList.remove("hidden");
                actualiserBoutonsActionsMusique();
            }
        } catch (erreur) {
            console.error("Erreur lors de l’ajout à la playlist :", erreur);
            statut.textContent = erreur.message;
            statut.classList.remove("text-gray-300", "text-green-400");
            statut.classList.add("text-red-400");
        } finally {
            bouton.disabled = false;
        }
    });
});

document
    .querySelectorAll(".boutonRetirerMusiquePlaylist")
    .forEach((bouton) => {
        bouton.addEventListener("click", async (evenement) => {
            evenement.stopPropagation();

            const carte = bouton.closest("[data-song-id]");
            const playlistId = playlistIdActuelle;

            if (!carte || playlistId === null) {
                return;
            }

            if (
                !window.confirm(
                    `Retirer « ${carte.dataset.songName} » de « ${nomPlaylistActuelle} » ?`
                )
            ) {
                return;
            }

            bouton.disabled = true;

            try {
                const reponse = await fetch("retirer-musique-playlist.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: new URLSearchParams({
                        playlist_id: playlistId,
                        song_id: carte.dataset.songId
                    })
                });
                const resultat = await reponse.json();

                if (!reponse.ok) {
                    throw new Error(
                        resultat.erreur || "Impossible de retirer cette musique."
                    );
                }

                if (String(playlistIdActuelle) !== playlistId) {
                    return;
                }

                if (fonduEnCours) {
                    annulerFonduEnchaine();
                }

                const indexRetire = cartesMusiques.indexOf(carte);

                if (indexRetire !== -1) {
                    cartesMusiques.splice(indexRetire, 1);
                    carte.classList.add("hidden");

                    if (indexRetire === indexMusiqueActuelle) {
                        carte.removeAttribute("data-playing");
                        indexMusiqueActuelle = -1;
                    } else if (indexRetire < indexMusiqueActuelle) {
                        indexMusiqueActuelle--;
                    }
                }

                actualiserBoutonsActionsMusique();

                if (cartesMusiques.length === 0) {
                    const message = document.getElementById("messageListeMusiques");
                    message.textContent =
                        "Cette playlist ne contient aucune musique.";
                    message.classList.remove("hidden");
                }
            } catch (erreur) {
                console.error("Erreur lors du retrait de la playlist :", erreur);
                window.alert(erreur.message);
            } finally {
                bouton.disabled = false;
            }
        });
    });

document.querySelectorAll(".carte-selection").forEach((carte) => {
    const checkbox = carte.querySelector('input[type="checkbox"]');

    // État initial
    carte.dataset.select = String(checkbox.checked);

    checkbox.addEventListener("change", () => {
        carte.dataset.select = String(checkbox.checked);
    });
});
const ordreMusiques = document.getElementById("ordreMusiques");

const casesMusiques = document.querySelectorAll(
    "#fenetrePlaylist [data-song-checkbox]"
);

const rechercheMusiquePlaylist = document.getElementById(
    "rechercheMusiquePlaylist"
);
document
    .getElementById("boutonRechercherMusiquePlaylist")
    .addEventListener("click", () => rechercheMusiquePlaylist.focus());

const messageAucunResultatPlaylist = document.getElementById(
    "aucunResultatRecherchePlaylist"
);

rechercheMusiquePlaylist.addEventListener("input", () => {
    const recherche = rechercheMusiquePlaylist.value
        .trim()
        .toLocaleLowerCase();
    let nombreResultats = 0;

    casesMusiques.forEach((checkbox) => {
        const carte = checkbox.closest(".carte-selection");
        const nomMusique = carte.textContent.trim().toLocaleLowerCase();
        const correspond = nomMusique.includes(recherche);

        carte.classList.toggle("hidden", !correspond);

        if (correspond) {
            nombreResultats++;
        }
    });

    messageAucunResultatPlaylist.classList.toggle(
        "hidden",
        nombreResultats > 0
    );
});

let ordreSelection = [];

casesMusiques.forEach((checkbox) => {
    checkbox.addEventListener("change", () => {
        const idMusique = checkbox.value;
        const carte = checkbox.closest("label");

        if (checkbox.checked) {
            // Ajouter à la fin de l'ordre de sélection
            if (!ordreSelection.includes(idMusique)) {
                ordreSelection.push(idMusique);
            }

            carte.dataset.select = "true";
        } else {
            // Retirer la musique désélectionnée
            ordreSelection = ordreSelection.filter(
                id => id !== idMusique
            );

            delete carte.dataset.select;
        }

        // Reconstruire les champs envoyés à PHP
        ordreMusiques.replaceChildren();

        ordreSelection.forEach((id) => {
            const input = document.createElement("input");

            input.type = "hidden";
            input.name = "song_ids[]";
            input.value = id;

            ordreMusiques.appendChild(input);
        });
    });
});
const boutonsPlaylists = document.querySelectorAll(
    "[data-playlist-id]"
);

boutonsPlaylists.forEach((bouton) => {
    bouton.addEventListener("click", async () => {
        const playlistId = bouton.dataset.playlistId;

        try {
            const reponse = await fetch(
                `charger-playlist.php?id=${encodeURIComponent(playlistId)}`
            );

            if (!reponse.ok) {
                throw new Error("Impossible de charger la playlist.");
            }

            const resultat = await reponse.json();

            if (!Array.isArray(resultat)) {
                throw new Error(
                    resultat.erreur || "Réponse invalide du serveur."
                );
            }

            // Retrouver les cartes correspondant aux morceaux reçus.
            const cartesPlaylist = resultat.map((musique) => {
                return toutesLesCartesMusiques.find(
                    (carte) =>
                        carte.dataset.songId === String(musique.id)
                );
            }).filter(Boolean);

            const nomPlaylist =
                bouton.querySelector("span")?.textContent.trim()
                || "Playlist";

            cartesMusiques = cartesPlaylist;
            nomPlaylistActuelle = nomPlaylist;
            playlistIdActuelle = playlistId;
            historiqueMusiques = [];
            indexMusiqueActuelle = -1;
            actualiserBoutonsActionsMusique();
            actualiserBoutonEffacerRecherche();

            toutesLesCartesMusiques.forEach((carte) => {
                carte.classList.toggle(
                    "hidden",
                    !cartesPlaylist.includes(carte)
                );
            });

            document.getElementById("titreListeMusiques").textContent =
                nomPlaylist;

            if (cartesPlaylist.length === 0) {
                const message = document.getElementById("messageListeMusiques");
                message.textContent = "Cette playlist ne contient aucune musique.";
                message.classList.remove("hidden");
                return;
            }

            document
                .getElementById("messageListeMusiques")
                .classList.add("hidden");

            // Lire le premier morceau de la playlist.
            await selectionnerMusique(0);

        } catch (erreur) {
            console.error(
                "Erreur lors du chargement de la playlist :",
                erreur
            );

            alert("Impossible de charger cette playlist.");
        }
    });
});

document
    .querySelectorAll("[data-supprimer-playlist-id]")
    .forEach((bouton) => {
        bouton.addEventListener("click", async (evenement) => {
            evenement.stopPropagation();

            const playlistId = bouton.dataset.supprimerPlaylistId;
            const nomPlaylist = bouton.dataset.playlistName;

            if (
                !window.confirm(
                    `Supprimer définitivement la playlist « ${nomPlaylist} » ?`
                )
            ) {
                return;
            }

            bouton.disabled = true;

            try {
                const reponse = await fetch("supprimer-playlist.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: new URLSearchParams({
                        playlist_id: playlistId
                    })
                });
                const resultat = await reponse.json();

                if (!reponse.ok) {
                    throw new Error(
                        resultat.erreur || "Impossible de supprimer la playlist."
                    );
                }

                const carteActive =
                    String(playlistIdActuelle) === playlistId
                        ? cartesMusiques[indexMusiqueActuelle]
                        : null;

                const listePlaylists =
                    bouton.closest(".cartePlaylist").parentElement;
                bouton.closest(".cartePlaylist").remove();

                if (!document.querySelector("[data-playlist-id]")) {
                    let message = document.getElementById(
                        "messageAucunePlaylist"
                    );

                    if (!message) {
                        message = document.createElement("p");
                        message.id = "messageAucunePlaylist";
                        message.className = "px-2 text-sm text-gray-400";
                        message.textContent = "Tu n'as pas encore de playlist.";
                        listePlaylists.appendChild(message);
                    } else {
                        message.classList.remove("hidden");
                    }
                }

                if (String(playlistIdActuelle) === playlistId) {
                    afficherCatalogueMusiques();

                    if (carteActive) {
                        indexMusiqueActuelle =
                            cartesMusiques.indexOf(carteActive);
                    }
                }
            } catch (erreur) {
                console.error("Erreur lors de la suppression de la playlist :", erreur);
                window.alert(erreur.message);
                bouton.disabled = false;
            }
        });
    });

async function chargerCommentaires(songId) {
    const listeCommentaires = document.getElementById("listeCommentaires");

    // Vider la liste pendant le chargement
    listeCommentaires.replaceChildren();

    try {
        const reponse = await fetch(
            `charger-commentaires.php?song_id=${encodeURIComponent(songId)}`
        );

        if (!reponse.ok) {
            throw new Error("Erreur lors du chargement des commentaires.");
        }

        const commentaires = await reponse.json();

        if (commentaires.length === 0) {
            const message = document.createElement("p");
            message.className = "text-gray-400";
            message.textContent = "Aucun commentaire pour cette musique.";

            listeCommentaires.appendChild(message);
            return;
        }

        commentaires.forEach((commentaire) => {
            const bloc = document.createElement("article");
            bloc.className = "mb-4 rounded-lg bg-[#000A1C] p-3";

            const auteur = document.createElement("p");
            auteur.className = "mb-1 font-semibold";
            auteur.style.color =
                typeof commentaire.username_color === "string" &&
                /^#[0-9a-f]{6}$/i.test(commentaire.username_color)
                    ? commentaire.username_color
                    : "#a855f7";
            auteur.textContent = commentaire.username;

            const texte = document.createElement("p");
            texte.className = "whitespace-pre-wrap wrap-break-word text-white";
            texte.textContent = commentaire.text;

            bloc.append(auteur, texte);
            listeCommentaires.appendChild(bloc);
        });

    } catch (erreur) {
        console.error(erreur);

        const message = document.createElement("p");
        message.className = "text-red-400";
        message.textContent = "Impossible de charger les commentaires.";

        listeCommentaires.appendChild(message);
    }
}

function ouvrirCommentaire() {
    const fenetre = document.getElementById("fenetreCommentaire");

    fenetre.classList.remove("hidden");
    fenetre.classList.add("flex");

    document.getElementById("texteCommentaire").focus();
}

function fermerCommentaire() {
    const fenetre = document.getElementById("fenetreCommentaire");

    fenetre.classList.add("hidden");
    fenetre.classList.remove("flex");
}

const formulaireCommentaire = document.getElementById("formulaireCommentaire");


formulaireCommentaire.addEventListener("submit", async (event) => {
    event.preventDefault();

    const champTexte = document.getElementById("texteCommentaire");
    const texte = champTexte.value.trim();

    if (texte === "") {
        return;
    }

    const carte = cartesMusiques[indexMusiqueActuelle];

    if (!carte) {
        alert("Sélectionne d'abord une musique.");
        return;
    }

    const donnees = new FormData();

    donnees.append("song_id", carte.dataset.songId);
    donnees.append("text", texte);

    try {
        const reponse = await fetch("enregistrer-commentaire.php", {
            method: "POST",
            body: donnees
        });

        const resultat = await reponse.json();

        if (!reponse.ok) {
            throw new Error(
                resultat.erreur || "Impossible de publier le commentaire."
            );
        }

        champTexte.value = "";
        fermerCommentaire();

        await chargerCommentaires(carte.dataset.songId);

    } catch (erreur) {
        console.error(erreur);
        alert(erreur.message || "Une erreur est survenue lors de la publication.");
    }
});

document.getElementById("iconeVolume").addEventListener("click", basculerVolume);
