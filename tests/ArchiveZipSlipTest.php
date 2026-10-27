<?php

use Kiwilan\Archive\Archive;

beforeEach(function () {
    recurseRmdir(outputPath());
});

it('cannot extract a zip entry outside of the extraction directory', function () {
    $path = outputPath('zip-slip', 'evil.zip');

    $zip = new ZipArchive;
    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('good.txt', 'good');
    $zip->addFromString('../escaped.txt', 'escaped');
    $zip->addFromString('sub/../../escaped2.txt', 'escaped');
    $zip->close();

    $archive = Archive::read($path);
    $output = outputPath('zip-slip-extract');

    expect(fn () => $archive->extract($output, $archive->getFileItems()))
        ->toThrow(Exception::class);

    expect(file_exists(__DIR__.'/output/escaped.txt'))->toBeFalse();
    expect(file_exists(__DIR__.'/output/escaped2.txt'))->toBeFalse();
});

it('can extract a zip entry with a non escaping dot-dot path', function () {
    $path = outputPath('zip-slip', 'inner.zip');

    $zip = new ZipArchive;
    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('sub/../inner.txt', 'inner');
    $zip->close();

    $archive = Archive::read($path);
    $paths = $archive->extract(outputPath('zip-slip-inner'), $archive->getFileItems());

    expect($paths)->toHaveCount(1);
    expect($paths[0])->toBeReadableFile();
    expect(file_get_contents($paths[0]))->toBe('inner');
});
