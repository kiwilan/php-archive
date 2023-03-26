<?php

use Kiwilan\Archive\ArchiveItem;
use Kiwilan\Archive\BaseArchive;

it('can create ArchiveItem', function () {
    $item = ArchiveItem::fromP7zip([
        'Id' => base64_encode('archive/metadata.xml'),
        'Path' => 'archive/metadata.xml',
        'Folder' => '-',
        'Size' => '313',
        'Packed Size' => '199',
        'Modified' => '2023-03-21 15:30:52',
        'Created' => '',
        'Accessed' => '2023-03-21 15:30:52',
        'Attributes' => '_ -rw-r--r--',
        'Encrypted' => '-',
        'Comment' => '',
        'CRC' => '89F4DD2B',
        'Method' => 'Deflate',
        'Characteristics' => 'UT 0x7875',
        'Host OS' => 'Unix',
        'Version Index' => '20',
        'Volume' => '0',
        'Offset' => '116929',
    ]);

    expect($item)->toBeInstanceOf(ArchiveItem::class);
});

it('can create ArchiveItem without id', function () {
    $item = ArchiveItem::fromP7zip([
        'Path' => 'archive/metadata.xml',
        'Size' => '313',
        'Packed Size' => '199',
        'Modified' => '2023-03-21 15:30:52',
        'Created' => '',
        'Accessed' => '2023-03-21 15:30:52',
        'Host OS' => 'Unix',
    ]);

    expect($item)->toBeInstanceOf(ArchiveItem::class);
});

it('can create ArchiveItem with path', function () {
    $item = ArchiveItem::fromP7zip([
        'Path' => 'archive/metadata.xml',
    ]);

    expect($item)->toBeInstanceOf(ArchiveItem::class);
});

it('can failed ArchiveItem if no data', function () {
    expect(fn () => ArchiveItem::fromP7zip([]))->toThrow(\Exception::class);
});

it('can failed ArchiveItem if no data path', function () {
    expect(fn () => ArchiveItem::fromP7zip([
        'Path' => null,
    ]))->toThrow(\Exception::class);
});

it('can read attributes', function () {
    $item = ArchiveItem::fromP7zip([
        'Id' => base64_encode('archive/metadata.xml'),
        'Path' => 'archive/metadata.xml',
        'Folder' => '-',
        'Size' => '313',
        'Packed Size' => '199',
        'Modified' => '2023-03-21 15:30:52',
        'Created' => '',
        'Accessed' => '2023-03-21 15:30:52',
        'Attributes' => '_ -rw-r--r--',
        'Encrypted' => '-',
        'Comment' => '',
        'CRC' => '89F4DD2B',
        'Method' => 'Deflate',
        'Characteristics' => 'UT 0x7875',
        'Host OS' => 'Unix',
        'Version Index' => '20',
        'Volume' => '0',
        'Offset' => '116929',
    ]);

    $modified = new DateTime('2023-03-21 15:30:52');
    $accessed = new DateTime('2023-03-21 15:30:52');
    $modifiedFormat = $modified->format('Y-m-d H:i:s');
    $accessedFormat = $accessed->format('Y-m-d H:i:s');

    expect($item->id())->toBe(base64_encode('archive/metadata.xml'));
    expect($item->filename())->toBe('metadata.xml');
    expect($item->extension())->toBe('xml');
    expect($item->path())->toBe('archive/metadata.xml');
    expect($item->isDirectory())->toBe(false);
    expect($item->isHidden())->toBe(false);
    expect($item->sizeHuman())->toBe(BaseArchive::bytesToHuman(313));
    // expect($item->folder())->toBe('-');
    expect($item->size())->toBe(313);
    expect($item->packedSize())->toBe(199);
    expect($item->modified()->format('Y-m-d H:i:s'))->toBe($modifiedFormat);
    expect($item->created())->toBeNull();
    expect($item->accessed()->format('Y-m-d H:i:s'))->toBe($accessedFormat);
    // expect($item->attributes())->toBe('_ -rw-r--r--');
    // expect($item->encrypted())->toBe('-');
    // expect($item->comment())->toBe('');
    // expect($item->crc())->toBe('89F4DD2B');
    // expect($item->method())->toBe('Deflate');
    // expect($item->characteristics())->toBe('UT 0x7875');
    expect($item->hostOS())->toBe('Unix');
    // expect($item->versionIndex())->toBe(20);
    // expect($item->volume())->toBe(0);
    // expect($item->offset())->toBe(116929);
});

it('can be print as array', function () {
    $item = ArchiveItem::fromP7zip([
        'Id' => base64_encode('archive/metadata.xml'),
        'Path' => 'archive/metadata.xml',
        'Folder' => '-',
        'Size' => '313',
        'Packed Size' => '199',
    ]);

    expect($item->toArray())->toBe([
        'id' => base64_encode('archive/metadata.xml'),
        'archivePath' => null,
        'filename' => 'metadata.xml',
        'extension' => 'xml',
        'path' => 'archive/metadata.xml',
        'rootPath' => 'archive/metadata.xml',
        'sizeHuman' => '313 B',
        'size' => 313,
        'packedSize' => 199,
        'isDirectory' => false,
        'isImage' => false,
        'isHidden' => false,
        'modified' => null,
        'created' => null,
        'accessed' => null,
        'hostOS' => null,
    ]);
});

it('can print as string', function () {
    $item = ArchiveItem::fromP7zip([
        'Id' => base64_encode('archive/metadata.xml'),
        'Path' => 'archive/metadata.xml',
        'Folder' => '-',
        'Size' => '313',
        'Packed Size' => '199',
    ]);

    expect($item->__toString())->toBe('archive/metadata.xml');
});
