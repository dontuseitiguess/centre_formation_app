<?php
require __DIR__ . '/db.php';

// 1) récupérer les formations
$sql = "SELECT id, titre, domaine, niveau, description, created_at
        FROM formation
        ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$formations = $stmt->fetchAll();

// 2) petite fonction helper pour échapper le HTML (sécurité XSS)
function e($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Formations - Centre de formation</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:40px;line-height:1.5}
    h1{margin-bottom:16px}
    .card{border:1px solid #ddd;border-radius:12px;padding:16px;margin-bottom:12px}
    .pill{display:inline-block;padding:2px 8px;border:1px solid #ccc;border-radius:999px;font-size:12px;margin-right:6px}
    .empty{color:#777}
    a{color:inherit}
  </style>
</head>
<body>
  <h1>📚 Liste des formations</h1>

  <?php if (!$formations): ?>
    <p class="empty">Aucune formation pour le moment.</p>
  <?php else: ?>
    <?php foreach ($formations as $f): ?>
      <div class="card">
        <h2><?= e($f['titre']) ?></h2>
        <div>
          <span class="pill"><?= e($f['domaine']) ?></span>
          <span class="pill"><?= e($f['niveau']) ?></span>
        </div>
        <?php if (!empty($f['description'])): ?>
          <p><?= e($f['description']) ?></p>
        <?php endif; ?>
        <p style="font-size:12px;color:#666">Créée le <?= e($f['created_at']) ?></p>
        <p><a href="sessions.php?formation_id=<?= (int)$f['id'] ?>">Voir les sessions ➜</a></p>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</body>
</html>
