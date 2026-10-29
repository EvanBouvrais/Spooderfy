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
        try {
            await audio.play();
        } catch (erreur) {
            console.error("Impossible de lire le fichier audio :", erreur);
        }
    } else {
        audio.pause();
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
});

// Permet de changer de position dans le morceau
barreProgression.addEventListener("input", () => {
    if (Number.isFinite(audio.duration) && audio.duration > 0) {
        audio.currentTime =
            (Number(barreProgression.value) / 100) * audio.duration;
    }
});

// Contrôle du volume
volume.addEventListener("input", () => {
    audio.volume = Number(volume.value);
});

// Volume initial
audio.volume = Number(volume.value);


const toutesLesCartesMusiques = Array.from(
    document.querySelectorAll("[data-song-audio]")
);

// File de lecture actuelle : toutes les musiques ou une playlist
let cartesMusiques = [...toutesLesCartesMusiques];

let nomPlaylistActuelle = null;
let indexMusiqueActuelle = -1;
let retourHistoriqueEnCours = false;

async function selectionnerMusique(index) {
    const carte = cartesMusiques[index];

    if (!carte) {
        return;
    }

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

    // Mettre à jour la pochette
    if (cheminPochette) {
        pochetteMusique.src = cheminPochette;
        pochetteMusique.alt = "Pochette de " + nomMusique;
    } else {
        pochetteMusique.removeAttribute("src");
        pochetteMusique.alt = "Aucune pochette disponible";
    }

    // Mettre à jour le titre
    document.getElementById("titreMusique").textContent = nomMusique;

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
    carte.addEventListener("click", () => {
        // Un clic dans le catalogue complet quitte la playlist.
        cartesMusiques = [...toutesLesCartesMusiques];
        nomPlaylistActuelle = null;

        historiqueMusiques = [];

        const index = cartesMusiques.indexOf(carte);

        indexMusiqueActuelle = -1;

// Réafficher toutes les musiques du catalogue
toutesLesCartesMusiques.forEach((carte) => {
    carte.classList.remove("hidden");
});
cartesMusiques = cartesPlaylist;

        selectionnerMusique(index);
    });
});

const boutonSuivant = document.getElementById("boutonSuivant");

boutonSuivant.addEventListener("click", () => {
    if (aleatoireActif) {
        // Choisir une musique au hasard
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

    // Si aucune musique n'est sélectionnée, commencer par la première
    if (indexMusiqueActuelle === -1) {
        selectionnerMusique(0);
        return;
    }

    // Sinon, passer à la musique suivante dans l'ordre normal
    const nouvelIndex =
        (indexMusiqueActuelle + 1) % cartesMusiques.length;

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

            if (cartesPlaylist.length === 0) {
                alert("Cette playlist ne contient aucune musique.");
                return;
            }

            // Remplacer la file de lecture par cette playlist.
            cartesMusiques = cartesPlaylist;
            nomPlaylistActuelle =
                bouton.querySelector("span")?.textContent.trim()
                || "Playlist";

            // Réinitialiser l'historique et la position.
            historiqueMusiques = [];
            indexMusiqueActuelle = -1;

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