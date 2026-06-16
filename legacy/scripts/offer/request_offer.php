<?php

http_response_code(410);
header('Content-Type: application/json');

echo json_encode([
    'error' => 'This legacy offer-request endpoint is retired.',
    'replacement' => '/offer/{id}/request',
]);
