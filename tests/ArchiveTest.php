<?php

use Kiwilan\Archive\Archive;
use Kiwilan\Archive\ArchiveItem;
use Kiwilan\Archive\Enums\ArchiveEnum;

// it('can read', function (string $path) {
//     $archive = Archive::make($path);
//     $files = $archive->files();
//     $extension = pathinfo($path, PATHINFO_EXTENSION);

//     expect($archive->os())->toBe(PHP_OS_FAMILY);
//     expect($archive->extension())->toBe($extension);
//     expect($archive->path())->toBe($path);
//     expect($archive->type())->toBeInstanceOf(ArchiveEnum::class);
//     expect($files)->toBeIterable();
//     expect($archive->count())->toBeGreaterThanOrEqual(4);
// })->with(ARCHIVES);

// it('can failed', function () {
//     expect(fn () => Archive::make(FAILED))->toThrow(\Exception::class);
// });

// it('can failed on pdf', function () {
//     expect(fn () => Archive::make(__DIR__.'/media/example.pdf'))->toThrow(\Exception::class);
// });

// it('can check macos', function () {
//     $archive = Archive::make(ZIP);
//     expect($archive->isDarwin())->toBeBool();
//     expect($archive->isDarwin())->toBeTrue();
// })->skip(PHP_OS_FAMILY !== 'Darwin', 'Only for macOS');

// it('can get content', function (string $path) {
//     $archive = Archive::make($path);
//     $content = $archive->contentFile('archive/cover.jpeg');

//     expect($content)->toBeString();
// })->with(ARCHIVES);

// it('can get content with base64', function (string $path) {
//     $archive = Archive::make($path);
//     $content = $archive->contentFile('archive/cover.jpeg', true);
//     $isBase64 = isBase64($content);

//     expect($isBase64)->toBeTrue();
// })->with(ARCHIVES);

// it('can get content failed', function (string $path) {
//     $archive = Archive::make($path);

//     expect(fn () => $archive->contentFile('archive/cover'))->toThrow(\Exception::class);
// })->with(ARCHIVES);

// it('can find files', function (string $path) {
//     $archive = Archive::make($path);
//     $files = $archive->findAll('jpeg');

//     expect($files)->toBeIterable();
// })->with(ARCHIVES);

// it('can find file', function (string $path) {
//     $archive = Archive::make($path);
//     $file = $archive->find('jpeg');

//     expect($file)->toBeInstanceOf(ArchiveItem::class);
// })->with(ARCHIVES);

// it('can find and get content specific file', function (string $path) {
//     $archive = Archive::make($path);
//     $file = $archive->find('metadata.xml');
//     $content = $archive->contentFile($file->path());

//     expect($file)->toBeInstanceOf(ArchiveItem::class);
//     expect($content)->toBeString();
// })->with(ARCHIVES);

// it('can extract all', function (string $path) {
//     $archive = Archive::make($path);
//     $output = outputPath();
//     $archive->extractTo($output);

//     expect($output)->toBeReadableDirectory();
//     expect("{$output}/archive/cover.jpeg")->toBeFile();
//     recurseRmdir($output);
// })->with(ARCHIVES);

// it('can failed extract all', function () {
//     $archive = Archive::make(ZIP);
//     $output = outputPathFake();

//     expect(fn () => $archive->extractTo($output))->toThrow(\Exception::class);
// });
