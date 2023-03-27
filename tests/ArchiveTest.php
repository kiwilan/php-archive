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
})->with([...ARCHIVES_NATIVE, EPUB, CBZ, PDF]);

it('can get text', function (string $path) {
    $archive = Archive::make($path);
    $files = $archive->files();
    $first = array_filter($files, fn (ArchiveItem $item) => ! $item->isImage());
    $first = array_shift($first);
    $text = $archive->text($first);

    expect($text)->toBeString();
})->with([...ARCHIVES_NATIVE, EPUB, CBZ, PDF]);

it('can failed if not found', function () {
    expect(fn () => Archive::make(FAILED))->toThrow(\Exception::class);
});

it('can get files', function (string $path) {
    $archive = Archive::make($path);
    $files = $archive->files();

    expect($files)->toBeArray();
    expect($files)->toHaveCount($archive->count());
})->with([...ARCHIVES_NATIVE, EPUB, CBZ]);

it('can find all images', function (string $path) {
    $archive = Archive::make($path);
    $ext = 'jpeg';
    $files = $archive->findAll($ext);
    if ($archive->extension() === 'cbz') {
        $ext = 'jpg';
        $files = $archive->findAll($ext);
    }

    expect($files)->toBeArray();
    expect($files)->each(
        function (Pest\Expectation $item) use ($ext) {
            $file = $item->value;
            expect($file)->toBeInstanceOf(ArchiveItem::class);
            expect($file->extension())->toBe($ext);
        }
    );
})->with([...ARCHIVES_NATIVE, EPUB, CBZ]);

it('can get content first file', function (string $path) {
    $archive = Archive::make($path);
    $content = $archive->content($archive->first());

    $output = outputPath();
    $file = "{$output}first.jpg";
    stringToImage($content, $file);

    expect($content)->toBeString();
    expect($file)->toBeReadableFile();
})->with([...ARCHIVES_NATIVE, EPUB, CBZ]);

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

it('can cover with base64', function (string $path) {
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
})->with([...ARCHIVES_NATIVE, EPUB, CBZ]);

it('can extract files', function (string $path) {
    $archive = Archive::make($path);
    $paths = $archive->extractAll(outputPath());

    expect($paths)->toBeArray();
    expect($paths)->toBeGreaterThanOrEqual(5);
})->with([...ARCHIVES_NATIVE, EPUB, CBZ]);
