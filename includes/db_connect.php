<?php
// Ce fichier permet de se connecter à la base de données MySQL avec PDO.


//  On crée une fonction "db()" qui fera la connexion à chaque fois qu’on l’appelle.
function db(): PDO {
    static $pdo = null; // permet de réutiliser la même connexion sans la refaire

    // si la connexion existe déjà, on la renvoie directement
    if ($pdo !== null) return $pdo;

    // Informations de connexion :
    $host = '127.0.0.1';     // ou 'localhost' : c’est ton serveur local WAMP
    $db   = 'altconnect';    // le nom exact de ta base dans phpMyAdmin
    $user = 'root';          // utilisateur par défaut dans WAMP
    $pass = '';              // mot de passe vide par défaut
    $charset = 'utf8mb4';    // encodage moderne pour gérer les accents

    // Construction du "chemin" de connexion
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

    //  Options de sécurité et de confort
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // affiche les erreurs SQL
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // résultat sous forme de tableau associatif
        PDO::ATTR_EMULATE_PREPARES   => false,                  // meilleures requêtes préparées
    ];

    //  On essaie de se connecter
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        return $pdo;
    } catch (PDOException $e) {
        //  Si la connexion échoue, on affiche le message d’erreur
        exit('❌ Erreur de connexion : ' . $e->getMessage());
    }
}
