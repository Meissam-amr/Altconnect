<?php
// ALTCONNECT/entreprises_new.php
require_once __DIR__ . '/includes/init.php'; // session + DB + auth
require_login();

// 1) Accès réservé aux recruteurs
if (current_user_role() !== 'recruteur') {
    include __DIR__ . '/includes/header.php';
    echo "<p>Accès réservé aux recruteurs.</p>";
    include __DIR__ . '/includes/footer.php';
    exit;
}

// 2) Empêcher la 2ᵉ création : si l'entreprise existe déjà, on redirige vers l'édition
$uid = current_user_id();
$st  = db()->prepare("SELECT id_entreprise FROM entreprises WHERE user_id = :u");
$st->execute([':u' => $uid]);
$existingId = $st->fetchColumn();
if ($existingId) {
    header('Location: entreprises_edit.php?id=' . (int)$existingId);
    exit;
}

include __DIR__ . '/includes/header.php';

$errors  = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 3) Récupération + nettoyage
    $nom_entreprise = trim($_POST['nom_entreprise'] ?? '');
    $secteur        = trim($_POST['secteur'] ?? '');
    $localisation   = trim($_POST['localisation'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $site_web       = trim($_POST['site_web'] ?? '');
    $contact_email  = trim($_POST['contact_email'] ?? '');
    $contact_tel    = trim($_POST['contact_tel'] ?? '');
    $logo_url       = trim($_POST['logo_url'] ?? '');

    // 4) Validation simple
    if ($nom_entreprise === '') $errors[] = "Le nom de l'entreprise est obligatoire.";
    if ($secteur === '')        $errors[] = "Le secteur est obligatoire.";
    if ($localisation === '')   $errors[] = "La localisation est obligatoire.";
    if ($description === '')    $errors[] = "La description est obligatoire.";
    if ($site_web !== '' && !filter_var($site_web, FILTER_VALIDATE_URL)) {
        $errors[] = "Le site web n'est pas une URL valide.";
    }
    if ($contact_email !== '' && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email de contact n'est pas valide.";
    }

    // 5) Insertion si pas d'erreurs
    if (!$errors) {
        try {
            $pdo = db();
            $sql = "INSERT INTO entreprises
                      (user_id, nom_entreprise, secteur, localisation, description, site_web, contact_email, contact_tel, logo_url)
                    VALUES
                      (:user_id, :nom_entreprise, :secteur, :localisation, :description, :site_web, :contact_email, :contact_tel, :logo_url)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user_id'        => $uid, // vient de la session
                ':nom_entreprise' => $nom_entreprise,
                ':secteur'        => $secteur,
                ':localisation'   => $localisation,
                ':description'    => $description,
                ':site_web'       => $site_web !== '' ? $site_web : null,
                ':contact_email'  => $contact_email !== '' ? $contact_email : null,
                ':contact_tel'    => $contact_tel !== '' ? $contact_tel : null,
                ':logo_url'       => $logo_url !== '' ? $logo_url : null,
            ]);

            // Récupérer l'id créé pour rediriger vers l'édition
            $newId  = (int)$pdo->lastInsertId();
            $success = "Entreprise créée avec succès ✅";
            // Redirection immédiate vers l'édition pour continuer la mise à jour
            header('Location: entreprises_edit.php?id=' . $newId);
            exit;

        } catch (PDOException $e) {
            // 23000 = contrainte (UNIQUE user_id ou FK)
            if ($e->getCode() === '23000') {
                // Cas typique : l'unicité user_id a bloqué (déjà une entreprise)
                // On essaie de récupérer l'id existant pour aider l'utilisateur
                $q = db()->prepare("SELECT id_entreprise FROM entreprises WHERE user_id = :u");
                $q->execute([':u' => $uid]);
                $idExist = $q->fetchColumn();
                if ($idExist) {
                    $errors[] = "Vous avez déjà créé votre entreprise. Vous pouvez la modifier ici : ".
                                "<a href=\"entreprises_edit.php?id=".(int)$idExist."\">ouvrir ma fiche</a>.";
                } else {
                    $errors[] = "Impossible d'enregistrer (contrainte d'intégrité). Réessayez.";
                }
            } else {
                $errors[] = "Erreur d'insertion : " . htmlspecialchars($e->getMessage());
            }
        }
    }
}
?>
<h2>Nouvelle entreprise</h2>

<?php if ($success): ?>
  <p style="color:green"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<?php if ($errors): ?>
  <ul style="color:red">
    <?php foreach ($errors as $e): ?>
      <li><?= is_string($e) ? $e : htmlspecialchars((string)$e) ?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<form method="post" style="max-width:560px;display:grid;gap:10px">
  <label>Nom de l'entreprise* <input name="nom_entreprise" required value="<?= htmlspecialchars($_POST['nom_entreprise'] ?? '') ?>"></label>
  <label>Secteur* <input name="secteur" required value="<?= htmlspecialchars($_POST['secteur'] ?? '') ?>"></label>
  <label>Localisation* <input name="localisation" required value="<?= htmlspecialchars($_POST['localisation'] ?? '') ?>"></label>
  <label>Description* <textarea name="description" rows="5" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea></label>
  <label>Site web <input name="site_web" placeholder="https://..." value="<?= htmlspecialchars($_POST['site_web'] ?? '') ?>"></label>
  <label>Email de contact <input name="contact_email" type="email" value="<?= htmlspecialchars($_POST['contact_email'] ?? '') ?>"></label>
  <label>Téléphone <input name="contact_tel" value="<?= htmlspecialchars($_POST['contact_tel'] ?? '') ?>"></label>
  <label>Logo (URL) <input name="logo_url" placeholder="https://..." value="<?= htmlspecialchars($_POST['logo_url'] ?? '') ?>"></label>
  <button>Enregistrer</button>
</form>

<p style="margin-top:10px">
  <a href="dashboard_entreprise.php">← Retour à mon espace</a>
</p>

<?php include __DIR__ . '/includes/footer.php'; ?>
