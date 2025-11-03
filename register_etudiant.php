<?php
// register_etudiant.php
declare(strict_types=1);
require_once __DIR__ . '/includes/init.php';
$page_class = 'register'; 
include __DIR__ . '/includes/header.php';

// CSRF
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];
?>
<div class="page register">
  <div class="register-hero"></div>

  <h1 class="register-title">Inscription Étudiant</h1>
  <p class="register-lead">Créez votre compte et complétez votre profil pour commencer à postuler.</p>

  <div class="register-wrapper">
    <form class="register-card"
          action="<?= BASE ?>/save_register_etudiant.php"
          method="post" enctype="multipart/form-data" novalidate>
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">

      <div class="form-group">
        <label for="nom_complet">Nom complet</label>
        <input id="nom_complet" name="nom_complet" type="text" placeholder="Ex : Lina Tritar" required autocomplete="name">
      </div>

      <div class="form-group">
        <label for="email">Adresse e-mail</label>
        <input id="email" name="email" type="email" placeholder="prenom.nom@example.com" required autocomplete="email">
      </div>

      <div class="form-group">
        <label for="password">Mot de passe</label>
        <input id="password"
               name="password"
               type="password"
               placeholder="••••••••"
               minlength="8"
               required
               autocomplete="new-password">
      </div>

      <!-- 🔹 Confirmation du mot de passe -->
      <div class="form-group">
        <label for="password2">Confirmer le mot de passe</label>
        <input id="password2"
               name="password_confirm"
               type="password"
               placeholder="Retapez le mot de passe"
               minlength="8"
               required
               autocomplete="new-password">
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label for="ville">Ville</label>
          <input id="ville" name="ville" type="text" placeholder="Ex : Calais" autocomplete="address-level2">
        </div>
        <div class="form-group">
          <label for="domaine">Domaine d’étude</label>
          <input id="domaine" name="domaine" type="text" placeholder="Ex : Informatique, Électronique…">
        </div>
      </div>

      <div class="form-group">
        <label for="competances">Compétences</label>
        <input id="competances" name="competances" type="text" placeholder="Ex : PHP, HTML/CSS, SQL…">
      </div>

      <div class="form-group">
  <label>École / Université</label>
  <select name="ecole" id="ecole-select" required>
    <option value="">— Sélectionnez —</option>
    <option>Université du Littoral</option>
    <option>IMT</option>
    <option>Université de Lille</option>
    <option>ULCO</option>
    <option value="_autre">Autre…</option>
  </select>
</div>

<div class="form-group" id="ecole-autre" style="display:none;">
  <label>Préciser l’établissement</label>
  <input type="text" name="ecole_autre" placeholder="Nom de l’établissement">
</div>

<script>
document.getElementById('ecole-select').addEventListener('change', function() {
  const autre = document.getElementById('ecole-autre');
  autre.style.display = (this.value === '_autre') ? 'block' : 'none';
});
</script>


      <div class="grid-2">
        <div class="form-group">
          <label for="photo">Photo de profil (JPG/PNG, 2 Mo max)</label>
          <input id="photo" name="photo" type="file" accept=".jpg,.jpeg,.png">
        </div>
        <div class="form-group">
          <label for="cv">CV (PDF, 4 Mo max)</label>
          <input id="cv" name="cv" type="file" accept="application/pdf,.pdf">
        </div>
      </div>

      <button class="btn-primary" type="submit">Continuer</button>
      <p class="auth-actions">Déjà inscrit ? <a href="<?= BASE ?>/login.php">Se connecter</a></p>
    </form>
  </div>
</div>

<!-- 🔸 Vérification immédiate côté navigateur (confort utilisateur) -->
<script>
  (function () {
    const pwd  = document.getElementById('password');
    const pwd2 = document.getElementById('password2');
    function validateMatch() {
      if (pwd2.value && pwd.value !== pwd2.value) {
        pwd2.setCustomValidity('Les mots de passe ne correspondent pas');
      } else {
        pwd2.setCustomValidity('');
      }
    }
    pwd.addEventListener('input', validateMatch);
    pwd2.addEventListener('input', validateMatch);
  })();
</script>


<?php include __DIR__ . '/includes/footer.php'; ?>
