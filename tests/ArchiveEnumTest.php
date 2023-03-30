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

    expect($enum)->toBe(ArchiveEnum::phar);
})->with(['tar', 'cbt']);

it('can read phar', function (string $extension) {
    $enum = ArchiveEnum::fromExtension($extension);

    expect($enum)->toBe(ArchiveEnum::phar);
})->with(['tar.gz', 'tar.bz2', 'tar.xz', 'gz', 'bz2', 'xz', 'phar']);

it('can read pdf', function (string $extension) {
    $enum = ArchiveEnum::fromExtension($extension);

    expect($enum)->toBe(ArchiveEnum::pdf);
})->with(['pdf']);

it('can read others', function () {
    expect(ArchiveEnum::fromExtension('', 'zip'))->toBe(ArchiveEnum::zip);
    expect(ArchiveEnum::fromExtension('', 'rar'))->toBe(ArchiveEnum::rar);
    expect(ArchiveEnum::fromExtension('', 'pdf'))->toBe(ArchiveEnum::pdf);
});

it('can failed', function () {
    expect(fn () => ArchiveEnum::fromExtension('jpg'))->toThrow(\Exception::class);
});
