<?php
declare(strict_types=1);

/**
 * Build app URLs safely
 */
function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return rtrim(BASE_URL, '/') . ($path !== '' ? '/' . $path : '');
}
