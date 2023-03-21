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

it('can read archive file', function () {
    $archive = Archive::make(ZIP);

    expect($archive->path())->toBe(ZIP);
    expect($archive->extension())->toBe(ArchiveUtils::getExtension(ZIP));
    expect($archive->type())->toBe(ArchiveEnum::zip);

    $archive->parse(function (ReaderFile $file) {
        dump($file->name());
    });
});

it('can read rar archive file', function () {
    $archive = Archive::make(RAR);

    expect($archive->path())->toBe(RAR);
    expect($archive->extension())->toBe(ArchiveUtils::getExtension(RAR));
    expect($archive->type())->toBe(ArchiveEnum::rar);

    $archive->parse(function (ReaderFile $file) {
        dump($file->name());
    });
});
