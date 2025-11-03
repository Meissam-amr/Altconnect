<?php
require_once __DIR__.'/includes/init.php';
require_role('entreprise');
include __DIR__.'/includes/header.php';
?>
<h2>Espace entreprise</h2>
<ul>
  <li><a href="<?= BASE ?>/offres/offres_new.php">Publier une offre</a></li>
  <li><a href="<?= BASE ?>/offres/offres_list.php">Mes offres</a></li>
  <li><a href="<?= BASE ?>/candidatures_reçues.php">Candidatures reçues</a></li>
  <li><a href="<?= BASE ?>/profil_entreprise.php">Profil entreprise</a></li>
</ul>
<?php include __DIR__.'/includes/footer.php'; ?>
