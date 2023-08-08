<?php

use Kiwilan\Archive\Archive;

beforeEach(function () {
    recurseRmdir(outputPath());
});

it('can read', function (string $path) {
    $archive = Archive::read($path);
    $stat = $archive->getStat();

    expect($stat->getPath())->toBeString();
    expect($stat->getDeviceNumber())->toBeInt();
    expect($stat->getInodeNumber())->toBeInt();
    expect($stat->getInodeProtectionMode())->toBeInt();
    expect($stat->getNumberOfLinks())->toBeInt();
    expect($stat->getUserId())->toBeInt();
    expect($stat->getGroupId())->toBeInt();
    expect($stat->getDeviceType())->toBeInt();
    expect($stat->getSize())->toBeInt();
    expect($stat->getLastAccessAt())->toBeInstanceOf(DateTime::class);
    expect($stat->getCreatedAt())->toBeInstanceOf(DateTime::class);
    expect($stat->getModifiedAt())->toBeInstanceOf(DateTime::class);
    expect($stat->getBlockSize())->toBeInt();
    expect($stat->getNumberOfBlocks())->toBeInt();
    if ($stat->getStatus()) {
        expect($stat->getStatus())->toBeString();
    }

    expect($stat->toArray())->toBeArray();
    expect($stat->toJson())->toBeString();
    expect($stat->__toString())->toBeString();
})->with([...ARCHIVES_ZIP, ...ARCHIVES_TAR, ...ARCHIVES_PDF, ...ARCHIVES_RAR, SEVENZIP]);
