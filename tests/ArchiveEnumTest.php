<?php

use Kiwilan\Archive\Enums\ArchiveEnum;

it('can read zip', function () {
    $enum = ArchiveEnum::fromExtension('zip');

    expect($enum)->toBe(ArchiveEnum::zip);
});

it('can read sevenzip', function () {
    $enum = ArchiveEnum::fromExtension('7z');

    expect($enum)->toBe(ArchiveEnum::sevenZip);
});

it('can read rar', function () {
    $enum = ArchiveEnum::fromExtension('rar');

    expect($enum)->toBe(ArchiveEnum::rar);
});

it('can read tar', function () {
    $enum = ArchiveEnum::fromExtension('tar');

    expect($enum)->toBe(ArchiveEnum::tar);
});

it('can read tar extended compression', function (string $extension) {
    $enum = ArchiveEnum::fromExtension($extension);

    expect($enum)->toBe(ArchiveEnum::tarExtended);
})->with(['tar.gz', 'tar.bz2', 'tar.xz', 'gz', 'bz2', 'xz', 'phar']);
