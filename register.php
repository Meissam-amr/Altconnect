<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/auth.php';
include __DIR__ . '/includes/header.php';

$ok = null; $err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = $_POST['nom']     ?? '';
    $email   = $_POST['email']   ?? '';
    $pass    = $_POST['password']?? '';
    $role    = $_POST['role']    ?? 'etudiant'; // optionnel

    [$success, $message] = register_user($nom, $email, $pass, $role);
    if ($success) {
        $ok = "Compte créé ✅ Vous pouvez vous connecter.";
    } else {
        $err = $message;
    }
}
?>
<h2>Inscription</h2>

<?php if ($ok): ?><p style="color:green"><?= htmlspecialchars($ok) ?></p><?php endif; ?>
<?php if ($err): ?><p style="color:red"><?= htmlspecialchars($err) ?></p><?php endif; ?>

<form method="post" style="max-width:420px;display:grid;gap:8px">
  <label>Nom*
    <input name="nom" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
  </label>
  <label>Email*
    <input name="email" type="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
  </label>
  <label>Mot de passe* (min 8)
    <input name="password" type="password" required>
  </label>
  <label>Rôle (optionnel)
    <select name="role">
      <option value="etudiant">Étudiant</option>
      <option value="recruteur">Recruteur</option>
      <option value="admin">Admin</option>
    </select>
  </label>
  <button>S’inscrire</button>
</form>

<p><a href="login.php">Déjà un compte ? Se connecter</a></p>

<?php include __DIR__ . '/includes/footer.php'; ?>
