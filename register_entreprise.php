<?php
// register_entreprise.php
declare(strict_types=1);

// Charge les helpers (db(), current_user_role(), démarrage session, etc.)
require_once __DIR__ . '/includes/init.php';

// CSRF
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = (string)$_SESSION['csrf'];

$page_title = 'Inscription Entreprise';
include __DIR__ . '/includes/header.php';
?>

<main class="container page register">
  <h1 class="register-title">Inscription Entreprise</h1>
  <p class="register-lead">Créez votre compte entreprise et publiez vos offres.</p>

  <div class="register-card">
    <form method="post" action="save_register_entreprise.php" enctype="multipart/form-data" novalidate>
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
      <input type="hidden" name="role" value="entreprise">

      <div class="form-group">
        <label>Nom d'entreprise *</label>
        <input type="text" name="nom_entreprise" placeholder="Ex : AltConnect " required>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label>E-mail (compte) *</label>
          <input type="email" name="email" placeholder="ex: contact@exemple.com" required>
        </div>
        <div class="form-group">
          <label>Site web </label>
          <input type="text" name="site_web" placeholder="https://www.exemple.com" required>
        </div>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label>Mot de passe *</label>
          <input type="password" name="password" minlength="8" required>
        </div>
        <div class="form-group">
          <label>Confirmer le mot de passe *</label>
          <input type="password" name="password_confirm" minlength="8" required>
        </div>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label>Secteur</label>
          <input type="text" name="secteur" placeholder="Ex : Informatique, BTP…">
        </div>
        <div class="form-group">
          <label>Localisation</label>
          <input type="text" name="localisation" placeholder="Ex : Calais">
        </div>
      </div>

      <div class="form-group">
        <label>Description</label>
        <input type="text" name="description" placeholder="Décrivez brièvement votre entreprise">
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label>E-mail (contact)</label>
          <input type="email" name="contact_email" placeholder="ex: rh@exemple.com">
        </div>
        <div class="form-group">
          <label>Téléphone (contact)</label>
          <input type="text" name="contact_tel" placeholder="Ex : 06 12 34 56 78">
        </div>
      </div>

      <div class="form-group">
        <label>Logo (JPG/PNG, 2 Mo max)</label>
        <input type="file" name="logo" accept="image/png,image/jpeg">
      </div>

      <button type="submit" class="btn-primary">Continuer</button>

      <p class="auth-actions" style="margin-top:12px">
        Déjà inscrit ? <a href="login.php">Se connecter</a>
      </p>
    </form>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
