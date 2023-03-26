<?php

use Kiwilan\Archive\Archive;
use Kiwilan\Archive\ArchiveItem;
use Kiwilan\Archive\Enums\ArchiveEnum;

it('can read epub', function () {
    $archive = Archive::make(EPUB);
    $extension = pathinfo(EPUB, PATHINFO_EXTENSION);

    $files = $archive->files();

    $cover = $archive->find('cover.jpeg');
    $content = $archive->content($cover);

    $output = outputPath();
    stringToImage($content, "{$output}/cover.jpeg");

    $output = outputPath($archive->basename());
    $paths = $archive->extractAll($output);

    expect($archive->extension())->toBe($extension);
    expect($archive->path())->toBe(EPUB);
    expect($archive->type())->toBe(ArchiveEnum::zip);
    expect($archive->files())->toBeArray();
    expect($archive->count())->toBe(8);

    expect($cover)->toBeInstanceOf(ArchiveItem::class);
    expect($content)->toBeString();
    expect("{$output}/cover.jpeg")->toBeReadableFile();

    expect($paths)->toBeArray();
    expect($paths)->toHaveCount(9);

    $select = [$files[0], $files[1]];
    $paths = $archive->extract($output, $select);
    expect($paths)->toBeArray();
    expect($paths)->toHaveCount(2);
});
