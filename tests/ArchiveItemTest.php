<?php

use Kiwilan\Archive\ArchiveItem;
use Kiwilan\Archive\ArchiveUtils;

it('can create ArchiveItem', function () {
    $item = ArchiveItem::make([
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
    $item = ArchiveItem::make([
        'Path' => 'archive/metadata.xml',
        'Folder' => '-',
        'Size' => '313',
        'Packed Size' => '199',
        'Modified' => '2023-03-21 15:30:52',
        'Created' => '2023-03-21 15:30:52',
        'Accessed' => '',
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

it('can create ArchiveItem with path', function () {
    $item = ArchiveItem::make([
        'Path' => 'archive/metadata.xml',
    ]);

    expect($item)->toBeInstanceOf(ArchiveItem::class);
});

it('can failed ArchiveItem if no data', function () {
    expect(fn () => ArchiveItem::make([]))->toThrow(\Exception::class);
});

it('can failed ArchiveItem if no data path', function () {
    expect(fn () => ArchiveItem::make([
        'Path' => null,
    ]))->toThrow(\Exception::class);
});

it('can read attributes', function () {
    $item = ArchiveItem::make([
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
    expect($item->fileSize())->toBe(ArchiveUtils::bytesToHuman(313));
    expect($item->folder())->toBe('-');
    expect($item->size())->toBe(313);
    expect($item->packedSize())->toBe(199);
    expect($item->modified()->format('Y-m-d H:i:s'))->toBe($modifiedFormat);
    expect($item->created())->toBeNull();
    expect($item->accessed()->format('Y-m-d H:i:s'))->toBe($accessedFormat);
    expect($item->attributes())->toBe('_ -rw-r--r--');
    expect($item->encrypted())->toBe('-');
    expect($item->comment())->toBe('');
    expect($item->crc())->toBe('89F4DD2B');
    expect($item->method())->toBe('Deflate');
    expect($item->characteristics())->toBe('UT 0x7875');
    expect($item->hostOS())->toBe('Unix');
    expect($item->versionIndex())->toBe(20);
    expect($item->volume())->toBe(0);
    expect($item->offset())->toBe(116929);
});

it('can be print as array', function () {
    $item = ArchiveItem::make([
        'Id' => base64_encode('archive/metadata.xml'),
        'Path' => 'archive/metadata.xml',
        'Folder' => '-',
        'Size' => '313',
        'Packed Size' => '199',
    ]);

    expect($item->toArray())->toBe([
        'id' => base64_encode('archive/metadata.xml'),
        'archive' => null,
        'filename' => 'metadata.xml',
        'extension' => 'xml',
        'path' => 'archive/metadata.xml',
        'isDirectory' => false,
        'isHidden' => false,
        'fileSize' => '313 B',
        'folder' => '-',
        'size' => 313,
        'packedSize' => 199,
        'modified' => null,
        'created' => null,
        'accessed' => null,
        'attributes' => null,
        'encrypted' => null,
        'comment' => null,
        'crc' => null,
        'method' => null,
        'characteristics' => null,
        'hostOS' => null,
        'versionIndex' => null,
        'volume' => null,
        'offset' => null,
    ]);
});

it('can print as string', function () {
    $item = ArchiveItem::make([
        'Id' => base64_encode('archive/metadata.xml'),
        'Path' => 'archive/metadata.xml',
        'Folder' => '-',
        'Size' => '313',
        'Packed Size' => '199',
    ]);

    expect($item->__toString())->toBe('archive/metadata.xml');
});
