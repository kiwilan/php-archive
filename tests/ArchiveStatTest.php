<?php

use Kiwilan\Archive\Archive;

beforeEach(function () {
    recurseRmdir(outputPath());
});

it('can read', function (string $path) {
    $archive = Archive::read($path);
    $stat = $archive->stat();

    expect($stat->path())->toBeString();
    expect($stat->deviceNumber())->toBeInt();
    expect($stat->inodeNumber())->toBeInt();
    expect($stat->inodeProtectionMode())->toBeInt();
    expect($stat->numberOfLinks())->toBeInt();
    expect($stat->userId())->toBeInt();
    expect($stat->groupId())->toBeInt();
    expect($stat->deviceType())->toBeInt();
    expect($stat->size())->toBeInt();
    expect($stat->lastAccessAt())->toBeInstanceOf(DateTime::class);
    expect($stat->createdAt())->toBeInstanceOf(DateTime::class);
    expect($stat->modifiedAt())->toBeInstanceOf(DateTime::class);
    expect($stat->blockSize())->toBeInt();
    expect($stat->numberOfBlocks())->toBeInt();
    if ($stat->status()) {
        expect($stat->status())->toBeString();
    }

    expect($stat->toArray())->toBeArray();
    expect($stat->toJson())->toBeString();
    expect($stat->__toString())->toBeString();
})->with([...ARCHIVES_ZIP, ...ARCHIVES_TAR, ...ARCHIVES_PDF, ...ARCHIVES_RAR, SEVENZIP]);
