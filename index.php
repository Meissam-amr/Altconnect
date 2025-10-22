<?php
// On inclut notre fichier de connexion
require_once __DIR__ . '/includes/db_connect.php';

// On appelle la fonction "db()" pour se connecter
$pdo = db();

// On envoie une petite requête pour tester la base de données
$stmt = $pdo->query('SELECT NOW() AS now_time');

// On récupère la réponse (la date/heure actuelle du serveur MySQL)
$row = $stmt->fetch();

// On affiche le résultat dans la page
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Test connexion AltConnect</title>
</head>
<body>
  <h1>Connexion MySQL réussie ✅</h1>
  <p>Heure du serveur MySQL : <strong><?= htmlspecialchars($row['now_time']) ?></strong></p>
</body>
</html>
