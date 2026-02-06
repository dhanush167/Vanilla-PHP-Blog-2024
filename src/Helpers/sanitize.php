<?php
declare(strict_types=1);

/**
 * Output escaping shortcut
 * Use: <?= e($value) ?>
 */
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Basic input sanitizers (optional helpers)
 */
function sanitize_string(string $value, int $maxLen = 255): string
{
    $value = trim($value);
    if (mb_strlen($value) > $maxLen) {
        $value = mb_substr($value, 0, $maxLen);
    }
    return $value;
}

function sanitize_int(mixed $value, int $default = 0): int
{
    if (is_int($value)) return $value;
    if (is_string($value) && ctype_digit($value)) return (int)$value;
    return $default;
}

function sanitize_float(mixed $value, float $default = 0.0): float
{
    if (is_float($value) || is_int($value)) return (float)$value;
    if (is_string($value) && is_numeric($value)) return (float)$value;
    return $default;
}
