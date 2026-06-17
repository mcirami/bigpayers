<?php

$urlId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$urlId) {
    http_response_code(404);
    exit;
}

header("Location: /offer/urls/{$urlId}/edit", true, 302);
exit;
