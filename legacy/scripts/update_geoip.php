<?php

http_response_code(410);
header('Content-Type: application/json');

echo json_encode([
    'error' => 'This legacy GeoIP web updater is retired.',
    'replacement' => 'Use the provisioning or ops workflow.',
]);
