<?php

http_response_code(410);
header('Content-Type: application/json');

echo json_encode([
    'error' => 'This legacy device-rule creation endpoint is retired.',
    'replacement' => 'POST /offer/rules/device',
]);
