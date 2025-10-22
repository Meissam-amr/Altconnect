<?php
require_once __DIR__.'/includes/db_connect.php';
require_once __DIR__.'/includes/auth.php';
include __DIR__.'/includes/header.php';

$ok=null; $err=null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $nom   = $_POST['nom'] ?? '';
  $email = $_POST['email'] ?? '';
  $pass  = $_POST['password'] ?? '';
  [$success,$message] = register_user($nom,$email,$pass,'etudiant');
  if ($success) $ok="Compte étudiant créé ✅ Vous pouvez vous connecter.";
  else $err=$message;
}
?>
<h2>Inscription Étudiant</h2>
<?php if ($ok): ?><p style="color:green"><?= htmlspecialchars($ok) ?></p><?php endif; ?>
<?php if ($err): ?><p style="color:red"><?= htmlspecialchars($err) ?></p><?php endif; ?>
<form method="post" class="card" style="max-width:460px;display:grid;gap:10px">
  <label>Nom complet* <input name="nom" required></label>
  <label>Adresse e-mail* <input name="email" type="email" required></label>
  <label>Mot de passe* (min 8) <input name="password" type="password" required></label>
  <button class="btn">Continuer</button>
</form>
<p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
<?php include __DIR__.'/includes/footer.php'; ?>
