<?php

$offerId = filter_input(INPUT_GET, 'idoffer', FILTER_VALIDATE_INT);

if (!$offerId) {
    http_response_code(404);
    exit;
}

header("Location: /offer/edit/{$offerId}", true, 302);
exit;
