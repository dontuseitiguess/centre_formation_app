<?php
require __DIR__.'/db.php';
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$action = $_GET['action'] ?? 'list';
$msg = $err = null;

/* data pour selects */
$formations = $pdo->query("SELECT id, titre FROM formation ORDER BY titre")->fetchAll();
$formateurs = $pdo->query("SELECT id, CONCAT(prenom,' ',nom) AS nom FROM formateur ORDER BY nom")->fetchAll();

/* helpers */
function firstAffectationFormateur($pdo,$session_id){
  $q = $pdo->prepare("SELECT fm.id, CONCAT(fm.prenom,' ',fm.nom) AS nom
                      FROM affectation a JOIN formateur fm ON fm.id=a.formateur_id
                      WHERE a.session_id=? LIMIT 1");
  $q->execute([$session_id]); return $q->fetch();
}

/* CREATE */
if ($action==='create' && $_SERVER['REQUEST_METHOD']==='POST') {
  try {
    $formation_id = (int)$_POST['formation_id'];
    $date_debut = $_POST['date_debut'];
    $date_fin   = $_POST['date_fin'];
    $salle      = trim($_POST['salle'] ?? '');
    $capacite   = (int)$_POST['capacite'];
    $formateur_id = (int)$_POST['formateur_id'];

    if (strtotime($date_fin) < strtotime($date_debut)) {
      throw new Exception("La date de fin ne peut pas être avant la date de début.");
    }
    $pdo->beginTransaction();
    $pdo->prepare("INSERT INTO session(formation_id,date_debut,date_fin,salle,capacite,statut)
                   VALUES(?,?,?,?,?, 'PLANIFIEE')")
        ->execute([$formation_id,$date_debut,$date_fin,$salle,$capacite]);
    $sid = (int)$pdo->lastInsertId();
    $pdo->prepare("INSERT INTO affectation(session_id, formateur_id, role) VALUES(?, ?, 'FORMATEUR_PRINCIPAL')")
        ->execute([$sid,$formateur_id]);
    $pdo->commit();
    header('Location: sessions.php?msg=ajoute'); exit;
  } catch(Exception $ex){ $pdo->rollBack(); $err = $ex->getMessage(); }
}

/* UPDATE */
if ($action==='update' && $_SERVER['REQUEST_METHOD']==='POST') {
  try {
    $id = (int)$_POST['id'];
    $date_debut = $_POST['date_debut'];
    $date_fin   = $_POST['date_fin'];
    if (strtotime($date_fin) < strtotime($date_debut)) {
      throw new Exception("La date de fin ne peut pas être avant la date de début.");
    }
    $pdo->prepare("UPDATE session SET formation_id=?, date_debut=?, date_fin=?, salle=?, capacite=?, statut=? WHERE id=?")
        ->execute([
          (int)$_POST['formation_id'], $date_debut, $date_fin,
          trim($_POST['salle'] ?? ''), (int)$_POST['capacite'], $_POST['statut'], $id
        ]);
    // maj affectation principale (simple : on remplace)
    $pdo->prepare("DELETE FROM affectation WHERE session_id=?")->execute([$id]);
    $pdo->prepare("INSERT INTO affectation(session_id, formateur_id, role) VALUES(?, ?, 'FORMATEUR_PRINCIPAL')")
        ->execute([$id, (int)$_POST['formateur_id']]);
    header('Location: sessions.php?msg=modifie'); exit;
  } catch(Exception $ex){ $err = $ex->getMessage(); }
}

/* DELETE */
if ($action==='delete') {
  try {
    $pdo->prepare("DELETE FROM session WHERE id=?")->execute([(int)$_GET['id']]);
    header('Location: sessions.php?msg=supprime'); exit;
  } catch(PDOException $ex){ $err = $ex->getMessage(); }
}

/* EDIT data */
if ($action==='edit') {
  $st = $pdo->prepare("SELECT * FROM session WHERE id=?"); $st->execute([(int)$_GET['id']]); $s = $st->fetch();
  if(!$s){ die("Session introuvable."); }
  $aff = firstAffectationFormateur($pdo,$s['id']);
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Sessions</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{font-family:system-ui;margin:32px}
.btn{display:inline-block;padding:8px 12px;border:1px solid #ccc;border-radius:8px;text-decoration:none}
table{border-collapse:collapse;width:100%;max-width:1100px}
th,td{border:1px solid #ddd;padding:8px}
.notice{padding:10px;border-radius:8px;margin:10px 0}
.ok{background:#e7f7ee;border:1px solid #bfe8cf}
.err{background:#fde2e2;border:1px solid #f5b6b6}
input,select{padding:8px;width:100%;max-width:360px}
.row{margin-bottom:10px}
</style>
</head>
<body>
  <h1>📅 Sessions</h1>
  <p><a class="btn" href="formations.php">← Formations</a>
     <a class="btn" href="sessions.php?action=new">➕ Créer une session</a></p>

  <?php if(isset($_GET['msg']) && $_GET['msg']==='ajoute'): ?><div class="notice ok">Session ajoutée.</div><?php endif; ?>
  <?php if(isset($_GET['msg']) && $_GET['msg']==='modifie'): ?><div class="notice ok">Session modifiée.</div><?php endif; ?>
  <?php if(isset($_GET['msg']) && $_GET['msg']==='supprime'): ?><div class="notice ok">Session supprimée.</div><?php endif; ?>
  <?php if($err): ?><div class="notice err"><?= e($err) ?></div><?php endif; ?>

  <?php if($action==='new'): ?>
    <h2>Créer une session</h2>
    <form method="post" action="sessions.php?action=create">
      <div class="row">
        <label>Formation *</label>
        <select name="formation_id" required>
          <?php foreach($formations as $f): ?>
            <option value="<?= (int)$f['id'] ?>"><?= e($f['titre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="row"><label>Date début *</label><input type="date" name="date_debut" required></div>
      <div class="row"><label>Date fin *</label><input type="date" name="date_fin" required></div>
      <div class="row"><label>Salle *</label><input name="salle" required></div>
      <div class="row"><label>Capacité *</label><input type="number" name="capacite" min="1" required></div>
      <div class="row">
        <label>Formateur *</label>
        <select name="formateur_id" required>
          <?php foreach($formateurs as $fm): ?>
            <option value="<?= (int)$fm['id'] ?>"><?= e($fm['nom']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn">Enregistrer</button>
      <a class="btn" href="sessions.php">Annuler</a>
    </form>

  <?php elseif($action==='edit'): ?>
    <h2>Modifier la session</h2>
    <form method="post" action="sessions.php?action=update">
      <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
      <div class="row">
        <label>Formation *</label>
        <select name="formation_id" required>
          <?php foreach($formations as $f): ?>
            <option value="<?= (int)$f['id'] ?>" <?= $f['id']==$s['formation_id']?'selected':'' ?>><?= e($f['titre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="row"><label>Date début *</label><input type="date" name="date_debut" value="<?= e($s['date_debut']) ?>" required></div>
      <div class="row"><label>Date fin *</label><input type="date" name="date_fin" value="<?= e($s['date_fin']) ?>" required></div>
      <div class="row"><label>Salle *</label><input name="salle" value="<?= e($s['salle']) ?>" required></div>
      <div class="row"><label>Capacité *</label><input type="number" name="capacite" min="1" value="<?= (int)$s['capacite'] ?>" required></div>
      <div class="row">
        <label>Formateur *</label>
        <select name="formateur_id" required>
          <?php foreach($formateurs as $fm): ?>
            <option value="<?= (int)$fm['id'] ?>" <?= ($aff && $fm['id']==$aff['id'])?'selected':'' ?>><?= e($fm['nom']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="row">
        <label>Statut</label>
        <select name="statut">
          <?php foreach(['PLANIFIEE','OUVERTE','CLOTUREE','ANNULEE'] as $st): ?>
            <option <?= $st===$s['statut']?'selected':'' ?>><?= $st ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn">Enregistrer</button>
      <a class="btn" href="sessions.php">Annuler</a>
    </form>

  <?php else:
    $sql = "SELECT s.*, f.titre AS formation,
            (SELECT COUNT(*) FROM inscription i WHERE i.session_id=s.id AND i.statut<>'ANNULE') AS inscrits,
            s.capacite - (SELECT COUNT(*) FROM inscription i2 WHERE i2.session_id=s.id AND i2.statut<>'ANNULE') AS places_disponibles,
            (SELECT CONCAT(prenom,' ',nom) FROM affectation a JOIN formateur fm ON fm.id=a.formateur_id WHERE a.session_id=s.id LIMIT 1) AS formateur_principal
            FROM session s
            JOIN formation f ON f.id=s.formation_id
            ORDER BY s.date_debut";
    $rows = $pdo->query($sql)->fetchAll();
  ?>
    <table>
      <tr><th>Formation</th><th>Début</th><th>Fin</th><th>Salle</th><th>Capacité</th><th>Inscrits</th><th>Restant</th><th>Formateur</th><th>Statut</th><th>Actions</th></tr>
      <?php foreach($rows as $r): ?>
        <tr>
          <td><?= e($r['formation']) ?></td>
          <td><?= e($r['date_debut']) ?></td>
          <td><?= e($r['date_fin']) ?></td>
          <td><?= e($r['salle']) ?></td>
          <td><?= (int)$r['capacite'] ?></td>
          <td><?= (int)$r['inscrits'] ?></td>
          <td><?= (int)$r['places_disponibles'] ?></td>
          <td><?= e($r['formateur_principal'] ?? '—') ?></td>
          <td><?= e($r['statut']) ?></td>
          <td>
            <a class="btn" href="sessions.php?action=edit&id=<?= (int)$r['id'] ?>">✏️</a>
            <a class="btn" href="sessions.php?action=delete&id=<?= (int)$r['id'] ?>" onclick="return confirm('Supprimer cette session ?');">🗑️</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</body>
</html>
