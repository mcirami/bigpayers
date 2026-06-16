<?php

http_response_code(410);
header('Content-Type: application/json');

echo json_encode([
    'error' => 'This legacy geo-rule endpoint is retired.',
    'replacement' => 'GET|POST /offer/rules/geo/{rule}',
]);
