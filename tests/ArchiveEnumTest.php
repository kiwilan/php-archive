<?php

use Kiwilan\Archive\Enums\ArchiveEnum;

it('can read zip', function (string $extension) {
    $enum = ArchiveEnum::fromExtension($extension);

    expect($enum)->toBe(ArchiveEnum::zip);
})->with(['zip', 'epub']);

it('can read sevenzip', function (string $extension) {
    $enum = ArchiveEnum::fromExtension($extension);

    expect($enum)->toBe(ArchiveEnum::sevenZip);
})->with(['7z', '7zip', 'cb7']);

it('can read rar', function (string $extension) {
    $enum = ArchiveEnum::fromExtension($extension);

    expect($enum)->toBe(ArchiveEnum::rar);
})->with(['rar', 'cbr']);

it('can read tar', function (string $extension) {
    $enum = ArchiveEnum::fromExtension($extension);

    expect($enum)->toBe(ArchiveEnum::tar);
})->with(['tar', 'cbt']);

it('can read tar extended compression', function (string $extension) {
    $enum = ArchiveEnum::fromExtension($extension);

    expect($enum)->toBe(ArchiveEnum::tarExtended);
})->with(['tar.gz', 'tar.bz2', 'tar.xz', 'gz', 'bz2', 'xz', 'phar']);

it('can read pdf', function () {
    $enum = ArchiveEnum::fromExtension('pdf');

    expect($enum)->toBe(ArchiveEnum::pdf);
});
