<?php

use Kiwilan\Archive\Archive;
use Kiwilan\Archive\Enums\ArchiveEnum;

it('can read epub', function () {
    $archive = Archive::read(EPUB);
    $files = $archive->files();
    $extension = pathinfo(EPUB, PATHINFO_EXTENSION);

    expect($archive->extension())->toBe($extension);
    expect($archive->path())->toBe(EPUB);
    expect($archive->type())->toBeInstanceOf(ArchiveEnum::class);
    expect($files)->toBeIterable();
    expect($archive->count())->toBeGreaterThan(0);
});

// it('can read cba', function (string $path) {
//     $archive = Archive::read($path);
//     $files = $archive->files();
//     $extension = pathinfo($path, PATHINFO_EXTENSION);

//     // expect($archive->os())->toBe(PHP_OS_FAMILY);
//     // expect($archive->extension())->toBe($extension);
//     // expect($archive->path())->toBe($path);
//     // expect($archive->type())->toBeInstanceOf(ArchiveEnum::class);
//     // expect($files)->toBeIterable();
//     // expect($archive->count())->toBeGreaterThan(0);
// })->with(CBA_ITEMS);
