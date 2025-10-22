<?php
require_once __DIR__.'/includes/db_connect.php';
require_once __DIR__.'/includes/auth.php';
include __DIR__.'/includes/header.php';

$ok=null; $err=null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $nom   = $_POST['nom'] ?? '';
  $email = $_POST['email'] ?? '';
  $pass  = $_POST['password'] ?? '';
  [$success,$message] = register_user($nom,$email,$pass,'recruteur');
  if ($success) $ok="Compte recruteur créé ✅ Connectez-vous pour créer votre entreprise.";
  else $err=$message;
}
?>
<h2>Inscription Recruteur</h2>
<?php if ($ok): ?><p style="color:green"><?= htmlspecialchars($ok) ?></p><?php endif; ?>
<?php if ($err): ?><p style="color:red"><?= htmlspecialchars($err) ?></p><?php endif; ?>
<form method="post" class="card" style="max-width:460px;display:grid;gap:10px">
  <label>Nom / Responsable* <input name="nom" required></label>
  <label>Email professionnel* <input name="email" type="email" required></label>
  <label>Mot de passe* (min 8) <input name="password" type="password" required></label>
  <button class="btn">Continuer</button>
</form>
<p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
<?php include __DIR__.'/includes/footer.php'; ?>
