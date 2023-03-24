<?php

use Kiwilan\Archive\Archive;
use Kiwilan\Archive\ArchiveItem;
use Kiwilan\Archive\ArchiveUtils;
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

it('can read', function () {
    foreach (ARCHIVES as $name => $path) {
        $archive = Archive::make($path);
        $files = $archive->files();
        $extension = ArchiveUtils::getExtension($path);

        expect($archive->os())->toBe(PHP_OS_FAMILY);
        expect($archive->extension())->toBe($extension);
        expect($archive->path())->toBe($path);
        expect($archive->type())->toBeInstanceOf(ArchiveEnum::class);
        expect($files)->toBeIterable();
        expect($archive->count())->toBeGreaterThan(0);
    }
});

it('can failed', function () {
    expect(fn () => Archive::make(FAILED))->toThrow(\Exception::class);
});

it('can extract', function () {
    foreach (ARCHIVES as $name => $path) {
        $archive = Archive::make($path);
        $content = $archive->extractFile('archive/cover.jpeg');

        expect($content)->toBeString();
    }
});

it('can extract with base64', function () {
    foreach (ARCHIVES as $name => $path) {
        $archive = Archive::make($path);
        $content = $archive->extractFile('archive/cover.jpeg', true);
        $isBase64 = ArchiveUtils::isBase64($content);

        expect($isBase64)->toBeTrue();
    }
});

it('can extract failed', function () {
    foreach (ARCHIVES as $name => $path) {
        $archive = Archive::make($path);

        expect(fn () => $archive->extractFile('archive/cover'))->toThrow(\Exception::class);
    }
});

it('can find files', function () {
    foreach (ARCHIVES as $name => $path) {
        $archive = Archive::make($path);
        $files = $archive->findAll('jpeg');

        expect($files)->toBeIterable();
    }
});

it('can find file', function () {
    foreach (ARCHIVES as $name => $path) {
        $archive = Archive::make($path);
        $file = $archive->find('jpeg');

        expect($file)->toBeInstanceOf(ArchiveItem::class);
    }
});

it('can find and extract specific file', function () {
    $archive = Archive::make(ZIP);
    $file = $archive->find('metadata.xml');
    $content = $archive->extractFile($file->path());

    expect($file)->toBeInstanceOf(ArchiveItem::class);
    expect($content)->toBeString();
});
