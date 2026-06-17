<?php

$ruleId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
    ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$ruleId) {
    http_response_code(404);
    exit;
}

header("Location: /offer/rules/none-unique/{$ruleId}/edit", true, 302);
exit;
