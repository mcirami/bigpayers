<?php

$offerId = filter_input(INPUT_GET, 'offid', FILTER_VALIDATE_INT);

if (!$offerId) {
    http_response_code(404);
    exit;
}

header("Location: /offer/{$offerId}/postback", true, 302);
exit;
