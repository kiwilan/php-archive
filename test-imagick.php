#!/usr/bin/env php
<?php

use Kiwilan\Archive\Readers\BaseArchive;

define('PDF', __DIR__.'/tests/media/example.pdf');

var_dump('PDF: '.PDF);

function pdfImageExtract()
{
    $imagick = new Imagick;
    $imagick->setResolution(300, 300);
    $path = PDF;
    // $path = BaseArchive::pathToOsPath("{$path}[0]");
    $imagick->readimage("{$path}[0]");
    $imagick->setImageFormat('jpg');

    $content = null;
    $os = strtolower(PHP_OS_FAMILY);
    $imagick->writeImage("tests/output/test-{$os}.jpg");
    // $content = $imagick->getImageBlob();

    $imagick->clear();
    $imagick->destroy();

    return is_string($content);
}

var_dump('get pdfImageExtract');
pdfImageExtract();
var_dump('done pdfImageExtract');
