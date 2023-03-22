<?php

use Archive7z\Archive7z;
use Kiwilan\Archive\ArchiveUtils;

define('FAILED', __DIR__.'/media/test.zip');
define('SEVENZIP', __DIR__.'/media/archive.7z');
define('RAR', __DIR__.'/media/archive.rar');
define('TAR', __DIR__.'/media/archive.tar');
define('TARBZ2', __DIR__.'/media/archive.tar.bz2');
define('TARGZ', __DIR__.'/media/archive.tar.gz');
define('TARXZ', __DIR__.'/media/archive.tar.xz');
define('ZIP', __DIR__.'/media/archive.zip');

define('EPUB', __DIR__.'/media/le-clan-de-lours-des-cavernes.epub');
define('CBZ', __DIR__.'/media/grise-bouille-tome-1.cbz');
define('CBR', __DIR__.'/media/grise-bouille-tome-1.cbr');
define('CB7', __DIR__.'/media/grise-bouille-tome-1.cb7');

define('ARCHIVES', [
    'SEVENZIP' => SEVENZIP,
    'RAR' => RAR,
    'ZIP' => ZIP,
]);

define('COMICS', [
    'CBZ' => CBZ,
    'CBR' => CBR,
    'CB7' => CB7,
]);

it('can make archive', function () {
    foreach (ARCHIVES as $path) {
        $archive = new Archive7z($path);
        expect($archive->isValid())->toBeTrue();
    }
});

it('can read archive files', function () {
    foreach (ARCHIVES as $path) {
        $archive = new Archive7z($path);

        expect($archive->getEntries())->toBeIterable();
    }
});

it('can get archive file content', function () {
    foreach (ARCHIVES as $path) {
        $archive = new Archive7z($path);

        $cover = null;
        foreach ($archive->getEntries() as $key => $entry) {
            if (str_contains($entry->getPath(), 'cover')) {
                $cover = $entry->getContent();
            }
        }

        expect($cover)->toBeString();
    }
});

it('can extract archive file', function () {
    foreach (ARCHIVES as $name => $path) {
        $archive = new Archive7z($path);

        $file = null;
        foreach ($archive->getEntries() as $key => $entry) {
            if ($entry->getPath() === 'archive/cover.jpeg') {
                $file = $entry;
            }
        }

        $content = $file->getContent();
        $extension = pathinfo($file->getPath(), PATHINFO_EXTENSION);

        $currentdir = __DIR__;
        $path = "{$currentdir}/output/cover-{$name}.{$extension}";
        if (file_exists($path)) {
            unlink($path);
        }
        ArchiveUtils::stringToImage($content, $path);
        expect($content)->toBeString();
        expect($path)->toBeReadableFile();
    }
});

it('can extract comics file', function () {
    foreach (COMICS as $name => $path) {
        $archive = new Archive7z($path);

        $file = null;
        foreach ($archive->getEntries() as $key => $entry) {
            if ($entry->getPath() === 'grise-bouille-tome-1/volume-1/tad_001.jpg') {
                $file = $entry;
            }
        }

        $content = $file->getContent();
        $extension = pathinfo($file->getPath(), PATHINFO_EXTENSION);

        $currentdir = __DIR__;
        $path = "{$currentdir}/output/cover-{$name}.{$extension}";
        if (file_exists($path)) {
            unlink($path);
        }
        ArchiveUtils::stringToImage($content, $path);
        expect($content)->toBeString();
        expect($path)->toBeReadableFile();
    }
});

it('can extract epub file', function () {
    $archive = new Archive7z(EPUB);

    $file = null;
    foreach ($archive->getEntries() as $key => $entry) {
        if ($entry->getPath() === 'cover.jpeg') {
            $file = $entry;
        }
    }

    $content = $file->getContent();
    $extension = pathinfo($file->getPath(), PATHINFO_EXTENSION);

    $currentdir = __DIR__;
    $path = "{$currentdir}/output/cover-EPUB.{$extension}";
    if (file_exists($path)) {
        unlink($path);
    }
    ArchiveUtils::stringToImage($content, $path);
    expect($content)->toBeString();
    expect($path)->toBeReadableFile();
});

it('can extract epub metadata file', function () {
    $archive = new Archive7z(EPUB);

    $file = null;
    foreach ($archive->getEntries() as $key => $entry) {
        $path = $entry->getPath();
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if ($extension === 'opf') {
            $file = $entry;
        }
    }

    $content = $file->getContent();
    expect($content)->toBeString();
});
