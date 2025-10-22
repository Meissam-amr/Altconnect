<?php 
include __DIR__.'/includes/header.php';
require_once __DIR__ . '/includes/init.php';
?>
<h2>Inscription</h2>
<p>Choisissez votre profil :</p>
<div style="display:flex;gap:16px;flex-wrap:wrap">
  <div class="card" style="flex:1;min-width:240px">
    <h3>Étudiant</h3>
    <p>Créer un profil candidat et postuler aux offres.</p>
    <a class="btn" href="register_etudiant.php">Continuer</a>
  </div>
  <div class="card" style="flex:1;min-width:240px">
    <h3>Entreprise</h3>
    <p>Publier des offres et gérer les candidatures.</p>
    <a class="btn" href="register_entreprise.php">Continuer</a>
  </div>
</div>
<?php include __DIR__.'/includes/footer.php'; ?>
