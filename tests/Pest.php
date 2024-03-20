<?php

define('FAILED', __DIR__.'/media/test.zip');
define('SEVENZIP', __DIR__.'/media/archive.7z');
define('RAR', __DIR__.'/media/archive.rar');
define('TAR', __DIR__.'/media/archive.tar');
define('TARBZ2', __DIR__.'/media/archive.tar.bz2');
define('TARGZ', __DIR__.'/media/archive.tar.gz');
define('ZIP', __DIR__.'/media/archive.zip');
define('PDF', __DIR__.'/media/pdf-example.pdf');
define('PDF_SIMPLE', __DIR__.'/media/pdf-simple.pdf');
define('PDF_EMPTY', __DIR__.'/media/pdf-empty.pdf');

define('ZIP_PASSWORD', __DIR__.'/media/archive-password.zip');
define('RAR_PASSWORD', __DIR__.'/media/archive-password.rar');
define('SEVENZIP_PASSWORD', __DIR__.'/media/archive-password.7z');
define('TAR_PASSWORD', __DIR__.'/media/archive-password.tar.gz');

define('ARCHIVES', [
    'SEVENZIP' => SEVENZIP,
    'RAR' => RAR,
    'ZIP' => ZIP,
]);
define('ARCHIVES_NATIVE', [
    'TAR' => TAR,
    'TARBZ2' => TARBZ2,
    'TARGZ' => TARGZ,
    'ZIP' => ZIP,
]);
define('EPUB', __DIR__.'/media/epub.epub');
define('EPUB_BAD_FILE', __DIR__.'/media/epub-bad-file.epub');
define('CBZ', __DIR__.'/media/cba.cbz');
define('CBR', __DIR__.'/media/cba.cbr');
define('CBT', __DIR__.'/media/cba.cbt');
define('CB7', __DIR__.'/media/cba.cb7');
define('CBA_ITEMS', [
    'CBZ' => CBZ,
    'CBR' => CBR,
    // 'CBT' => CBT,
    'CB7' => CB7,
]);

define('ARCHIVES_TAR', [
    'TAR' => TAR,
    'TARBZ2' => TARBZ2,
    'TARGZ' => TARGZ,
]);
define('ARCHIVES_ZIP', [
    'ZIP' => ZIP,
    'CBZ' => CBZ,
    'EPUB' => EPUB,
]);
define('ARCHIVES_PDF', [
    'PDF' => PDF,
]);
define('ARCHIVES_RAR', [
    'RAR' => RAR,
    'CBR' => CBR,
]);
define('ARCHIVES_7Z', [
    'SEVENZIP' => SEVENZIP,
    'CB7' => CB7,
]);

function mediaPath(string $filename): string
{
    $pathBase = __DIR__.'/media/';

    return $pathBase.$filename;
}

function outputPath(?string $path = null, ?string $filename = null): string
{
    $pathBase = __DIR__.'/output/';

    if ($path) {
        $pathBase .= $path.'/';
    }

    if (! file_exists($pathBase)) {
        mkdir($pathBase, 0755, true);
    }

    if ($filename) {
        $pathBase .= $filename;
    }

    return $pathBase;
}

function outputPathFake(): string
{
    return __DIR__.'/outpu/';
}

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

function listFiles(string $dir): array
{
    $files = array_diff(scandir($dir), ['.', '..', '.gitignore']);

    $items = [];
    foreach ($files as $file) {
        if (! is_dir("$dir/$file") && ! is_link("$dir/$file")) {
            $items[] = $file;
        } else {
            $items = array_merge($items, listFiles("$dir/$file"));
        }
    }

    return $items;
}

function recurseRmdir(string $dir)
{
    $exclude = ['.gitignore'];
    $it = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
    $it = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $file) {
        if ($file->isDir()) {
            rmdir($file->getPathname());
        } elseif (! in_array($file->getFilename(), $exclude)) {
            unlink($file->getPathname());
        }
    }
    // rmdir($dir);
}

function dotenv(): array
{
    $path = __DIR__.'/../';
    $lines = file($path.'.env');
    $dotenv = [];

    foreach ($lines as $line) {
        if (! empty($line)) {
            $data = explode('=', $line);
            $key = $data[0];
            if ($key === " \n ") {
                continue;
            }
            unset($data[0]);
            $value = implode('=', $data);

            $key = $key ? trim($key) : '';
            $value = $value ? trim($value) : '';

            if ($key === '') {
                continue;
            }

            $value = str_replace('"', '', $value);
            $value = str_replace("'", '', $value);

            $dotenv[$key] = $value;
        }
    }

    return $dotenv;
}

function getDotenv(string $key): string
{
    return dotenv()[$key] ?? '';
}

function getSevenZipBinaryPath(): string
{
    $os = PHP_OS_FAMILY;
    $dotenv = match ($os) {
        'Windows' => 'SEVEN_ZIP_BINARY_PATH_WINDOWS',
        'Darwin' => 'SEVEN_ZIP_BINARY_PATH_MACOS',
        default => 'SEVEN_ZIP_BINARY_PATH_LINUX',
    };

    return getDotenv($dotenv);
}
