<?php
require_once __DIR__ . '/../includes/init.php';
require_login();
if (current_user_role() !== 'recruteur') { include __DIR__.'/../includes/header.php'; echo "<p>Accès réservé aux recruteurs.</p>"; include __DIR__.'/../includes/footer.php'; exit; }

// id de l'entreprise du recruteur
$uid = current_user_id();
$st = db()->prepare("SELECT id_entreprise FROM entreprises WHERE user_id = :u");
$st->execute([':u'=>$uid]);
$entreprise_id = (int)$st->fetchColumn();
if (!$entreprise_id) { header('Location: /ALTCONNECT/entreprises_new.php'); exit; }

$errors=[];

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $titre = trim($_POST['titre'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $ville = trim($_POST['ville'] ?? '');
  $domaine = trim($_POST['domaine'] ?? '');
  $type_contrat = $_POST['type_contrat'] ?? 'alternance';
  $date_debut = $_POST['date_debut'] ?? null;
  $remuneration = trim($_POST['remuneration'] ?? '');

  if ($titre==='') $errors[]="Le titre est obligatoire.";
  if ($description==='') $errors[]="La description est obligatoire.";
  if ($ville==='') $errors[]="La ville est obligatoire.";
  if ($domaine==='') $errors[]="Le domaine est obligatoire.";

  if (!$errors) {
    $sql="INSERT INTO offres (entreprise_id,titre,description,ville,domaine,type_contrat,date_debut,remuneration)
          VALUES (:eid,:t,:d,:v,:dom,:tc,:dd,:rem)";
    db()->prepare($sql)->execute([
      ':eid'=>$entreprise_id, ':t'=>$titre, ':d'=>$description, ':v'=>$ville,
      ':dom'=>$domaine, ':tc'=>$type_contrat, ':dd'=>$date_debut ?: null, ':rem'=>$remuneration ?: null
    ]);
    $newId = (int)db()->lastInsertId();
    header('Location: /ALTCONNECT/offres/offre.php?id='.$newId); exit;
  }
}
include __DIR__.'/../includes/header.php';
?>
<h2>Publier une offre</h2>
<?php if ($errors): ?><ul style="color:red"><?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul><?php endif; ?>
<form method="post" class="card" style="max-width:640px;display:grid;gap:10px">
  <label>Titre* <input name="titre" required></label>
  <label>Ville* <input name="ville" required></label>
  <label>Domaine* <input name="domaine" required></label>
  <label>Type de contrat
    <select name="type_contrat">
      <option value="alternance">Alternance</option>
      <option value="stage">Stage</option>
      <option value="cdi">CDI</option>
      <option value="cdd">CDD</option>
    </select>
  </label>
  <label>Date de début <input type="date" name="date_debut"></label>
  <label>Rémunération (texte) <input name="remuneration"></label>
  <label>Description* <textarea name="description" rows="6" required></textarea></label>
  <button class="btn">Publier</button>
</form>
<p><a href="/ALTCONNECT/offres/offres_list.php">← Voir les offres</a></p>
<?php include __DIR__.'/../includes/footer.php'; ?>
