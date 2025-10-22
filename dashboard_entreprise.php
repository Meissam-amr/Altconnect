<?php
require_once __DIR__ . '/includes/init.php';
require_login();

// réservé aux recruteurs
if (current_user_role() !== 'recruteur') {
    include __DIR__ . '/includes/header.php';
    echo "<p>Accès réservé aux recruteurs.</p>";
    include __DIR__ . '/includes/footer.php';
    exit;
}

$uid = current_user_id();
$stmt = db()->prepare("SELECT id_entreprise FROM entreprises WHERE user_id = :u");
$stmt->execute([':u' => $uid]);
$entrepriseId = $stmt->fetchColumn();

include __DIR__ . '/includes/header.php';
?>
<h2>Mon espace Recruteur</h2>

<?php if ($entrepriseId): ?>
  <p><a class="btn" href="entreprises_edit.php?id=<?= (int)$entrepriseId ?>">Voir / Modifier mon entreprise</a></p>
<?php else: ?>
  <p><a class="btn" href="entreprises_new.php">Créer mon entreprise</a></p>
<?php endif; ?>

<p><a href="logout.php">Déconnexion</a></p>

<?php include __DIR__ . '/includes/footer.php'; ?>
