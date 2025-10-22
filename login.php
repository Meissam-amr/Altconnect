<?php
require_once __DIR__.'/includes/db_connect.php';
require_once __DIR__.'/includes/auth.php';
include __DIR__.'/includes/header.php';

$msg = null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $email = $_POST['email'] ?? '';
  $pass  = $_POST['password'] ?? '';
  $next  = $_POST['next'] ?? '';

  if (login_user($email,$pass)) {
    // Redirection par rôle
    $role = current_user_role();
    if ($next) { header('Location: '.$next); exit; }
    switch ($role) {
      case 'admin':      header('Location: dashboard_admin.php'); break;
      case 'recruteur':  header('Location: dashboard_entreprise.php'); break;
      default:           header('Location: dashboard_etudiant.php'); break;
    }
    exit;
  } else {
    $msg = "Identifiants incorrects.";
  }
}
$next = $_GET['next'] ?? '';
?>
<h2>Connexion</h2>
<?php if ($msg): ?><p style="color:red"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
<form method="post" class="card" style="max-width:420px;display:grid;gap:10px">
  <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">
  <label>E-mail <input type="email" name="email" required placeholder="vous@exemple.com"></label>
  <label>Mot de passe <input type="password" name="password" required></label>
  <button class="btn">Continuer</button>
</form>
<p>Première visite ? <a href="choose_role.php">Inscrivez-vous</a></p>
<?php include __DIR__.'/includes/footer.php'; ?>
