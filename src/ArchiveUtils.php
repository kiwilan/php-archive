<?php

namespace Kiwilan\Archive;

use Symfony\Component\Console\Output\ConsoleOutput;

class ArchiveUtils
{
    public static function getExtension(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    public static function isImage(?string $extension): bool
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

    public static function isBase64(?string $data): bool
    {
        if (! $data) {
            return false;
        }

        if (base64_encode(base64_decode($data, true)) === $data) {
            return true;
        }

        return false;
    }

    public static function base64ToImage(?string $base64, string $path): string|false
    {
        if (! $base64) {
            return false;
        }

        $content = base64_decode($base64, true);

        return file_put_contents($path, $content);
    }

    public static function stringToImage(?string $content, string $path): string|false
    {
        if (! $content) {
            return false;
        }

        return file_put_contents($path, $content);
    }

    public static function isHidden(string $path): bool
    {
        return substr($path, 0, 1) === '.';
    }

    public static function print(string $message): void
    {
        $output = new ConsoleOutput();
        $output->writeln($message);
    }

    public static function bytesToHuman(mixed $bytes): ?string
    {
        if (! $bytes) {
            return null;
        }

        if (! is_numeric($bytes)) {
            $bytes = intval($bytes);
        }

        if ($bytes == 0) {
            return '0.00 B';
        }

        $size = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $floor = floor(log($bytes, 1024));
        $format = $size[$floor];

        $round = round($bytes / pow(1024, $floor), 2);

        return "{$round} {$format}";
    }
}
