<?php

http_response_code(410);
header('Content-Type: application/json');
echo json_encode([
    'error' => 'retired',
    'replacement' => '/chat-log/view/{saleLogId}/delete',
]);
