<?php
session_start();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="./output.css" rel="stylesheet">
</head>


<body class="h-screen w-screen flex flex-row bg-[#000817]">
    
    <!-- Hello, je suis la zone qui sert à afficher tout ce qui se trouve a gauche de l'écran
    soit les playlist et la connexion utilisateur -->
    <aside class="flex h-screen w-75 shrink-0 flex-col border-r-2 border-t-2 rounded-[15px] border-[#004FD9] bg-[#000A1C]">
        <!-- Hello je sert à afficher les playlists de mon utilisateur -->
        <section>

        </section>

        <!-- Hello je sert à connecter mon utilisateur et afficher son pseudo quand il est
        connecté -->
        <section class="mt-auto w-75 h-45 border-r-2 border-t-2 border-[#0055FF] rounded-[15px]">
            <p class="text-white mt-5 ml-5">Session actuelle:</p>
            <?php
            if (isset($_SESSION['username'])): ?>

                    <p class="text-white mt-5 ml-5">
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
         <header class="w-full h-20 border-b bg-[#000C21] border-[#004FD9] rounded-b-[15px] flex justify-center items-center">
            <form  method="POST" action="">
                <input class="w-110 h-10 border-b bg-[#000C21] border-[#004FD9] rounded-t-[15px] rounded-b-[15px] text-white" type="text" name="recherche" placeholder="Rechercher...">
            </form>
        </header>

        <!-- Hello, je sert à afficher les musiques -->
        <section>

        </section>

        <!-- Hello, je sert à afficher le lecteur audio -->
        <section class="w-full mt-auto h-45 border-t bg-[#000C21] border-[#004FD9] rounded-t-[15px]">

        </section>

    </main>




    <!-- Hello, je sert à afficher tout ce qui est à droite de l'écran,
    soit la musique actuelle et les commentaires -->
    <aside class="flex h-screen w-105 shrink-0 ml-auto flex-col border-l-2 border-t-2 rounded-[15px] border-[#004FD9] bg-[#000A1C]">

        <!-- Hello, je sert à afficher la musique actuelle -->
        <section>

        </section>

        <!-- Hello, je sert a afficher les commentaires -->
        <section>

        </section>

    </aside>

    <script src="script.js"></script>
</body>
</html>