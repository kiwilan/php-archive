<?php

use Kiwilan\Archive\ArchiveUtils;

it('can get extension', function () {
    $ext = ArchiveUtils::getExtension('test.zip');

    expect($ext)->toBe('zip');
});

it('can check if image', function () {
    $isImageJpg = ArchiveUtils::isImage('jpg');
    $isImagePdf = ArchiveUtils::isImage('pdf');

    expect($isImageJpg)->toBeTrue();
    expect($isImagePdf)->toBeFalse();
});

it('can check if base64', function () {
    $string = '-';
    $base64 = base64_encode($string);

    $isEmpty = ArchiveUtils::isBase64(null);
    $isNotBase64 = ArchiveUtils::isBase64($string);
    $isBase64 = ArchiveUtils::isBase64($base64);

    expect($isEmpty)->toBeFalse();
    expect($isNotBase64)->toBeFalse();
    expect($isBase64)->toBeTrue();
});

it('can save image base64', function () {
    $testPicture = file_get_contents(__DIR__.'/../tests/media/test.jpg');
    $base64 = base64_encode($testPicture);
    $isBase64 = ArchiveUtils::isBase64($base64);
    $path = __DIR__.'/output/test.jpg';
    ArchiveUtils::base64ToImage($base64, $path);

    expect($isBase64)->toBeTrue();
    expect($path)->toBeReadableFile();
});

it('can save image', function () {
    $testPicture = file_get_contents(__DIR__.'/../tests/media/test.jpg');
    $path = __DIR__.'/output/test.jpg';
    ArchiveUtils::stringToImage($testPicture, $path);

    expect($path)->toBeReadableFile();
});

it('can check if is hidden', function () {
    $isHidden = ArchiveUtils::isHidden('.test');
    $isNotHidden = ArchiveUtils::isHidden('test');

    expect($isHidden)->toBeTrue();
    expect($isNotHidden)->toBeFalse();
});

it('can convert bytes to human readable', function () {
    $bytes = 1024;
    $human = ArchiveUtils::bytesToHuman($bytes);

    expect($human)->toBe('1 KB');
});
