<?php

$userId = filter_input(INPUT_GET, 'affid', FILTER_VALIDATE_INT);

if (!$userId) {
    http_response_code(404);
    exit;
}

header("Location: /user/{$userId}/referrals", true, 302);
exit;
