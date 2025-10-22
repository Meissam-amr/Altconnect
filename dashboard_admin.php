<?php
require_once __DIR__ . '/includes/init.php';  // charge tout (session + DB + auth)

require_login(); // oblige à être connecté

include __DIR__ . '/includes/header.php';

// Vérifie que seul un admin peut accéder à cette page
if (current_user_role() !== 'admin') {
    echo "<p>Accès refusé ❌ — Cette page est réservée à l'administrateur.</p>";
    include __DIR__ . '/includes/footer.php';
    exit;
}
?>
<h2>Dashboard Admin</h2>

<ul>
  <li><a href="entreprises_list.php">Voir toutes les entreprises</a></li>
  <li><a href="offres.php">Gérer les offres (à venir)</a></li>
</ul>

<?php include __DIR__ . '/includes/footer.php'; ?>
