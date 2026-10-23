<?php
// ======================= index.php (HTML váz) =======================
// A PHP csak egyszer adja ki az oldalt a CSRF tokennel; utána minden adat fetch-el jön.
declare(strict_types=1);
session_start();
$csrf = $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
?>
<!doctype html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf" content="<?= htmlspecialchars($csrf) ?>">
  <title>Teendőlista</title>
  <style>
    body { font-family: system-ui, sans-serif; max-width: 520px; margin: 40px auto; padding: 0 16px; }
    form { display: flex; gap: 8px; }
    form input { flex: 1; padding: 8px; }
    ul { list-style: none; padding: 0; }
    li { display: flex; align-items: center; gap: 10px; padding: 8px; border-bottom: 1px solid #ddd; }
    li span { flex: 1; }
    li.kesz span { text-decoration: line-through; color: #888; }
    #hiba { color: #b91c1c; }
  </style>
</head>
<body>
  <h1>Teendőlista</h1>
  <form id="uj">
    <input name="cim" required maxlength="200" placeholder="pl. Anatómia ZH-ra készülni">
    <button>Hozzáad</button>
  </form>
  <p id="hiba" role="alert"></p>
  <ul id="lista"></ul>
  <script src="/app.js"></script>
</body>
</html>
