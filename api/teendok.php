<?php

function valasz(int $kod, mixed $adat = null): never {
    http_response_code($kod);
    if ($kod !== 204) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($adat, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
    exit;
}    
valasz(200, ['uzenet' => 'Szia, működik!']);

?>