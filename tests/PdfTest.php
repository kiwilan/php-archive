<?php

use Kiwilan\Archive\Archive;

it('can get files', function () {
    $archive = Archive::read(PDF);
    $files = $archive->files();

    expect($files)->toBeArray();
    expect($files)->toHaveCount($archive->count());
});

it('can get content first file', function () {
    $archive = Archive::read(PDF);
    $content = $archive->content($archive->first());

    $output = outputPath();
    $file = "{$output}first.jpg";
    stringToImage($content, $file);

    expect($content)->toBeString();
    expect($file)->toBeReadableFile();
});

it('can extract some files', function () {
    $archive = Archive::read(PDF);
    $files = $archive->files();
    $output = outputPath($archive->basename());

    $select = [$files[0], $files[1]];
    $paths = $archive->extract($output, $select);

    expect($paths)->toBeArray();
    expect($paths)->toHaveCount(2);
    expect($paths[0])->toBeString();
    expect($paths[0])->toBeReadableFile();
});

it('can extract files', function () {
    $archive = Archive::read(PDF);
    $paths = $archive->extractAll(outputPath());

    expect($paths)->toBeArray();
    expect($paths)->toBeGreaterThanOrEqual(5);
});

it('can read metadata', function () {
    $archive = Archive::read(PDF);
    $metadata = $archive->metadata();

    expect($metadata->title())->toBeString();
    expect($metadata->author())->toBeString();
    expect($metadata->subject())->toBeString();
    expect($metadata->keywords())->toBeArray();
    expect($metadata->creator())->toBeString();
    expect($metadata->creationDate())->toBeInstanceOf(\DateTime::class);
    expect($metadata->modDate())->toBeInstanceOf(\DateTime::class);
});
