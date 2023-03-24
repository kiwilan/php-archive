<?php

use Kiwilan\Archive\Archive;
use Kiwilan\Archive\ArchiveUtils;
use Kiwilan\Archive\Readers\ReaderFile;
use Kiwilan\Archive\SevenZipItem;

define('FAILED', __DIR__.'/media/test.zip');
define('SEVENZIP', __DIR__.'/media/archive.7z');
define('RAR', __DIR__.'/media/archive.rar');
define('TAR', __DIR__.'/media/archive.tar');
define('TARBZ2', __DIR__.'/media/archive.tar.bz2');
define('TARGZ', __DIR__.'/media/archive.tar.gz');
define('TARXZ', __DIR__.'/media/archive.tar.xz');
define('ZIP', __DIR__.'/media/archive.zip');
define('ARCHIVES', [
    'SEVENZIP' => SEVENZIP,
    'RAR' => RAR,
    'TAR' => TAR,
    'TARBZ2' => TARBZ2,
    'TARGZ' => TARGZ,
    'TARXZ' => TARXZ,
    'ZIP' => ZIP,
]);

it('can read', function () {
    foreach (ARCHIVES as $name => $path) {
        $archive = Archive::make($path);
        $files = $archive->files();

        expect($files)->toBeIterable();
        expect($archive->count())->toBeGreaterThan(0);
    }
});

it('can extract', function () {
    foreach (ARCHIVES as $name => $path) {
        $archive = Archive::make($path);
        $content = $archive->extractFile('archive/cover.jpeg');

        expect($content)->toBeString();
    }
});

it('can extract with base64', function () {
    foreach (ARCHIVES as $name => $path) {
        $archive = Archive::make($path);
        $content = $archive->extractFile('archive/cover.jpeg', true);
        $isBase64 = ArchiveUtils::isBase64($content);

        expect($isBase64)->toBeTrue();
    }
});

it('can extract failed', function () {
    foreach (ARCHIVES as $name => $path) {
        $archive = Archive::make($path);

        expect(fn () => $archive->extractFile('archive/cover'))->toThrow(\Exception::class);
    }
});

it('can find files', function () {
    foreach (ARCHIVES as $name => $path) {
        $archive = Archive::make($path);
        $files = $archive->findAll('jpeg');

        expect($files)->toBeIterable();
    }
});

it('can find file', function () {
    foreach (ARCHIVES as $name => $path) {
        $archive = Archive::make($path);
        $file = $archive->find('jpeg');

        expect($file)->toBeInstanceOf(SevenZipItem::class);
    }
});

it('can find and extract specific file', function () {
    $archive = Archive::make(ZIP);
    $file = $archive->find('metadata.xml');
    $content = $archive->extractFile($file->path());

    expect($file)->toBeInstanceOf(SevenZipItem::class);
    expect($content)->toBeString();
});

// it('can read archive', function () {
//     foreach (ARCHIVES as $name => $path) {
//         $archive = Archive::make($path);

//         expect($archive->path())->toBe($path);
//         expect($archive->extension())->toBe(ArchiveUtils::getExtension($path));
//         // expect($archive->type()->value)->toBe();
//         expect($archive->count())->toBeGreaterThan(0);
//     }
// });

// it('can read archive files', function () {
//     foreach (ARCHIVES as $path) {
//         $archive = Archive::make($path);

//         expect($archive->files())->toBeIterable();
//     }
// });

// it('can get archive file content', function () {
//     foreach (ARCHIVES as $path) {
//         $archive = Archive::make($path);

//         $file = $archive->parse(function (ReaderFile $file) {
//             if (str_contains($file->name(), 'cover')) {
//                 return $file->content();
//             }
//         });

//         expect($file)->toBeString();
//     }
// });

// it('can extract archive file', function () {
//     foreach (ARCHIVES as $path) {
//         $archive = Archive::make($path);

//         /** @var ReaderFile */
//         $file = $archive->parse(function (ReaderFile $file) {
//             if ($file->isImage() && $file->name() === 'archive/cover.jpeg') {
//                 return $file;
//             }
//         });

//         $content = $file->content();
//         $extension = $file->extension();

//         $path = __DIR__.'/output/cover.'.$extension;
//         if (file_exists($path)) {
//             unlink($path);
//         }
//         ArchiveUtils::base64ToImage($content, $path);
//         expect(ArchiveUtils::isBase64($content))->toBeTrue();
//         expect($path)->toBeReadableFile();
//     }
// });

// it('can extract archive files', function () {
//     foreach (ARCHIVES as $path) {
//         $archive = Archive::make($path);

//         $cover = null;
//         $extension = null;
//         $archive->parse(function (ReaderFile $file) use (&$cover, &$extension) {
//             if ($file->isImage() && $file->name() === 'archive/cover.jpeg') {
//                 $cover = $file->content();
//                 $extension = $file->extension();
//             }
//         });

//         $path = __DIR__.'/output/cover.'.$extension;
//         ArchiveUtils::base64ToImage($cover, $path);
//         expect(ArchiveUtils::isBase64($cover))->toBeTrue();
//         expect($path)->toBeReadableFile();
//     }
// });

// it('can get content after parse', function () {
//     foreach (ARCHIVES as $path) {
//         $archive = Archive::make($path);

//         $cover = null;
//         foreach ($archive->files() as $name => $file) {
//             if ($file->isImage() && $file->name() === 'archive/cover.jpeg') {
//                 $cover = $file->content();
//             }
//         }

//         expect(ArchiveUtils::isBase64($cover))->toBeTrue();
//     }
// });
