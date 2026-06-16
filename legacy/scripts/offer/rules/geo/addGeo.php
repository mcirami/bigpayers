<?php

http_response_code(410);
header('Content-Type: application/json');

echo json_encode([
    'error' => 'This legacy geo-rule creation endpoint is retired.',
    'replacement' => 'POST /offer/rules/geo',
]);
