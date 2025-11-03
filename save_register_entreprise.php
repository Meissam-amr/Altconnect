<?php
// save_register_entreprise.php
declare(strict_types=1);
require_once __DIR__ . '/includes/init.php';

// 1) Méthode & CSRF
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  header('Location: register_entreprise.php'); exit;
}
if (!isset($_POST['csrf'], $_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], (string)$_POST['csrf'])) {
  http_response_code(400); exit('Requête invalide (CSRF).');
}

// 2) Récupération
$role            = 'entreprise'; // constant pour ce script
$nomEntreprise   = trim((string)($_POST['nom_entreprise'] ?? ''));
$email           = mb_strtolower(trim((string)($_POST['email'] ?? '')));
$siteWeb         = trim((string)($_POST['site_web'] ?? ''));
$password        = (string)($_POST['password'] ?? '');
$password2       = (string)($_POST['password_confirm'] ?? '');

$secteur         = trim((string)($_POST['secteur'] ?? ''));
$localisation    = trim((string)($_POST['localisation'] ?? ''));
$description     = trim((string)($_POST['description'] ?? ''));
$contactEmail    = trim((string)($_POST['contact_email'] ?? ''));
$contactTel      = trim((string)($_POST['contact_tel'] ?? ''));

$errors = [];

// 3) Validations
if ($nomEntreprise === '')                         $errors[] = "La raison sociale est obligatoire.";
if (!filter_var($email, FILTER_VALIDATE_EMAIL))    $errors[] = "Adresse e-mail invalide.";
if ($siteWeb === '')                               $errors[] = "Le site web est obligatoire.";
if (strlen($password) < 8)                         $errors[] = "Mot de passe trop court (min. 8 caractères).";
if ($password !== $password2)                      $errors[] = "Les mots de passe ne correspondent pas.";
if ($contactEmail !== '' && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
  $errors[] = "L’e-mail de contact est invalide.";
}

// 4) Upload logo (facultatif)
$logoPath = null;
if (!empty($_FILES['logo']['name'])) {
  if ($_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    if ($_FILES['logo']['size'] > 2 * 1024 * 1024) {
      $errors[] = "Le logo dépasse 2 Mo.";
    } else {
      $ext = strtolower((string)pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
      if (!in_array($ext, ['jpg','jpeg','png'], true)) {
        $errors[] = "Logo : formats autorisés JPG ou PNG.";
      } else {
        $dir = __DIR__ . '/uploads/logos';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $filename = 'logo_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $dir . '/' . $filename)) {
          $logoPath = 'uploads/logos/' . $filename; // chemin relatif stocké en BDD
        } else {
          $errors[] = "Échec upload logo.";
        }
      }
    }
  } else {
    $errors[] = "Erreur upload logo (code " . (int)$_FILES['logo']['error'] . ").";
  }
}

// 5) Si erreurs → affichage
if ($errors) {
  include __DIR__ . '/includes/header.php';
  echo '<div class="container page"><h2>Erreurs</h2><ul>';
  foreach ($errors as $e) echo '<li>' . htmlspecialchars($e, ENT_QUOTES) . '</li>';
  echo '</ul><p><a class="btn" href="javascript:history.back()">Retour</a></p></div>';
  include __DIR__ . '/includes/footer.php';
  exit;
}

// 6) Insertion BDD (users + entreprises)
$pdo = db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
  $pdo->beginTransaction();

  // Unicité e-mail
  $exists = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
  $exists->execute([':email' => $email]);
  if ($exists->fetch()) {
    throw new RuntimeException("Un compte existe déjà avec cet e-mail.");
  }

  // users
  $hash = password_hash($password, PASSWORD_DEFAULT);
  $insUser = $pdo->prepare("
    INSERT INTO users (nom, email, mot_de_passe, telephone, role)
    VALUES (:nom, :email, :hash, :tel, :role)
  ");
  $insUser->execute([
    ':nom'   => $nomEntreprise,
    ':email' => $email,
    ':hash'  => $hash,
    ':tel'   => '',           // pas demandé ici
    ':role'  => $role,        // 'entreprise'
  ]);
  $userId = (int)$pdo->lastInsertId();

  // entreprises — aligne 1:1 avec ta table
  $insEnt = $pdo->prepare("
    INSERT INTO entreprises
      (user_id, nom_entreprise, secteur, localisation, description, site_web, contact_email, contact_tel, logo_url)
    VALUES
      (:uid, :nom_entreprise, :secteur, :localisation, :description, :site_web, :contact_email, :contact_tel, :logo_url)
  ");
  $insEnt->execute([
    ':uid'            => $userId,
    ':nom_entreprise' => $nomEntreprise,
    ':secteur'        => $secteur ?: null,
    ':localisation'   => $localisation ?: null,
    ':description'    => $description ?: null,
    ':site_web'       => $siteWeb,              // requis -> NOT NULL
    ':contact_email'  => $contactEmail ?: null,
    ':contact_tel'    => $contactTel ?: null,
    ':logo_url'       => $logoPath ?: null,
  ]);

  $pdo->commit();

  // 7) Succès
  $_SESSION['flash_success'] = "Votre compte entreprise a été créé. Vous pouvez vous connecter.";
  unset($_SESSION['csrf']);
  header('Location: login.php'); exit;

} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  if ($logoPath) @unlink(__DIR__ . '/' . $logoPath);

  include __DIR__ . '/includes/header.php';
  echo '<div class="container page"><h2>Erreur d’inscription</h2>';
  echo '<p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES) . '</p>';
  echo '<p><a class="btn" href="register_entreprise.php">Réessayer</a></p></div>';
  include __DIR__ . '/includes/footer.php';
  exit;
}
