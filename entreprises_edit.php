<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$role = current_user_role();

$stmt = db()->prepare("SELECT * FROM entreprises WHERE id_entreprise = :id");
$stmt->execute([':id' => $id]);
$entreprise = $stmt->fetch();

if (!$entreprise) {
    include __DIR__ . '/includes/header.php';
    echo "<p>Entreprise introuvable.</p>";
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Vérifie que l'entreprise appartient au recruteur connecté (sauf admin)
if ($role !== 'admin' && $entreprise['user_id'] !== current_user_id()) {
    include __DIR__ . '/includes/header.php';
    echo "<p>Accès refusé.</p>";
    include __DIR__ . '/includes/footer.php';
    exit;
}

$success = null;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_entreprise = trim($_POST['nom_entreprise'] ?? '');
    $secteur        = trim($_POST['secteur'] ?? '');
    $localisation   = trim($_POST['localisation'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $site_web       = trim($_POST['site_web'] ?? '');
    $contact_email  = trim($_POST['contact_email'] ?? '');
    $contact_tel    = trim($_POST['contact_tel'] ?? '');
    $logo_url       = trim($_POST['logo_url'] ?? '');

    if ($nom_entreprise === '') $errors[] = "Le nom de l'entreprise est obligatoire.";
    if ($secteur === '')        $errors[] = "Le secteur est obligatoire.";
    if ($localisation === '')   $errors[] = "La localisation est obligatoire.";

    if (!$errors) {
        $sql = "UPDATE entreprises 
                SET nom_entreprise=:n, secteur=:s, localisation=:l, description=:d,
                    site_web=:w, contact_email=:ce, contact_tel=:ct, logo_url=:lu
                WHERE id_entreprise=:id";
        $upd = db()->prepare($sql);
        $upd->execute([
            ':n' => $nom_entreprise,
            ':s' => $secteur,
            ':l' => $localisation,
            ':d' => $description,
            ':w' => $site_web,
            ':ce' => $contact_email,
            ':ct' => $contact_tel,
            ':lu' => $logo_url,
            ':id' => $id,
        ]);
        $success = "Modifications enregistrées ✅";
    }
}

include __DIR__ . '/includes/header.php';
?>
<h2>Modifier mon entreprise</h2>

<?php if ($success): ?><p style="color:green"><?= $success ?></p><?php endif; ?>
<?php if ($errors): ?><ul style="color:red"><?php foreach($errors as $e) echo "<li>$e</li>"; ?></ul><?php endif; ?>

<form method="post" style="max-width:560px;display:grid;gap:10px">
  <label>Nom de l'entreprise* 
    <input name="nom_entreprise" required value="<?= htmlspecialchars($entreprise['nom_entreprise']) ?>">
  </label>
  <label>Secteur* 
    <input name="secteur" required value="<?= htmlspecialchars($entreprise['secteur']) ?>">
  </label>
  <label>Localisation* 
    <input name="localisation" required value="<?= htmlspecialchars($entreprise['localisation']) ?>">
  </label>
  <label>Description* 
    <textarea name="description" rows="5"><?= htmlspecialchars($entreprise['description']) ?></textarea>
  </label>
  <label>Site web 
    <input name="site_web" value="<?= htmlspecialchars($entreprise['site_web'] ?? '') ?>">
  </label>
  <label>Email de contact 
    <input name="contact_email" value="<?= htmlspecialchars($entreprise['contact_email'] ?? '') ?>">
  </label>
  <label>Téléphone 
    <input name="contact_tel" value="<?= htmlspecialchars($entreprise['contact_tel'] ?? '') ?>">
  </label>
  <label>Logo (URL) 
    <input name="logo_url" value="<?= htmlspecialchars($entreprise['logo_url'] ?? '') ?>">
  </label>
  <button>Enregistrer</button>
</form>

<p><a href="dashboard_entreprise.php">← Retour à mon espace</a></p>

<?php include __DIR__ . '/includes/footer.php'; ?>
