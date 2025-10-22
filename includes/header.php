<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>AltConnect</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    body{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;margin:0}
    header{background:#164BBA;color:#fff}
    nav{max-width:1100px;margin:0 auto;display:flex;gap:16px;align-items:center;padding:14px}
    nav a{color:#fff;text-decoration:none;opacity:.95}
    nav a:hover{text-decoration:underline}
    .grow{flex:1}
    main{max-width:1100px;margin:18px auto;padding:0 12px}
    .btn{display:inline-block;padding:10px 14px;border-radius:8px;border:1px solid #d0d7ff;background:#e8eeff}
    .card{border:1px solid #e5e7eb;border-radius:12px;padding:16px}
  </style>
</head>
<body>
<header>
  <nav>
    <strong style="font-size:18px">AltConnect</strong>
    <a href="index.php">Accueil</a>
    <a href="offres.php">Offres</a>
    <a href="blog.php">Blog</a>
    <a href="stats.php">Statistiques</a>

    <div class="grow"></div>

    <?php if (!empty($_SESSION['user_id'])): ?>
      <?php $role = current_user_role(); ?>

      <?php if ($role === 'admin'): ?>
        <a href="entreprises_list.php">Entreprises</a> |
        <a href="dashboard_admin.php">Tableau de bord</a> |
      <?php elseif ($role === 'recruteur'): ?>
        <a href="dashboard_entreprise.php">Mon espace</a> |
      <?php elseif ($role === 'etudiant'): ?>
        <a href="dashboard_etudiant.php">Mon espace</a> |
      <?php endif; ?>

      <a href="logout.php">Déconnexion</a>

    <?php else: ?>
      <a href="login.php">Connexion</a> /
      <a href="choose_role.php">Inscription</a>
    <?php endif; ?>
  </nav>
</header>
<main>
