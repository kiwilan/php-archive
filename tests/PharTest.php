<?php

use Kiwilan\Archive\Archive;
use Kiwilan\Archive\ArchiveItem;
use Kiwilan\Archive\Enums\ArchiveEnum;

it('can read', function (string $path) {
    $archive = Archive::make($path);
    $extension = pathinfo($path, PATHINFO_EXTENSION);

    expect($archive->extension())->toBe($extension);
    expect($archive->path())->toBe($path);
    expect($archive->type())->toBe(ArchiveEnum::phar);
    expect($archive->files())->toBeArray();
    expect($archive->count())->toBe(7);
})->with(ARCHIVES_TAR);

it('can extract', function (string $path) {
    $archive = Archive::make($path);

    $output = outputPath($archive->basename());
    $archive->extractAll($output);

    expect("{$output}/archive/cover.jpeg")->toBeReadableFile();
})->with(ARCHIVES_TAR);

it('can get content file', function (string $path) {
    $archive = Archive::make($path);
    $content = $archive->content($archive->files()[0]);

    expect($content)->toBeString();
})->with(ARCHIVES_TAR);

it('can get cover', function (string $path) {
    $archive = Archive::make($path);
    $cover = $archive->find('cover.jpeg');
    $content = $archive->content($cover);
    $output = outputPath(filename: 'cover.jpeg');
    stringToImage($content, $output);

    expect($cover)->toBeInstanceOf(ArchiveItem::class);
    expect($content)->toBeString();
    expect($output)->toBeReadableFile();
})->with(ARCHIVES_TAR);

it('can extract selected files', function (string $path) {
    $archive = Archive::make($path);
    $files = $archive->files();
    $select = [$files[0], $files[1], $files[2]];

    $output = outputPath($archive->basename());
    $paths = $archive->extract($output, $select);

    expect($paths)->toBeArray();
    expect($paths)->toHaveCount(3);
    expect($paths[0])->toBeString();
    expect($paths[0])->toBeReadableFile();
})->with(ARCHIVES_TAR);
