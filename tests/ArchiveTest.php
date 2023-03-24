<?php

use Kiwilan\Archive\Archive;
use Kiwilan\Archive\ArchiveItem;
use Kiwilan\Archive\Enums\ArchiveEnum;

define('FAILED', __DIR__.'/media/test.zip');
define('SEVENZIP', __DIR__.'/media/archive.7z');
define('RAR', __DIR__.'/media/archive.rar');
define('TAR', __DIR__.'/media/archive.tar');
define('TARBZ2', __DIR__.'/media/archive.tar.bz2');
define('TARGZ', __DIR__.'/media/archive.tar.gz');
define('TARXZ', __DIR__.'/media/archive.tar.xz');
define('ZIP', __DIR__.'/media/archive.zip');
define('ARCHIVES', [
    'SEVENZIP' => SEVENZIP,
    'RAR' => RAR,
    'TAR' => TAR,
    'TARBZ2' => TARBZ2,
    'TARGZ' => TARGZ,
    'TARXZ' => TARXZ,
    'ZIP' => ZIP,
]);

it('can read', function (string $path) {
    $archive = Archive::make($path);
    $files = $archive->files();
    $extension = pathinfo($path, PATHINFO_EXTENSION);

    expect($archive->os())->toBe(PHP_OS_FAMILY);
    expect($archive->extension())->toBe($extension);
    expect($archive->path())->toBe($path);
    expect($archive->type())->toBeInstanceOf(ArchiveEnum::class);
    expect($files)->toBeIterable();
    expect($archive->count())->toBeGreaterThanOrEqual(4);
})->with(ARCHIVES);

it('can failed', function () {
    expect(fn () => Archive::make(FAILED))->toThrow(\Exception::class);
});

it('can extract', function (string $path) {
    $archive = Archive::make($path);
    $content = $archive->contentFile('archive/cover.jpeg');

    expect($content)->toBeString();
})->with(ARCHIVES);

it('can extract with base64', function (string $path) {
    $archive = Archive::make($path);
    $content = $archive->contentFile('archive/cover.jpeg', true);
    $isBase64 = isBase64($content);

    expect($isBase64)->toBeTrue();
})->with(ARCHIVES);

it('can extract failed', function (string $path) {
    $archive = Archive::make($path);

    expect(fn () => $archive->contentFile('archive/cover'))->toThrow(\Exception::class);
})->with(ARCHIVES);

it('can find files', function (string $path) {
    $archive = Archive::make($path);
    $files = $archive->findAll('jpeg');

    expect($files)->toBeIterable();
})->with(ARCHIVES);

it('can find file', function (string $path) {
    $archive = Archive::make($path);
    $file = $archive->find('jpeg');

    expect($file)->toBeInstanceOf(ArchiveItem::class);
})->with(ARCHIVES);

it('can find and extract specific file', function () {
    $archive = Archive::make(ZIP);
    $file = $archive->find('metadata.xml');
    $content = $archive->contentFile($file->path());

    expect($file)->toBeInstanceOf(ArchiveItem::class);
    expect($content)->toBeString();
})->with(ARCHIVES);
