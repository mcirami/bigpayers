<?php

$userId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
    ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$userId) {
    http_response_code(404);
    exit;
}

header("Location: /user/{$userId}/salary/showUpdate", true, 302);
exit;
