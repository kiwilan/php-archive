<?php

use Kiwilan\Archive\Archive;

beforeEach(function () {
    recurseRmdir(outputPath());
});

it('can create', function () {
    $path = outputPath(filename: 'test.zip');
    $medias = [
        mediaPath('archive/cover.jpeg'),
        mediaPath('archive/file-1.md'),
        mediaPath('archive/file-2.md'),
        mediaPath('archive/file-3.md'),
        mediaPath('archive/metadata.xml'),
    ];
    $archive = Archive::make($path);
    $archive->addFiles($medias);
    $archive->save();

    expect($archive->path())->toBe($path);
    expect($archive->name())->toBe('test.zip');
    expect($archive->path())->toBeReadableFile($path);
    expect($archive->count())->toBe(5);
    expect($archive->files())->toBeArray()
        ->each(fn ($file) => expect($file->value)->toBeInstanceOf(SplFileInfo::class));
});

it('can create with files', function () {
    $path = outputPath(filename: 'test.zip');

    $archive = Archive::make($path);
    $archive->addFiles([
        mediaPath('archive/cover.jpeg'),
        mediaPath('archive/file-1.md'),
        mediaPath('archive/file-2.md'),
        mediaPath('archive/file-3.md'),
        mediaPath('archive/metadata.xml'),
    ]);
    $archive->addFromString('test.txt', 'Hello World!');
    $archive->save();

    expect($archive->path())->toBeReadableFile($path);
    expect($archive->count())->toBe(6);
});

it('can create with strings', function () {
    $path = outputPath(filename: 'test.zip');

    $archive = Archive::make($path);
    $archive->addFromString('test.txt', 'Hello World!');
    $archive->addFromString('test-2.txt', 'Hello World!');
    $archive->save();

    expect($archive->path())->toBeReadableFile($path);
    expect($archive->count())->toBe(2);
});

it('can create with directory', function () {
    $path = outputPath(filename: 'test.zip');

    $archive = Archive::make($path);
    $archive->addDirectory(mediaPath('archive'));
    $archive->save();

    expect($archive->path())->toBeReadableFile($path);
    expect($archive->count())->toBe(5);
});

it('can edit', function () {
    $path = outputPath(filename: 'test.zip');

    $archive = Archive::make($path);
    $archive->addDirectory(mediaPath('archive'));
    $archive->save();

    $archive = Archive::make($path);
    $archive->addFromString('test.txt', 'Hello World!');
    $archive->save();

    expect($archive->path())->toBeReadableFile($path);
    expect($archive->count())->toBe(6);
});
