<?php

$offerId = filter_input(INPUT_GET, 'offid', FILTER_VALIDATE_INT);

if (!$offerId) {
    http_response_code(404);
    exit;
}

header("Location: /offer/rules/{$offerId}", true, 302);
exit;
