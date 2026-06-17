<?php

$userId = filter_input(INPUT_GET, 'uid', FILTER_VALIDATE_INT);

if (!$userId) {
    http_response_code(404);
    exit;
}

header("Location: /user/{$userId}/ban", true, 302);
exit;
