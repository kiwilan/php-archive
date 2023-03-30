<?php

use Kiwilan\Archive\Archive;
use Kiwilan\Archive\Enums\ArchiveEnum;
use Kiwilan\Archive\Models\ArchiveItem;
use Kiwilan\Archive\Models\ArchiveMetadata;
use Kiwilan\Archive\Readers\BaseArchive;

beforeEach(function () {
    recurseRmdir(outputPath());
});

it('can read', function (string $path) {
    $archive = Archive::read($path);
    $extension = pathinfo($path, PATHINFO_EXTENSION);
    $type = ArchiveEnum::fromExtension($extension, mime_content_type($path));

    expect($archive->extension())->toBe($extension);
    expect($archive->path())->toBe($path);
    expect($archive->type())->toBe($type);
})->with([...ARCHIVES_ZIP, ...ARCHIVES_TAR, ...ARCHIVES_PDF, ...ARCHIVES_RAR, SEVENZIP]);

it('can get text', function (string $path) {
    $archive = Archive::read($path);
    $files = $archive->files();
    $first = array_filter($files, fn (ArchiveItem $item) => ! $item->isImage());
    $first = array_shift($first);
    $text = $archive->text($first);

    expect($text)->toBeString();
})->with([...ARCHIVES_ZIP, ...ARCHIVES_TAR, ...ARCHIVES_PDF, ...ARCHIVES_RAR, SEVENZIP]);

it('can failed if not found', function () {
    expect(fn () => Archive::read(FAILED))->toThrow(\Exception::class);
});

it('can get metadata', function (string $path) {
    $archive = Archive::read($path);
    $metadata = $archive->metadata();

    expect($metadata)->toBeInstanceOf(ArchiveMetadata::class);
})->with([...ARCHIVES_ZIP, ...ARCHIVES_TAR, ...ARCHIVES_PDF, ...ARCHIVES_RAR, SEVENZIP]);

it('can get files', function (string $path) {
    $archive = Archive::read($path);
    $files = $archive->files();

    expect($files)->toBeArray();
    expect($files)->toHaveCount($archive->count());
})->with([...ARCHIVES_ZIP, ...ARCHIVES_TAR, ...ARCHIVES_PDF, ...ARCHIVES_RAR, SEVENZIP]);

it('can find all images', function (string $path) {
    $archive = Archive::read($path);
    $ext = 'jpeg';
    $files = $archive->filter($ext);
    if (empty($files)) {
        $ext = 'jpg';
        $files = $archive->filter($ext);
    }

    expect($files)->toBeArray();
    expect($files)->each(
        function (Pest\Expectation $item) use ($ext) {
            $file = $item->value;
            expect($file)->toBeInstanceOf(ArchiveItem::class);
            expect($file->extension())->toBe($ext);
        }
    );
})->with([...ARCHIVES_ZIP, ...ARCHIVES_TAR, ...ARCHIVES_RAR, SEVENZIP]);

it('can get content first file', function (string $path) {
    $archive = Archive::read($path);
    $content = $archive->content($archive->first());

    $output = outputPath();
    $file = BaseArchive::pathToOsPath("{$output}first.jpg");
    stringToImage($content, $file);

    expect($content)->toBeString();
    expect($file)->toBeReadableFile();
})->with([...ARCHIVES_ZIP, ...ARCHIVES_TAR, ...ARCHIVES_RAR, SEVENZIP]);

it('can get cover', function (string $path) {
    $archive = Archive::read($path);
    $cover = $archive->find('cover.jpeg');
    $content = $archive->content($cover);

    $output = outputPath();
    $coverPath = "{$output}cover.jpeg";
    stringToImage($content, $coverPath);

    expect($cover)->toBeInstanceOf(ArchiveItem::class);
    expect($content)->toBeString();
    expect($coverPath)->toBeReadableFile();
})->with([...ARCHIVES_NATIVE, EPUB, RAR, SEVENZIP]);

it('can cover with base64', function (string $path) {
    $archive = Archive::read($path);
    $cover = $archive->find('cover.jpeg');
    $content = $archive->content($cover, true);
    $isBase64 = isBase64($content);

    expect($isBase64)->toBeTrue();
})->with([...ARCHIVES_NATIVE, EPUB, RAR, SEVENZIP]);

it('can extract some files', function (string $path) {
    $archive = Archive::read($path);
    $files = $archive->files();
    $output = outputPath($archive->basename());

    $select = [$files[0], $files[1]];
    $paths = $archive->extract($output, $select);

    expect($paths)->toBeArray();
    expect($paths)->toHaveCount(2);
    expect($paths[0])->toBeString();
    expect($paths[0])->toBeReadableFile();
})->with([...ARCHIVES_ZIP, ...ARCHIVES_TAR, ...ARCHIVES_RAR, SEVENZIP]);

it('can extract files', function (string $path) {
    $archive = Archive::read($path);
    $paths = $archive->extractAll(outputPath());

    expect($paths)->toBeArray();
    expect($paths)->toBeGreaterThanOrEqual(5);
})->with([...ARCHIVES_ZIP, ...ARCHIVES_TAR, ...ARCHIVES_RAR, SEVENZIP]);
