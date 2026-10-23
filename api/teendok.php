<?php

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

valasz(200, json_torzs());

?>