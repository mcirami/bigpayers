<?php

$campaignId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$campaignId) {
    http_response_code(404);
    exit;
}

header("Location: /advertisers/{$campaignId}/edit", true, 302);
exit;
