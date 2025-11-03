<?php
// save_register_etudiant.php
declare(strict_types=1);
require_once __DIR__ . '/includes/init.php';

// 1) N'autoriser que POST (évite l'erreur CSRF en cas d'accès direct)
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  header('Location: register_etudiant.php');
  exit;
}

// 2) CSRF
if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
  http_response_code(400);
  exit('Requête invalide (CSRF).');
}

// 3) Récupération des données
$nom        = trim($_POST['nom_complet'] ?? '');
$email      = mb_strtolower(trim($_POST['email'] ?? ''));
$password   = (string)($_POST['password'] ?? '');
$password2  = (string)($_POST['password_confirm'] ?? ''); // <- confirmation
$ville      = trim($_POST['ville'] ?? '');
$domaine    = trim($_POST['domaine'] ?? '');
$competances     = trim($_POST['competances'] ?? '');
$ecole = trim($_POST['ecole'] ?? '');
if ($ecole === '_autre') {
  $ecole = trim($_POST['ecole_autre'] ?? '');
}
if ($ecole === '') $errors[] = "L’école / université est obligatoire.";
$diplome    = trim($_POST['diplome'] ?? '');

$errors = []; 



// 4) Validations
if ($nom === '')                                $errors[] = "Le nom complet est obligatoire.";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Adresse e-mail invalide.";
if (strlen($password) < 8)                      $errors[] = "Mot de passe trop court (min. 8 caractères).";
if ($password !== $password2)                   $errors[] = "Les mots de passe ne correspondent pas.";

// 5) Uploads (facultatifs)
$photoPath = null; // ex: uploads/photos/xxx.jpg
$cvPath    = null; // ex: uploads/cv/xxx.pdf

$uploadBase = __DIR__ . '/uploads';
$photoDir   = $uploadBase . '/photos';
$cvDir      = $uploadBase . '/cv';
if (!is_dir($photoDir)) @mkdir($photoDir, 0775, true);
if (!is_dir($cvDir))    @mkdir($cvDir, 0775, true);

// Photo (JPG/PNG, max 2 Mo)
if (!empty($_FILES['photo']['name'])) {
  if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
      $errors[] = "La photo dépasse 2 Mo.";
    } else {
      $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
      if (!in_array($ext, ['jpg','jpeg','png'], true)) {
        $errors[] = "Photo : formats autorisés JPG ou PNG.";
      } else {
        $filename = 'photo_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = $photoDir . '/' . $filename;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
          $photoPath = 'uploads/photos/' . $filename; // chemin relatif stocké en BDD
        } else {
          $errors[] = "Échec upload photo.";
        }
      }
    }
  } else {
    $errors[] = "Erreur upload photo (code " . (int)$_FILES['photo']['error'] . ").";
  }
}

// CV (PDF, max 4 Mo)
if (!empty($_FILES['cv']['name'])) {
  if ($_FILES['cv']['error'] === UPLOAD_ERR_OK) {
    if ($_FILES['cv']['size'] > 4 * 1024 * 1024) {
      $errors[] = "Le CV dépasse 4 Mo.";
    } else {
      $ext = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));
      if ($ext !== 'pdf') {
        $errors[] = "CV : PDF uniquement.";
      } else {
        $filename = 'cv_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
        $dest = $cvDir . '/' . $filename;
        if (move_uploaded_file($_FILES['cv']['tmp_name'], $dest)) {
          $cvPath = 'uploads/cv/' . $filename;
        } else {
          $errors[] = "Échec upload CV.";
        }
      }
    }
  } else {
    $errors[] = "Erreur upload CV (code " . (int)$_FILES['cv']['error'] . ").";
  }
}

// 6) Si erreurs, on affiche proprement
if ($errors) {
  include __DIR__ . '/includes/header.php';
  echo '<div class="container page"><h2>Erreurs</h2><ul>';
  foreach ($errors as $e) echo '<li>' . htmlspecialchars($e, ENT_QUOTES) . '</li>';
  echo '</ul><p><a class="btn" href="register_etudiant.php">Retour</a></p></div>';
  include __DIR__ . '/includes/footer.php';
  exit;
}

// 7) Insertion en base (users + etudiants)
$pdo = db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->beginTransaction();

try {
  // Unicité de l'email
  $exists = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
  $exists->execute([':email' => $email]);
  if ($exists->fetch()) {
    throw new RuntimeException("Un compte existe déjà avec cet e-mail.");
  }

  $hash = password_hash($password, PASSWORD_DEFAULT);

  // Table users : (nom, email, mot_de_passe, telephone, role)
  $insUser = $pdo->prepare("
    INSERT INTO users (nom, email, mot_de_passe, telephone, role)
    VALUES (:nom, :email, :hash, :tel, 'etudiant')
  ");
  $insUser->execute([
    ':nom'   => $nom,
    ':email' => $email,
    ':hash'  => $hash,
    ':tel'   => '', // pas demandé dans ce formulaire
  ]);
  $userId = (int)$pdo->lastInsertId();

  // Table etudiants : (user_id, ville, domaine, competances, cv_url, photo_url, ecole, diplome)
  $insEtu = $pdo->prepare("
    INSERT INTO etudiants (user_id, ville, domaine, competances, cv_url, photo_url, ecole, diplome)
    VALUES (:uid, :ville, :domaine, :competances, :cv, :photo, :ecole, :diplome)
  ");
  $insEtu->execute([
    ':uid'     => $userId,
    ':ville'   => $ville ?: null,
    ':domaine' => $domaine ?: null,
    ':competances'  => $competances ?: null,
    ':cv'      => $cvPath,    // peut être null
    ':photo'   => $photoPath, // peut être null
    ':ecole'   => $ecole ?: null,
    ':diplome' => $diplome ?: null,
  ]);

  $pdo->commit(); 
  // Après $pdo->commit();
$_SESSION['flash_success'] = "Votre compte a été créé. Vous pouvez vous connecter.";
unset($_SESSION['csrf']); // token utilisé

header('Location: login.php'); // ou connexion.php selon ton fichier
exit;


  // 8) Auto-login + redirection
  $_SESSION['user_id'] = $userId;
  $_SESSION['role']    = 'etudiant';
  unset($_SESSION['csrf']); // token utilisé

  header('Location: dashboard_etudiant.php'); // chemin relatif OK
  exit;

} catch (Throwable $e) {
  $pdo->rollBack();

  // Nettoyage fichiers en cas d'échec
  if ($photoPath) @unlink(__DIR__ . '/' . $photoPath);
  if ($cvPath)    @unlink(__DIR__ . '/' . $cvPath);

  include __DIR__ . '/includes/header.php';
  echo '<div class="container page"><h2>Erreur d’inscription</h2>';
  echo '<p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES) . '</p>';
  echo '<p><a class="btn" href="register_etudiant.php">Réessayer</a></p></div>';
  include __DIR__ . '/includes/footer.php';
  exit;
}
