<?php

$userId = filter_input(INPUT_GET, 'idrep', FILTER_VALIDATE_INT);

if (!$userId) {
    http_response_code(404);
    exit;
}

header("Location: /user/{$userId}/edit", true, 302);
exit;
