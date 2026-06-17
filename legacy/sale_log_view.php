<?php

$saleLogId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
    ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$saleLogId) {
    http_response_code(404);
    exit;
}

header("Location: /chat-log/view/{$saleLogId}", true, 302);
exit;
