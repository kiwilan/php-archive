<?php

function isImage(?string $extension): bool
{
    return in_array($extension, [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'bmp',
        'webp',
        'svg',
        'ico',
        'avif',
    ], true);
}

function isBase64(?string $data): bool
{
    if (! $data) {
        return false;
    }

    if (base64_encode(base64_decode($data, true)) === $data) {
        return true;
    }

    return false;
}

function isHiddenFile(string $path): bool
{
    return substr($path, 0, 1) === '.';
}

function base64ToImage(?string $base64, string $path): bool
{
    if (! $base64) {
        return false;
    }

    $content = base64_decode($base64, true);
    $res = file_put_contents($path, $content);

    return $res;
}

function stringToImage(?string $content, string $path): bool
{
    if (! $content) {
        return false;
    }

    $res = file_put_contents($path, $content);

    return $res;
}
