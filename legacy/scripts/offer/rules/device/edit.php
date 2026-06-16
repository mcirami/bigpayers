<?php

http_response_code(410);
header('Content-Type: application/json');

echo json_encode([
    'error' => 'This legacy device-rule endpoint is retired.',
    'replacement' => 'GET|POST /offer/rules/device/{rule}',
]);
