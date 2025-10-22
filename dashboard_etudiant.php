<?php
require_once __DIR__ . '/includes/init.php'; // charge la session + base + auth

require_login(); // empêche l'accès si non connecté

include __DIR__ . '/includes/header.php';

// Vérifie que seul un étudiant peut accéder à cette page
if (current_user_role() !== 'etudiant') {
    echo "<p>Accès réservé aux étudiants.</p>";
    include __DIR__ . '/includes/footer.php';
    exit;
}
?>

<h2>Mon espace Étudiant</h2>

<ul>
  <li><a href="offres.php">Parcourir les offres</a></li>
  <li><a href="profil.php">Compléter mon profil (à venir)</a></li>
</ul>

<?php include __DIR__ . '/includes/footer.php'; ?>
