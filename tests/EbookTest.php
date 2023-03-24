<?php

use Kiwilan\Archive\Archive;
use Kiwilan\Archive\Enums\ArchiveEnum;

define('EPUB', __DIR__.'/media/epub.epub');
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

it('can read epub', function () {
    $archive = Archive::make(EPUB);
    $files = $archive->files();
    $extension = pathinfo(EPUB, PATHINFO_EXTENSION);

    expect($archive->os())->toBe(PHP_OS_FAMILY);
    expect($archive->extension())->toBe($extension);
    expect($archive->path())->toBe(EPUB);
    expect($archive->type())->toBeInstanceOf(ArchiveEnum::class);
    expect($files)->toBeIterable();
    expect($archive->count())->toBeGreaterThan(0);
});

it('can read cba', function () {
    foreach (CBA_ITEMS as $name => $path) {
        $archive = Archive::make($path);
        $files = $archive->files();
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        expect($archive->os())->toBe(PHP_OS_FAMILY);
        expect($archive->extension())->toBe($extension);
        expect($archive->path())->toBe($path);
        expect($archive->type())->toBeInstanceOf(ArchiveEnum::class);
        expect($files)->toBeIterable();
        expect($archive->count())->toBeGreaterThan(0);
    }
});
