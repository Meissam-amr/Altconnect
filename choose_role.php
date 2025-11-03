<?php
require_once __DIR__ . '/includes/init.php';
include __DIR__ . '/includes/header.php';
?>

<section class="role-page">
  <h1 class="role-title">Inscription</h1>
  <p class="role-subtitle">Choisissez votre type de compte :</p>

  <div class="role-grid">

    <!-- Carte Étudiant -->
    <article class="role-card">
      <div class="role-emoji" aria-hidden="true">🎓</div>
      <h2 class="role-name">Étudiant</h2>
      <p class="role-desc">
        Créez un profil, déposez un CV, recherchez des offres et suivez vos candidatures.
      </p>
      <a class="role-btn" href="<?= BASE ?>/register_etudiant.php">Continuer</a>
    </article>

    <!-- Carte Entreprise -->
    <article class="role-card">
      <div class="role-emoji" aria-hidden="true">🏢</div>
      <h2 class="role-name">Entreprise</h2>
      <p class="role-desc">
        Publiez des offres, gérez les candidatures reçues et mettez à jour leur statut.
      </p>
      <a class="role-btn" href="<?= BASE ?>/register_entreprise.php">Continuer</a>
    </article>

  </div>

  <p class="role-bottom">
    Vous avez déjà un compte ? <a href="<?= BASE ?>/login.php">Se connecter</a>
  </p>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
