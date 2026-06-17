<?php

$bonusId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
    ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$bonusId) {
    http_response_code(404);
    exit;
}

header("Location: /bonuses/{$bonusId}/edit", true, 302);
exit;
