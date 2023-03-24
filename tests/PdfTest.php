<?php

use Kiwilan\Archive\Archive;
use Kiwilan\Archive\ArchivePdf;
use Kiwilan\Archive\ArchiveUtils;

define('PDF', __DIR__.'/media/example.pdf');

it('can read pdf', function () {
    $archive = ArchivePdf::make(PDF);

    expect($archive->metadata())->toBeInstanceOf(\Kiwilan\Archive\Utils\PdfMetadata::class);
    expect($archive->dictionary())->toBeArray();
    expect($archive->objects())->toBeArray();
    expect($archive->pages())->toBeArray();
    expect($archive->text())->toBeString();
    expect($archive->count())->toBeGreaterThan(0);
});

it('can failed with archive', function () {
    expect(fn () => Archive::make(PDF))->toThrow(\Exception::class);
});

it('can failed', function () {
    expect(fn () => ArchivePdf::make('path/to/pdf'))->toThrow(\Exception::class);
});

it('can failed with zip', function () {
    expect(fn () => ArchivePdf::make(ZIP))->toThrow(\Exception::class);
});

it('can extract pdf cover', function () {
    $archive = ArchivePdf::make(PDF);
    $content = $archive->extractPage(index: 0, isBase64: false);
    $path = 'tests/output/cover-PDF.jpg';

    ArchiveUtils::stringToImage($content, $path);

    expect($content)->toBeString();
    expect($path)->toBeReadableFile();

    $content = $archive->extractPage(index: 0);
    $path = 'tests/output/coverBase64-PDF.jpg';
    $isBase64 = ArchiveUtils::isBase64($content);
    ArchiveUtils::base64ToImage($content, $path);

    expect($isBase64)->toBeTrue();
    expect($path)->toBeReadableFile();
})->skip(PHP_OS_FAMILY === 'Windows', 'Not supported on Windows');

it('can read pdf metadata', function () {
    $archive = ArchivePdf::make(PDF);
    $metadata = $archive->metadata();

    expect($metadata->title())->toBeString();
    expect($metadata->author())->toBeString();
    expect($metadata->subject())->toBeString();
    expect($metadata->keywords())->toBeArray();
    expect($metadata->creator())->toBeString();
    expect($metadata->creationDate())->toBeInstanceOf(\DateTime::class);
    expect($metadata->modDate())->toBeInstanceOf(\DateTime::class);
});
