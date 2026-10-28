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