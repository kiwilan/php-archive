<?php

use Kiwilan\Archive\Archive;
use Kiwilan\Archive\Enums\ArchiveEnum;
use Kiwilan\Archive\Models\ArchiveItem;
use Kiwilan\Archive\Models\ArchiveStat;
use Kiwilan\Archive\Readers\BaseArchive;

beforeEach(function () {
    recurseRmdir(outputPath());
});

it('can read', function (string $path) {
    $archive = Archive::read($path);
    $extension = pathinfo($path, PATHINFO_EXTENSION);
    $type = ArchiveEnum::fromExtension($extension, Archive::getMimeType($path));

    expect($archive->getExtension())->toBe($extension);
    expect($archive->getPath())->toBe($path);
    expect($archive->getType())->toBe($type);
})->with([...ARCHIVES_ZIP, ...ARCHIVES_TAR, ...ARCHIVES_PDF, ...ARCHIVES_RAR, SEVENZIP]);

it('can get text', function (string $path) {
    $archive = Archive::read($path);
    $files = $archive->getFileItems();
    $first = array_filter($files, fn (ArchiveItem $item) => ! $item->isImage());
    $first = array_shift($first);
    $text = $archive->getText($first);

    expect($text)->toBeString();
})->with([...ARCHIVES_ZIP, ...ARCHIVES_TAR, ...ARCHIVES_PDF, ...ARCHIVES_RAR, SEVENZIP]);

it('can failed if not found', function () {
    expect(fn () => Archive::read(FAILED))->toThrow(\Exception::class);
});

it('can get metadata', function (string $path) {
    $archive = Archive::read($path);
    $stat = $archive->getStat();

    expect($stat)->toBeInstanceOf(ArchiveStat::class);
})->with([...ARCHIVES_ZIP, ...ARCHIVES_TAR, ...ARCHIVES_PDF, ...ARCHIVES_RAR, SEVENZIP]);

it('can get files', function (string $path) {
    $archive = Archive::read($path);
    $files = $archive->getFileItems();

    expect($files)->toBeArray();
    expect($files)->toHaveCount($archive->getCount());
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
            expect($file->getExtension())->toBe($ext);
        }
    );
})->with([...ARCHIVES_ZIP, ...ARCHIVES_TAR, ...ARCHIVES_RAR, SEVENZIP]);

it('can get content first file', function (string $path) {
    $archive = Archive::read($path);
    $content = $archive->getContents($archive->getFirst());

    $output = outputPath();
    $file = BaseArchive::pathToOsPath("{$output}first.jpg");
    stringToImage($content, $file);

    expect($content)->toBeString();
    expect($file)->toBeReadableFile();

    $content = $archive->getContents($archive->getFirst());
})->with([...ARCHIVES_ZIP, ...ARCHIVES_TAR, ...ARCHIVES_RAR, SEVENZIP]);

it('can get cover', function (string $path) {
    $archive = Archive::read($path);
    $cover = $archive->find('cover.jpeg');
    $content = $archive->getContents($cover);

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
    $content = $archive->getContents($cover, true);
    $isBase64 = isBase64($content);

    expect($isBase64)->toBeTrue();
})->with([...ARCHIVES_NATIVE, EPUB, RAR, SEVENZIP]);

it('can extract some files', function (string $path) {
    $archive = Archive::read($path);
    $files = $archive->getFileItems();
    $output = outputPath($archive->getBasename());

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

it('can handle bad archive', function (string $path) {
    expect(fn () => Archive::read($path))->toThrow(ValueError::class);
})->with([EPUB_BAD_FILE]);

it('can handle archive as string', function (string $path) {
    $contents = file_get_contents($path);
    $archive = Archive::readFromString($contents);
    $files = $archive->getFileItems();

    expect($files)->toBeArray();
    expect($files)->toHaveCount($archive->getCount());
})->with([...ARCHIVES_ZIP, ...ARCHIVES_TAR, ...ARCHIVES_RAR, SEVENZIP, PDF]);

it('can handle archive with password', function (string $path) {
    $archive = Archive::read($path);
    $archive->setPassword('password');

    $files = $archive->getFileItems();
    expect($files)->toBeArray();

    $file = $archive->getFileItem('archive/file-1.md');
    $text = $archive->getText($file);
    expect($text)->toBeString();
})->with([ZIP_PASSWORD, RAR_PASSWORD, SEVENZIP_PASSWORD]);
