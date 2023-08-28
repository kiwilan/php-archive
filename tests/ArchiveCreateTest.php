<?php

use Kiwilan\Archive\Archive;
use Kiwilan\Archive\ArchiveFile;

beforeEach(function () {
    recurseRmdir(outputPath());
});

it('can create', function () {
    $path = outputPath(filename: 'test.zip');
    $medias = [
        'archive/cover.jpeg' => mediaPath('archive/cover.jpeg'),
        'archive/file-1.md' => mediaPath('archive/file-1.md'),
        'archive/file-2.md' => mediaPath('archive/file-2.md'),
        'archive/file-3.md' => mediaPath('archive/file-3.md'),
        'archive/metadata.xml' => mediaPath('archive/metadata.xml'),
    ];
    $archive = Archive::make($path);
    foreach ($medias as $output => $media) {
        $archive->addFile($output, $media);
    }
    $archive->save();

    expect($archive->getPath())->toBe($path);
    expect($archive->getName())->toBe('test.zip');
    expect($archive->getPath())->toBeReadableFile($path);
    expect($archive->getCount())->toBe(5);
    expect($archive->getFiles())->toBeArray()
        ->each(fn ($file) => expect($file->value)->toBeInstanceOf(ArchiveFile::class));
});

it('can create with files', function () {
    $path = outputPath(filename: 'test.zip');
    $files = [
        'archive/cover.jpeg' => mediaPath('archive/cover.jpeg'),
        'archive/file-1.md' => mediaPath('archive/file-1.md'),
        'archive/file-2.md' => mediaPath('archive/file-2.md'),
        'archive/file-3.md' => mediaPath('archive/file-3.md'),
        'archive/metadata.xml' => mediaPath('archive/metadata.xml'),
    ];

    $archive = Archive::make($path);
    foreach ($files as $path => $file) {
        $archive->addFile($path, $file);
    }
    $archive->addFromString('test.txt', 'Hello World!');
    $archive->save();

    expect($archive->getPath())->toBeReadableFile($path);
    expect($archive->getCount())->toBe(6);
});

it('can create with strings', function () {
    $path = outputPath(filename: 'test.zip');

    $archive = Archive::make($path);
    $archive->addFromString('test.txt', 'Hello World!');
    $archive->addFromString('test-2.txt', 'Hello World!');
    $archive->save();

    expect($archive->getPath())->toBeReadableFile($path);
    expect($archive->getCount())->toBe(2);
});

it('can create with directory', function () {
    $path = outputPath(filename: 'test.zip');

    $archive = Archive::make($path);
    $archive->addDirectory('./archive', mediaPath('archive'));
    $archive->addFromString('test.txt', 'Hello World!');
    $archive->save();

    expect($archive->getPath())->toBeReadableFile($path);
    expect($archive->getCount())->toBe(6);
});

it('can edit', function () {
    $path = outputPath(filename: 'test.zip');

    $archive = Archive::make($path);
    $archive->addDirectory('./archive', mediaPath('archive'));
    $archive->save();

    $archive = Archive::make($path);
    $archive->addFromString('test.txt', 'Hello World!');
    $archive->save();

    expect($archive->getPath())->toBeReadableFile($path);
    expect($archive->getCount())->toBe(6);
});
