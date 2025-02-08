<?php

function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function sanitizeInput($input) {
    return trim(strip_tags($input));
}
