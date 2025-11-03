<?php
require_once __DIR__ . '/../includes/init.php';

$q = trim($_GET['q'] ?? '');
$ville = trim($_GET['ville'] ?? '');
$domaine = trim($_GET['domaine'] ?? '');

$conds=[]; $params=[];
if ($q!==''){ $conds[]="(o.titre LIKE :q OR o.description LIKE :q)"; $params[':q']="%$q%"; }
if ($ville!==''){ $conds[]="o.ville = :v"; $params[':v']=$ville; }
if ($domaine!==''){ $conds[]="o.domaine = :dom"; $params[':dom']=$domaine; }

$sql="SELECT o.id, o.entreprise_id, o.titre, o.description, o.ville, o.domaine, o.type_contrat, o.created_at, e.nom_entreprise
      FROM offres o
      JOIN entreprises e ON e.id_entreprise=o.entreprise_id";
if ($conds) $sql.=" WHERE ".implode(' AND ',$conds);
$sql.=" ORDER BY o.created_at DESC";

$st = db()->prepare($sql); $st->execute($params); $rows=$st->fetchAll();

include __DIR__.'/../includes/header.php';
?>
<h2>Offres</h2>
<form method="get" class="card" style="display:grid;gap:10px;max-width:760px;margin-bottom:16px">
  <label>Mot-clé <input name="q" value="<?= htmlspecialchars($q) ?>"></label>
  <label>Ville <input name="ville" value="<?= htmlspecialchars($ville) ?>"></label>
  <label>Domaine <input name="domaine" value="<?= htmlspecialchars($domaine) ?>"></label>
  <button class="btn">Filtrer</button>
</form>

<?php if (!$rows): ?>
  <p>Aucune offre trouvée.</p>
<?php else: ?>
  <?php foreach($rows as $o): ?>
    <div class="card">
      <h3 style="margin:0 0 6px 0">
        <a href="/ALTCONNECT/offres/offre.php?id=<?= (int)$o['id'] ?>"><?= htmlspecialchars($o['titre']) ?></a>
      </h3>
      <div><strong><?= htmlspecialchars($o['nom_entreprise']) ?></strong> — <?= htmlspecialchars($o['ville']) ?> — <?= htmlspecialchars($o['domaine']) ?> — <?= htmlspecialchars($o['type_contrat']) ?></div>
      <p><?= nl2br(htmlspecialchars(mb_strimwidth($o['description'],0,200,'…'))) ?></p>
      <?php
      $ownerStmt = db()->prepare("SELECT user_id FROM entreprises WHERE id_entreprise=:id");
      $ownerStmt->execute([':id'=>$o['entreprise_id']]);
      $isOwner = (int)$ownerStmt->fetchColumn() === current_user_id();
      if (current_user_role()==='admin' || $isOwner): ?>
        <a class="btn" href="/ALTCONNECT/offres/offres_edit.php?id=<?= (int)$o['id'] ?>">Éditer</a>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
<?php include __DIR__.'/../includes/footer.php'; ?>
