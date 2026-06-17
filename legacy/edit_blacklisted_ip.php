<?php

$entryId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
    ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$entryId) {
    http_response_code(404);
    exit;
}

header("Location: /ip-blacklist/{$entryId}/edit", true, 302);
exit;
