<?php

$offerId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
    ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$offerId) {
    http_response_code(404);
    exit;
}

header("Location: /offer/rules/{$offerId}/none-unique/create", true, 302);
exit;
