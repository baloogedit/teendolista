<?php

$pdo = new PDO('sqlite:' . __DIR__ . '/teendok.sqlite');

$pdo->exec(
  "CREATE TABLE IF NOT EXISTS teendo (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  cim VARCHAR(200) NOT NULL,
  kesz INTEGER NOT NULL DEFAULT 0,
  letrehozva TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
  )
");

echo "Kész\n";

?>