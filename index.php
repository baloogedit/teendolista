<?php
// ======================= index.php (HTML váz) =======================
// A PHP csak egyszer adja ki az oldalt a CSRF tokennel; utána minden adat fetch-csel jön.
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
  <link rel="stylesheet" href="/style.css">
</head>
<body>
  <main>
    <h1>📝 Teendőlista</h1>
    <form id="uj">
      <input name="cim" required maxlength="200" placeholder="pl. Anatómia ZH-ra készülni">
      <button>Hozzáad</button>
    </form>
    <p id="hiba" role="alert"></p>
    <ul id="lista"></ul>
  </main>
  <script src="/app.js"></script>
</body>
</html>
