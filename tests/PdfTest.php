<?php

use Kiwilan\Archive\Archive;

beforeEach(function () {
    recurseRmdir(outputPath());
});

it('can get files', function () {
    $archive = Archive::read(PDF);
    $files = $archive->getFiles();

    expect($files)->toBeArray();
    expect($files)->toHaveCount($archive->getCount());
});

it('can get content first file', function () {
    $archive = Archive::read(PDF);
    $content = $archive->getContent($archive->getFirst());

    $output = outputPath();
    $file = "{$output}first.jpg";
    stringToImage($content, $file);

    expect($content)->toBeString();
    expect($file)->toBeReadableFile();
})->skip(PHP_OS_FAMILY === 'Windows', 'Not supported on Windows');

it('can extract some files', function () {
    $archive = Archive::read(PDF);
    $files = $archive->getFiles();
    $output = outputPath($archive->getBasename());

    $select = [$files[0], $files[1]];
    $paths = $archive->extract($output, $select);

    expect($paths)->toBeArray();
    expect($paths)->toHaveCount(2);
    expect($paths[0])->toBeString();
    expect($paths[0])->toBeReadableFile();
})->skip(PHP_OS_FAMILY === 'Windows', 'Not supported on Windows');

it('can extract files', function () {
    $archive = Archive::read(PDF);
    $paths = $archive->extractAll(outputPath());

    expect($paths)->toBeArray();
    expect($paths)->toBeGreaterThanOrEqual(5);
})->skip(PHP_OS_FAMILY === 'Windows', 'Not supported on Windows');

it('can read metadata', function () {
    $archive = Archive::read(PDF);
    $pdf = $archive->getPdf();

    expect($pdf->getTitle())->toBe('Example PDF');
    expect($pdf->getAuthor())->toBe('Ewilan Rivière');
    expect($pdf->getSubject())->toBe('This is an example PDF for php-archive package tests.');
    expect($pdf->getKeywords())->toBe(['test', 'pdf', 'example']);
    expect($pdf->getCreator())->toBe('Kiwilan');
    expect($pdf->getCreationDate())->toBeInstanceOf(\DateTime::class);
    expect($pdf->getModDate())->toBeInstanceOf(\DateTime::class);
    expect($pdf->getPages())->toBe(4);
    expect($pdf->getKeywords())->toBeArray();
    expect($pdf->toArray())->toBeArray();
    expect($pdf->toJson())->toBeString();
});
