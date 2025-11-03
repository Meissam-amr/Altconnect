<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare("SELECT o.*, e.user_id FROM offres o JOIN entreprises e ON e.id_entreprise=o.entreprise_id WHERE o.id=:id");
$st->execute([':id'=>$id]);
$o = $st->fetch();
if (!$o) { http_response_code(404); echo "Offre introuvable."; exit; }

$role = current_user_role();
$owner = ((int)$o['user_id'] === current_user_id());
if (!($role==='admin' || $owner)) { echo "Accès refusé."; exit; }

$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $titre = trim($_POST['titre'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $ville = trim($_POST['ville'] ?? '');
  $domaine = trim($_POST['domaine'] ?? '');
  $type_contrat = $_POST['type_contrat'] ?? 'alternance';
  $date_debut = $_POST['date_debut'] ?? null;
  $remuneration = trim($_POST['remuneration'] ?? '');

  if ($titre==='' || $description==='' || $ville==='' || $domaine==='') $errors[]="Champs obligatoires manquants.";

  if (!$errors) {
    $sql="UPDATE offres SET titre=:t, description=:d, ville=:v, domaine=:dom, type_contrat=:tc, date_debut=:dd, remuneration=:rem WHERE id=:id";
    db()->prepare($sql)->execute([
      ':t'=>$titre, ':d'=>$description, ':v'=>$ville, ':dom'=>$domaine,
      ':tc'=>$type_contrat, ':dd'=>$date_debut ?: null, ':rem'=>$remuneration ?: null, ':id'=>$id
    ]);
    header('Location: /ALTCONNECT/offres/offre.php?id='.$id); exit;
  } else {
    $o = array_merge($o, compact('titre','description','ville','domaine','type_contrat','date_debut','remuneration'));
  }
}
include __DIR__.'/../includes/header.php';
?>
<h2>Modifier l’offre</h2>
<?php if ($errors): ?><ul style="color:red"><?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul><?php endif; ?>
<form method="post" class="card" style="max-width:640px;display:grid;gap:10px">
  <label>Titre* <input name="titre" required value="<?= htmlspecialchars($o['titre']) ?>"></label>
  <label>Ville* <input name="ville" required value="<?= htmlspecialchars($o['ville']) ?>"></label>
  <label>Domaine* <input name="domaine" required value="<?= htmlspecialchars($o['domaine']) ?>"></label>
  <label>Type de contrat
    <select name="type_contrat">
      <?php $opts=['alternance'=>'Alternance','stage'=>'Stage','cdi'=>'CDI','cdd'=>'CDD'];
      foreach($opts as $k=>$v){ $sel=$k===$o['type_contrat']?'selected':''; echo "<option value='$k' $sel>$v</option>"; } ?>
    </select>
  </label>
  <label>Date de début <input type="date" name="date_debut" value="<?= htmlspecialchars($o['date_debut'] ?? '') ?>"></label>
  <label>Rémunération <input name="remuneration" value="<?= htmlspecialchars($o['remuneration'] ?? '') ?>"></label>
  <label>Description* <textarea name="description" rows="6" required><?= htmlspecialchars($o['description']) ?></textarea></label>
  <button class="btn">Enregistrer</button>
</form>
<p><a href="/ALTCONNECT/offres/offre.php?id=<?= (int)$id ?>">← Retour à l’offre</a></p>
<?php include __DIR__.'/../includes/footer.php'; ?>
