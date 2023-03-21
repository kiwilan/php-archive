<?php

use Kiwilan\Archive\Archive;
use Kiwilan\Archive\ArchiveUtils;
use Kiwilan\Archive\Enums\ArchiveEnum;
use Kiwilan\Archive\Readers\ReaderFile;

define('FAILED', __DIR__.'/media/test.zip');
define('ZIP', __DIR__.'/media/archive.zip');
define('RAR', __DIR__.'/media/archive.rar');

it('can make archive', function () {
    expect(Archive::make(ZIP))->toBeInstanceOf(\Kiwilan\Archive\ArchiveFile::class);
});

it('failed if file not exists', function () {
    expect(fn () => Archive::make(FAILED))->toThrow(\Exception::class);
});

it('can read archive', function () {
    $archive = Archive::make(ZIP);

    expect($archive->path())->toBe(ZIP);
    expect($archive->extension())->toBe(ArchiveUtils::getExtension(ZIP));
    expect($archive->type())->toBe(ArchiveEnum::zip);
    expect($archive->count())->toBeGreaterThan(0);
});

it('can read archive files', function () {
    $archive = Archive::make(ZIP);

    expect($archive->files())->toBeIterable();
});

it('can get archive file content', function () {
    $archive = Archive::make(ZIP);

    $file = $archive->parse(function (ReaderFile $file) {
        if (str_contains($file->name(), 'cover')) {
            return $file->content();
        }
    });

    expect($file)->toBeString();
});

it('can extract archive file', function () {
    $archive = Archive::make(ZIP);

    expect($archive->path())->toBe(ZIP);

    /** @var ReaderFile */
    $file = $archive->parse(function (ReaderFile $file) {
        if ($file->isImage() && $file->name() === 'archive/cover.jpeg') {
            return $file;
        }
    });

    $content = $file->content();
    $extension = $file->extension();

    $path = __DIR__.'/output/cover.'.$extension;
    if (file_exists($path)) {
        unlink($path);
    }
    ArchiveUtils::base64ToImage($content, $path);
    expect(ArchiveUtils::isBase64($content))->toBeTrue();
    expect($path)->toBeReadableFile();
});

it('can extract archive files', function () {
    $archive = Archive::make(ZIP);

    expect($archive->path())->toBe(ZIP);

    $cover = null;
    $extension = null;
    $archive->parse(function (ReaderFile $file) use (&$cover, &$extension) {
        if ($file->isImage() && $file->name() === 'archive/cover.jpeg') {
            $cover = $file->content();
            $extension = $file->extension();
        }
    });

    $path = __DIR__.'/output/cover.'.$extension;
    ArchiveUtils::base64ToImage($cover, $path);
    expect(ArchiveUtils::isBase64($cover))->toBeTrue();
    expect($path)->toBeReadableFile();
});

it('can get content after parse', function () {
    $archive = Archive::make(ZIP);

    $cover = null;
    foreach ($archive->files() as $name => $file) {
        if ($file->isImage() && $file->name() === 'archive/cover.jpeg') {
            $cover = $file->content();
        }
    }

    expect(ArchiveUtils::isBase64($cover))->toBeTrue();
});
