<?php

namespace Kiwilan\Archive;

use DateTime;

class ArchiveItem
{
    protected function __construct(
        protected ?string $id = null,
        protected ?string $archive = null,
        protected ?string $filename = null,
        protected ?string $extension = null,
        protected ?string $path = null,
        protected bool $isDirectory = false,
        protected bool $isHidden = false,
        protected ?string $fileSize = null,
        protected ?string $folder = null,
        protected ?int $size = null,
        protected ?int $packedSize = null,
        protected ?DateTime $modified = null,
        protected ?DateTime $created = null,
        protected ?DateTime $accessed = null,
        protected ?string $attributes = null,
        protected ?string $encrypted = null,
        protected ?string $comment = null,
        protected ?string $crc = null,
        protected ?string $method = null,
        protected ?string $characteristics = null,
        protected ?string $hostOS = null,
        protected ?int $versionIndex = null,
        protected ?int $volume = null,
        protected ?int $offset = null,
    ) {
    }

    public static function make(array $data, ?string $archive = null): self
    {
        if (empty($data)) {
            throw new \Exception('No data provided.');
        }

        if (! $data['Path']) {
            throw new \Exception('No path provided.');
        }

        $path = $data['Path'];
        $isFile = pathinfo($path, PATHINFO_EXTENSION) !== '';
        $id = array_key_exists('Id', $data)
            ? $data['Id']
            : base64_encode($path);
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if ($isFile) {
            $filename = "{$filename}.{$extension}";
        }
        $size = $data['Size'] ?? null;
        $fileSize = ArchiveItem::bytesToHuman($size);
        $packedSize = array_key_exists('Packed Size', $data) && ! empty($data['Packed Size'])
            ? (int) $data['Packed Size']
            : null;

        $modified = array_key_exists('Modified', $data) && ! empty($data['Modified'])
            ? new DateTime($data['Modified'])
            : null;
        $created = array_key_exists('Created', $data) && ! empty($data['Created'])
            ? new DateTime($data['Created'])
            : null;
        $accessed = array_key_exists('Accessed', $data) && ! empty($data['Accessed'])
            ? new DateTime($data['Accessed'])
            : null;

        $isHidden = str_starts_with($filename, '.') || str_contains($path, 'PaxHeader');
        $offset = $data['Offset'] ?? null;

        return new self(
            id: $id,
            archive: $archive,
            filename: $filename,
            extension: $extension,
            isDirectory: ! $isFile,
            isHidden: $isHidden,
            path: $path,
            fileSize: $fileSize,
            folder: $data['Folder'] ?? null,
            size: $size,
            packedSize: $packedSize,
            modified: $modified,
            created: $created,
            accessed: $accessed,
            attributes: $data['Attributes'] ?? null,
            encrypted: $data['Encrypted'] ?? null,
            comment: $data['Comment'] ?? null,
            crc: $data['CRC'] ?? null,
            method: $data['Method'] ?? null,
            characteristics: $data['Characteristics'] ?? null,
            hostOS: $data['Host OS'] ?? null,
            versionIndex: $data['Version Index'] ?? null,
            volume: $data['Volume'] ?? null,
            offset: $offset,
        );
    }

    /**
     * Path encoded in base64.
     */
    public function id(): ?string
    {
        return $this->id;
    }

    public function archive(): ?string
    {
        return $this->archive;
    }

    public function filename(): ?string
    {
        return $this->filename;
    }

    public function extension(): ?string
    {
        return $this->extension;
    }

    public function path(): ?string
    {
        return $this->path;
    }

    public function isDirectory(): bool
    {
        return $this->isDirectory;
    }

    public function isHidden(): bool
    {
        return $this->isHidden;
    }

    public function fileSize(): ?string
    {
        return $this->fileSize;
    }

    public function folder(): ?string
    {
        return $this->folder;
    }

    public function size(): ?int
    {
        return $this->size;
    }

    public function packedSize(): ?int
    {
        return $this->packedSize;
    }

    public function modified(): ?DateTime
    {
        return $this->modified;
    }

    public function created(): ?DateTime
    {
        return $this->created;
    }

    public function accessed(): ?DateTime
    {
        return $this->accessed;
    }

    public function attributes(): ?string
    {
        return $this->attributes;
    }

    public function encrypted(): ?string
    {
        return $this->encrypted;
    }

    public function comment(): ?string
    {
        return $this->comment;
    }

    public function crc(): ?string
    {
        return $this->crc;
    }

    public function method(): ?string
    {
        return $this->method;
    }

    public function characteristics(): ?string
    {
        return $this->characteristics;
    }

    public function hostOS(): ?string
    {
        return $this->hostOS;
    }

    public function versionIndex(): ?int
    {
        return $this->versionIndex;
    }

    public function volume(): ?int
    {
        return $this->volume;
    }

    public function offset(): ?int
    {
        return $this->offset;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'archive' => $this->archive(),
            'filename' => $this->filename(),
            'extension' => $this->extension(),
            'path' => $this->path(),
            'isDirectory' => $this->isDirectory(),
            'isHidden' => $this->isHidden(),
            'fileSize' => $this->fileSize(),
            'folder' => $this->folder(),
            'size' => $this->size(),
            'packedSize' => $this->packedSize(),
            'modified' => $this->modified(),
            'created' => $this->created(),
            'accessed' => $this->accessed(),
            'attributes' => $this->attributes(),
            'encrypted' => $this->encrypted(),
            'comment' => $this->comment(),
            'crc' => $this->crc(),
            'method' => $this->method(),
            'characteristics' => $this->characteristics(),
            'hostOS' => $this->hostOS(),
            'versionIndex' => $this->versionIndex(),
            'volume' => $this->volume(),
            'offset' => $this->offset(),
        ];
    }

    public function __toString(): string
    {
        return $this->path();
    }

    public static function bytesToHuman(mixed $bytes): ?string
    {
        if (empty($bytes)) {
            return null;
        }

        if (gettype($bytes) !== 'integer' && gettype($bytes) !== 'double' && gettype($bytes) !== 'float') {
            $bytes = intval($bytes);
        }

        $size = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $floor = floor(log($bytes, 1024));
        $format = $size[$floor];

        $round = round($bytes / pow(1024, $floor), 2);

        return "{$round} {$format}";
    }
}
