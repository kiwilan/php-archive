<?php

// it('can check if image', function () {
//     $isImageJpg = isImage('jpg');
//     $isImagePdf = isImage('pdf');

//     expect($isImageJpg)->toBeTrue();
//     expect($isImagePdf)->toBeFalse();
// });

// it('can check if base64', function () {
//     $string = '-';
//     $base64 = base64_encode($string);

//     $isEmpty = isBase64(null);
//     $isNotBase64 = isBase64($string);
//     $isBase64 = isBase64($base64);

//     expect($isEmpty)->toBeFalse();
//     expect($isNotBase64)->toBeFalse();
//     expect($isBase64)->toBeTrue();
// });

// it('can save image base64', function () {
//     $testPicture = file_get_contents(__DIR__.'/../tests/media/test.jpg');
//     $base64 = base64_encode($testPicture);
//     $isBase64 = isBase64($base64);
//     $path = __DIR__.'/output/test.jpg';
//     base64ToImage($base64, $path);

//     expect($isBase64)->toBeTrue();
//     expect($path)->toBeReadableFile();
// });

// it('can failed save image base64', function () {
//     $path = __DIR__.'/output/test.jpg';
//     $res = base64ToImage(null, $path);

//     expect($res)->toBeFalse();
// });

// it('can save image', function () {
//     $testPicture = file_get_contents(__DIR__.'/../tests/media/test.jpg');
//     $path = __DIR__.'/output/test.jpg';
//     stringToImage($testPicture, $path);

//     expect($path)->toBeReadableFile();
// });

// it('can failed save image', function () {
//     $path = __DIR__.'/output/test.jpg';
//     $res = stringToImage(null, $path);

//     expect($res)->toBeFalse();
// });
