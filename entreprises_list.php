<?php
// ALTCONNECT/entreprises_list.php — Liste globale (ADMIN uniquement)
require_once __DIR__ . '/includes/init.php';
require_login();

// Réserver l'accès à l'admin
if (current_user_role() !== 'admin') {
    include __DIR__ . '/includes/header.php';
    echo "<p>Accès réservé à l’administrateur.</p>";
    include __DIR__ . '/includes/footer.php';
    exit;
}

include __DIR__ . '/includes/header.php';

$pdo = db();

$sql = "
SELECT 
  e.id_entreprise,
  e.nom_entreprise,
  e.secteur,
  e.localisation,
  e.description,
  e.site_web,
  e.contact_email,
  e.contact_tel,
  e.logo_url,
  u.nom   AS user_nom,
  u.email AS user_email,
  u.id    AS user_id
FROM entreprises e
JOIN users u ON u.id = e.user_id
ORDER BY e.id_entreprise DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll();
?>
<h2>Entreprises (admin)</h2>

<?php if (!$rows): ?>
  <p>Aucune entreprise trouvée.</p>
<?php else: ?>
  <table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse; width:100%; max-width:1100px">
    <tr>
      <th>ID</th>
      <th>Logo</th>
      <th>Nom</th>
      <th>Secteur</th>
      <th>Localisation</th>
      <th>Contact</th>
      <th>Site</th>
      <th>Propriétaire</th>
    </tr>

    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id_entreprise'] ?></td>

        <td style="text-align:center">
          <?php if (!empty($r['logo_url'])): ?>
            <img src="<?= htmlspecialchars($r['logo_url']) ?>" alt="logo" style="max-width:60px; max-height:60px; object-fit:contain">
          <?php endif; ?>
        </td>

        <td><?= htmlspecialchars($r['nom_entreprise']) ?></td>
        <td><?= htmlspecialchars($r['secteur']) ?></td>
        <td><?= htmlspecialchars($r['localisation']) ?></td>

        <td>
          <?php if (!empty($r['contact_email'])): ?>
            <div><?= htmlspecialchars($r['contact_email']) ?></div>
          <?php endif; ?>
          <?php if (!empty($r['contact_tel'])): ?>
            <div><?= htmlspecialchars($r['contact_tel']) ?></div>
          <?php endif; ?>
        </td>

        <td>
          <?php if (!empty($r['site_web'])): ?>
            <a href="<?= htmlspecialchars($r['site_web']) ?>" target="_blank" rel="noopener">ouvrir</a>
          <?php endif; ?>
        </td>

        <td>
          <?= htmlspecialchars($r['user_nom'] ?? '') ?><br>
          <small><?= htmlspecialchars($r['user_email'] ?? '') ?></small>
        </td>
      </tr>
      <tr>
        <td colspan="8">
          <strong>Description :</strong>
          <div><?= nl2br(htmlspecialchars($r['description'] ?? '')) ?></div>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
