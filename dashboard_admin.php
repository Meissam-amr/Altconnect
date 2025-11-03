<?php
require_once __DIR__.'/includes/init.php';
require_role('admin');
include __DIR__.'/includes/header.php';
?>
<h2>Tableau de bord administrateur</h2>
<ul>
  <li><a href="<?= BASE ?>/admin_users.php">Gérer les utilisateurs</a></li>
  <li><a href="<?= BASE ?>/offres/offres_list.php">Voir toutes les offres</a></li>
  <li><a href="<?= BASE ?>/stats.php">Statistiques</a></li>
</ul>
<?php include __DIR__.'/includes/footer.php'; ?>
