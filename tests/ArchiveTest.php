<?php

use Kiwilan\Archive\Archive;
use Kiwilan\Archive\ArchiveItem;
use Kiwilan\Archive\Enums\ArchiveEnum;

beforeEach(function () {
    recurseRmdir(outputPath());
});

it('can read', function (string $path) {
    $archive = Archive::make($path);
    $extension = pathinfo($path, PATHINFO_EXTENSION);
    $type = ArchiveEnum::fromExtension($extension, mime_content_type($path));

    expect($archive->extension())->toBe($extension);
    expect($archive->path())->toBe($path);
    expect($archive->type())->toBe($type);
})->with([...ARCHIVES_NATIVE, EPUB]);

it('can failed if not found', function () {
    expect(fn () => Archive::make(FAILED))->toThrow(\Exception::class);
});

it('can get files', function (string $path) {
    $archive = Archive::make($path);
    $files = $archive->files();

    expect($files)->toBeArray();
    expect($files)->toHaveCount($archive->count());
})->with([...ARCHIVES_NATIVE, EPUB]);

it('can find all images', function (string $path) {
    $archive = Archive::make($path);
    $files = $archive->findAll('jpeg');

    expect($files)->toBeArray();
    expect($files)->each(
        function (Pest\Expectation $item) {
            $file = $item->value;
            expect($file)->toBeInstanceOf(ArchiveItem::class);
            expect($file->extension())->toBe('jpeg');
        }
    );
})->with([...ARCHIVES_NATIVE, EPUB]);

it('can get cover', function (string $path) {
    $archive = Archive::make($path);
    $cover = $archive->find('cover.jpeg');
    $content = $archive->content($cover);

    $output = outputPath();
    $coverPath = "{$output}cover.jpeg";
    stringToImage($content, $coverPath);

    expect($cover)->toBeInstanceOf(ArchiveItem::class);
    expect($content)->toBeString();
    expect($coverPath)->toBeReadableFile();
})->with([...ARCHIVES_NATIVE, EPUB]);

it('can get content with base64', function (string $path) {
    $archive = Archive::make($path);
    $cover = $archive->find('cover.jpeg');
    $content = $archive->content($cover, true);
    $isBase64 = isBase64($content);

    expect($isBase64)->toBeTrue();
})->with([...ARCHIVES_NATIVE, EPUB]);

it('can extract some files', function (string $path) {
    $archive = Archive::make($path);
    $files = $archive->files();
    $output = outputPath($archive->basename());

    $select = [$files[0], $files[1]];
    $paths = $archive->extract($output, $select);

    expect($paths)->toBeArray();
    expect($paths)->toHaveCount(2);
    expect($paths[0])->toBeString();
    expect($paths[0])->toBeReadableFile();
})->with([...ARCHIVES_NATIVE, EPUB]);

it('can extract files', function (string $path) {
    $archive = Archive::make($path);
    $paths = $archive->extractAll(outputPath());

    expect($paths)->toBeArray();
    expect($paths)->toBeGreaterThanOrEqual(5);
})->with([...ARCHIVES_NATIVE, EPUB]);

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

// it('can find files', function (string $path) {
//     $archive = Archive::make($path);
//     $files = $archive->findAll('jpeg');

//     expect($files)->toBeIterable();
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
