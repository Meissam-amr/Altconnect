<?php
require_once __DIR__.'/includes/init.php';
require_role('etudiant');
include __DIR__.'/includes/header.php';
?>
<h2>Espace étudiant</h2>
<ul>
  <li><a href="<?= BASE ?>/offres/offres_list.php">Voir les offres</a></li>
  <li><a href="<?= BASE ?>/mes_candidatures.php">Mes candidatures</a></li>
  <li><a href="<?= BASE ?>/profil_etudiant.php">Mon profil</a></li>
</ul>
<?php include __DIR__.'/includes/footer.php'; ?>
