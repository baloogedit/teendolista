<?php
declare(strict_types=1);

function db(): PDO {
    static $pdo = null;   // csak az első hívásnál csatlakozik, utána újrahasználja
    $pdo ??= new PDO('sqlite:' . __DIR__ . '/../teendok.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // sorok tömbként: $sor['cim']
    ]);
    return $pdo;
}

function valasz(int $kod, mixed $adat = null): never {
    http_response_code($kod);
    if ($kod !== 204) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($adat, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
    exit;
}

function json_torzs(): array {
    // 1) Csak JSON-t fogadunk el → különben 415
    if (!str_starts_with($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
        valasz(415, ['hiba' => 'A törzs típusa application/json legyen.']);
    }
    // 2) A nyers törzs a php://input-ban van (a $_POST JSON-nál üres!)
    try {
        $d = json_decode((string)file_get_contents('php://input'), true, 16, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        valasz(400, ['hiba' => 'Érvénytelen JSON.']);
    }
    // 3) Objektumot várunk, nem pl. "szia"-t vagy 5-öt
    if (!is_array($d)) {
        valasz(400, ['hiba' => 'JSON objektumot várok.']);
    }
    return $d;
}

function formaz(array $s): array {
    return ['id' => (int)$s['id'], 'cim' => $s['cim'], 'kesz' => (bool)$s['kesz']];
}

function teendo(int $id): array {
    $stmt = db()->prepare('SELECT id, cim, kesz FROM teendo WHERE id = ?');
    $stmt->execute([$id]);
    $sor = $stmt->fetch();
    if (!$sor) {
        valasz(404, ['hiba' => 'Nincs ilyen teendő.']);
    }
    return formaz($sor);
}

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if ($id) {
            valasz(200, teendo($id));
        }
        $sorok = db()->query('SELECT id, cim, kesz FROM teendo ORDER BY id')->fetchAll();
        valasz(200, array_map('formaz', $sorok));

    case 'POST':
        $d = json_torzs();
        $cim = trim((string)($d['cim'] ?? ''));
        if ($cim === '' || mb_strlen($cim) > 200) {
            valasz(422, ['hiba' => 'A cím 1–200 karakter legyen.']);
        }
        db()->prepare('INSERT INTO teendo (cim) VALUES (?)')->execute([$cim]);
        $uj = (int)db()->lastInsertId();
        header('Location: /api/teendok.php?id=' . $uj);
        valasz(201, ['id' => $uj, 'cim' => $cim, 'kesz' => false]);


     case 'PATCH':
        if (!$id) {
            valasz(400, ['hiba' => 'Hiányzó vagy hibás id.']);
        }
        teendo($id);                       // ha nincs ilyen → 404
        $d = json_torzs();
        if (!array_key_exists('kesz', $d) || !is_bool($d['kesz'])) {
            valasz(422, ['hiba' => 'A kesz mező true vagy false legyen.']);
        }
        db()->prepare('UPDATE teendo SET kesz = ? WHERE id = ?')->execute([$d['kesz'] ? 1 : 0, $id]);
        valasz(200, teendo($id));          // a frissített sort küldjük vissza

    case 'DELETE':
        if (!$id) {
            valasz(400, ['hiba' => 'Hiányzó vagy hibás id.']);
        }
        $stmt = db()->prepare('DELETE FROM teendo WHERE id = ?');
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) {     // nem volt mit törölni
            valasz(404, ['hiba' => 'Nincs ilyen teendő.']);
        }
        valasz(204);
        
    default:
        header('Allow: GET, POST, PATCH, DELETE');
        valasz(405, ['hiba' => 'Nem támogatott metódus.']);
}
