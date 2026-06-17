<?php

$offerId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$userId = filter_input(INPUT_GET, 'u', FILTER_VALIDATE_INT);

if (!$offerId || !$userId) {
    http_response_code(404);
    exit;
}

header("Location: /offer/{$offerId}/approve-request/{$userId}", true, 302);
exit;
