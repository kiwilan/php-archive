<?php

use Kiwilan\Archive\Archive;
use Kiwilan\Archive\Enums\ArchiveEnum;

it('can read epub', function () {
    $archive = Archive::read(EPUB);
    $files = $archive->getFiles();
    $extension = pathinfo(EPUB, PATHINFO_EXTENSION);

    expect($archive->getExtension())->toBe($extension);
    expect($archive->getPath())->toBe(EPUB);
    expect($archive->getType())->toBeInstanceOf(ArchiveEnum::class);
    expect($files)->toBeIterable();
    expect($archive->getCount())->toBeGreaterThan(0);
});

it('can read cba', function (string $path) {
    $archive = Archive::read($path);
    $files = $archive->getFiles();
    $extension = pathinfo($path, PATHINFO_EXTENSION);

    expect($archive->getExtension())->toBe($extension);
    expect($archive->getPath())->toBe($path);
    expect($archive->getType())->toBeInstanceOf(ArchiveEnum::class);
    expect($files)->toBeIterable();
    expect($archive->getCount())->toBeGreaterThan(0);
})->with(CBA_ITEMS);

it('can get cover with cba', function (string $path) {
    $archive = Archive::read($path);
    $files = $archive->filter('jpg');

    expect($files[0])->not()->toBeNull();
})->with(CBA_ITEMS);
