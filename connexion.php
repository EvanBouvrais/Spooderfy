<?php

$user = 'root';
$pass = '';

try {
    $mysqlClient = new PDO('mysql:host=localhost;dbname=spooderfy', $user, $pass);
} catch (PDOException $e) {
    print "Erreur !: " . $e->getMessage() . "<br/>";
    die();
}
