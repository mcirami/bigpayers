<?php

$pendingConversionId = filter_input(INPUT_GET, 'pcid', FILTER_VALIDATE_INT)
    ?: filter_input(INPUT_GET, 'cid', FILTER_VALIDATE_INT)
    ?: filter_input(INPUT_POST, 'pendingConversionId', FILTER_VALIDATE_INT);

if (!$pendingConversionId) {
    http_response_code(404);
    exit;
}

header("Location: /chat-log/add/{$pendingConversionId}", true, 302);
exit;
