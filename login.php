<?php
require_once __DIR__ . '/includes/init.php';

// Si déjà connecté, on redirige vers le bon espace
if (current_user_id()) {
  $role = current_user_role();
  if     ($role === 'admin')      redirect(BASE.'/dashboard_admin.php');
  elseif ($role === 'entreprise') redirect(BASE.'/dashboard_entreprise.php');
  else                            redirect(BASE.'/dashboard_etudiant.php');
}

$error = null;

// next = destination après connexion (optionnel)
$next = $_GET['next'] ?? BASE.'/index.php';
// On n’autorise que des chemins internes (évite open redirect)
if (!is_string($next) || !str_starts_with($next, '/')) {
  $next = BASE.'/index.php';
}

// CSRF token (simple)
if (empty($_SESSION['csrf_login'])) {
  $_SESSION['csrf_login'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Vérif CSRF
  $token = $_POST['csrf_token'] ?? '';
  if (!hash_equals($_SESSION['csrf_login'] ?? '', $token)) {
    $error = "Session expirée. Veuillez réessayer.";
  } else {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($email === '' || $pass === '') {
      $error = "Veuillez renseigner l’e-mail et le mot de passe.";
    } else {
      if (login_user($email, $pass)) {
        // Succès → redirection prioritaire vers ?next= si interne
        $dest = $next ?: BASE.'/index.php';
        // Pour éviter de sortir du site, on force BASE si besoin
        if (!str_starts_with($dest, BASE.'/') && $dest !== BASE.'/index.php') {
          $dest = BASE.'/index.php';
        }
        header('Location: '.$dest);
        exit;
      } else {
        $error = "Identifiants incorrects.";
      }
    }
  }
}

include __DIR__ . '/includes/header.php';
?>


<div class="auth-wrapper">
  <h1 class="auth-title">Connexion</h1>
  <div class="auth-card">

    <?php if (!empty($error)): ?>
      <p style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;padding:10px;border-radius:8px;">
        <?= e($error) ?>
      </p>
    <?php endif; ?>

    <form method="post" action="<?= e($_SERVER['PHP_SELF']).'?next='.urlencode($next) ?>">
      <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_login']) ?>">

      <label for="email">E-mail</label>
      <input type="email" id="email" name="email" placeholder="ex: nom@exemple.com" required>

      <label for="password">Mot de passe</label>
      <input type="password" id="password" name="password" placeholder="••••••••" required>

      <button type="submit">Continuer</button>

      <div class="auth-actions">
        <a href="#">Mot de passe oublié ?</a><br>
        Première visite sur le site ? <a href="<?= BASE ?>/choose_role.php">Inscrivez-vous</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
