<?php
require __DIR__.'/db.php';
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$action = $_GET['action'] ?? 'list';
$msg = $err = null;

/* CREATE */
if ($action === 'create' && $_SERVER['REQUEST_METHOD']==='POST') {
  try {
    $sql = "INSERT INTO formation(titre, domaine, niveau, description) VALUES(?,?,?,?)";
    $pdo->prepare($sql)->execute([
      trim($_POST['titre'] ?? ''),
      trim($_POST['domaine'] ?? ''),
      trim($_POST['niveau'] ?? ''),
      $_POST['description'] ?? null
    ]);
    header('Location: formations.php?msg=ajoute'); exit;
  } catch(PDOException $ex){ $err = $ex->getMessage(); }
}

/* UPDATE */
if ($action === 'update' && $_SERVER['REQUEST_METHOD']==='POST') {
  try {
    $pdo->prepare("UPDATE formation SET titre=?, domaine=?, niveau=?, description=? WHERE id=?")
        ->execute([
          trim($_POST['titre'] ?? ''),
          trim($_POST['domaine'] ?? ''),
          trim($_POST['niveau'] ?? ''),
          $_POST['description'] ?? null,
          (int)$_POST['id']
        ]);
    header('Location: formations.php?msg=modifie'); exit;
  } catch(PDOException $ex){ $err = $ex->getMessage(); }
}

/* DELETE */
if ($action === 'delete') {
  try {
    $pdo->prepare("DELETE FROM formation WHERE id=?")->execute([(int)$_GET['id']]);
    header('Location: formations.php?msg=supprime'); exit;
  } catch(PDOException $ex){
    // Si des sessions existent, la suppression peut échouer (clé étrangère)
    $err = "Impossible de supprimer : des sessions existent pour cette formation.";
    $action = 'list';
  }
}

/* EDIT form */
if ($action === 'edit') {
  $st = $pdo->prepare("SELECT * FROM formation WHERE id=?");
  $st->execute([(int)$_GET['id']]);
  $f = $st->fetch();
  if(!$f){ die("Formation introuvable."); }
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Formations</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{font-family:system-ui;margin:32px}
h1{margin-bottom:14px}
.btn{display:inline-block;padding:8px 12px;border:1px solid #ccc;border-radius:8px;text-decoration:none}
table{border-collapse:collapse;width:100%;max-width:1000px}
th,td{border:1px solid #ddd;padding:8px;vertical-align:top}
.actions a{margin-right:8px}
.notice{padding:10px;border-radius:8px;margin:10px 0}
.ok{background:#e7f7ee;border:1px solid #bfe8cf}
.err{background:#fde2e2;border:1px solid #f5b6b6}
input,textarea,select{padding:8px;width:100%;max-width:520px}
form .row{margin-bottom:10px}
</style>
</head>
<body>
  <h1>📚 Formations</h1>
  <p><a class="btn" href="list_formations.php">← Retour liste simple</a>
     <a class="btn" href="formations.php?action=new">➕ Ajouter une formation</a></p>

  <?php if(isset($_GET['msg']) && $_GET['msg']==='ajoute'): ?><div class="notice ok">Formation ajoutée.</div><?php endif; ?>
  <?php if(isset($_GET['msg']) && $_GET['msg']==='modifie'): ?><div class="notice ok">Formation modifiée.</div><?php endif; ?>
  <?php if(isset($_GET['msg']) && $_GET['msg']==='supprime'): ?><div class="notice ok">Formation supprimée.</div><?php endif; ?>
  <?php if($err): ?><div class="notice err"><?= e($err) ?></div><?php endif; ?>

  <?php if($action==='new'): ?>
    <h2>Ajouter</h2>
    <form method="post" action="formations.php?action=create">
      <div class="row"><label>Titre *</label><input name="titre" required></div>
      <div class="row"><label>Domaine *</label><input name="domaine" required></div>
      <div class="row"><label>Niveau *</label><input name="niveau" required></div>
      <div class="row"><label>Description</label><textarea name="description" rows="4"></textarea></div>
      <button class="btn" type="submit">Enregistrer</button>
      <a class="btn" href="formations.php">Annuler</a>
    </form>

  <?php elseif($action==='edit'): ?>
    <h2>Modifier</h2>
    <form method="post" action="formations.php?action=update">
      <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
      <div class="row"><label>Titre *</label><input name="titre" value="<?= e($f['titre']) ?>" required></div>
      <div class="row"><label>Domaine *</label><input name="domaine" value="<?= e($f['domaine']) ?>" required></div>
      <div class="row"><label>Niveau *</label><input name="niveau" value="<?= e($f['niveau']) ?>" required></div>
      <div class="row"><label>Description</label><textarea name="description" rows="4"><?= e($f['description']) ?></textarea></div>
      <button class="btn" type="submit">Enregistrer</button>
      <a class="btn" href="formations.php">Annuler</a>
    </form>

  <?php else: 
    $rows = $pdo->query("SELECT id,titre,domaine,niveau,description FROM formation ORDER BY created_at DESC")->fetchAll();
  ?>
    <table>
      <tr><th>Titre</th><th>Domaine</th><th>Niveau</th><th>Description</th><th>Actions</th></tr>
      <?php foreach($rows as $r): ?>
        <tr>
          <td><?= e($r['titre']) ?></td>
          <td><?= e($r['domaine']) ?></td>
          <td><?= e($r['niveau']) ?></td>
          <td><?= nl2br(e($r['description'])) ?></td>
          <td class="actions">
            <a class="btn" href="formations.php?action=edit&id=<?= (int)$r['id'] ?>">✏️ Modifier</a>
            <a class="btn" href="formations.php?action=delete&id=<?= (int)$r['id'] ?>" onclick="return confirm('Supprimer cette formation ?');">🗑️ Supprimer</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</body>
</html>
