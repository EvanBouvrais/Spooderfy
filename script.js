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