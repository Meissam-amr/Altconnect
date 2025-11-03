<?php
// includes/header.php
// ATTENTION : init.php doit déjà être inclus par la page appelante
$role = current_user_role();      // etudiant | entreprise | admin | null
$user = current_user();

// helper active
function active_link(string $path): string {
  $uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?'); // /altconnect/...
  return ($uri === $path) ? ' class="active"' : '';
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>AltConnect</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
 <link rel="stylesheet"
      href="<?= BASE ?>/assets/css/style.css?v=<?= @filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/css/style.css') ?>">
</head>
<body>

<header class="topbar">
  <div class="container topbar-inner">

    <!-- Logo à gauche -->
    <a href="<?= BASE ?>/index.php" class="logo-left">
      <img src="<?= BASE ?>/assets/images/logo-altconnect.png" alt="AltConnect" class="logo-img">
    </a>

    <!-- Menu centre -->
    <nav class="mainnav" role="navigation">
      <a<?= active_link(BASE.'/index.php') ?> href="<?= BASE ?>/index.php">Accueil</a>
      <a<?= active_link(BASE.'/offres/offres_list.php') ?> href="<?= BASE ?>/offres/offres_list.php">Offres</a>
      <a<?= active_link(BASE.'/blog.php') ?> href="<?= BASE ?>/blog.php">Blog</a>
      <a<?= active_link(BASE.'/stats.php') ?> href="<?= BASE ?>/stats.php">Statistiques</a>

      <?php if ($role === 'entreprise'): ?>
        <a<?= active_link(BASE.'/offres/offres_new.php') ?> href="<?= BASE ?>/offres/offres_new.php">Publier</a>
        <a href="<?= BASE ?>/offres/offres_list.php">Mes offres</a>
      <?php elseif ($role === 'etudiant'): ?>
        <a href="<?= BASE ?>/mes_candidatures.php">Mes candidatures</a>
      <?php elseif ($role === 'admin'): ?>
        <a href="<?= BASE ?>/admin_users.php">Utilisateurs</a>
      <?php endif; ?>
    </nav>

    <!-- Droite -->
    <div class="auth">
      <?php if (!$user): ?>
        <a class="btn-outline" href="<?= BASE ?>/login.php">Connexion</a>
        <a class="btn" href="<?= BASE ?>/choose_role.php">Inscription</a>
      <?php else: ?>
        <?php
          $dest = ($role==='admin')
                  ? BASE.'/dashboard_admin.php'
                  : (($role==='entreprise') ? BASE.'/dashboard_entreprise.php' : BASE.'/dashboard_etudiant.php');
        ?>
        <a class="btn-outline" href="<?= $dest ?>">Mon espace</a>
        <a class="btn" href="<?= BASE ?>/logout.php">Déconnexion</a>
      <?php endif; ?>
    </div>

  </div>
</header>

<main class="container page <?= isset($page_class) ? htmlspecialchars($page_class) : '' ?>">
  <?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success">
    <?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES) ?>
  </div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-error">
    <?= htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES) ?>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>


