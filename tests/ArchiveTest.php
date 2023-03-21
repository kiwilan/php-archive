<?php

use Kiwilan\Archive\Archive;

define('PATH', __DIR__.'/media/archive.zip');

it('can make archive', function () {
    $archive = Archive::make(PATH);
    expect($archive)->toBeInstanceOf(\Kiwilan\Archive\ArchiveFile::class);
});

it('failed if file not exists', function () {
    $path = __DIR__.'/media/test.zip';
    expect(fn () => Archive::make($path))->toThrow(\Exception::class);
});

it('can read path of archive', function () {
    $archive = Archive::make(PATH);
    expect($archive->path())->toBe(PATH);
});
