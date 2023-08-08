<?php

namespace Kiwilan\Archive\Models;

use DateTime;
use Kiwilan\Archive\Readers\BaseArchive;

class ArchiveItem
{
    public function __construct(
        protected ?string $id = null,
        protected ?string $archivePath = null,

        protected ?string $filename = null,
        protected ?string $extension = null,
        protected ?string $path = null,
        protected ?string $rootPath = null,

        protected ?string $sizeHuman = null,
        protected ?int $size = null,
        protected ?int $packedSize = null,

        protected bool $isDirectory = false,
        protected bool $isImage = false,
        protected bool $isHidden = false,

        protected ?DateTime $modified = null,
        protected ?DateTime $created = null,
        protected ?DateTime $accessed = null,

        protected array $extraInfos = [],

        protected ?string $hostOS = null,
    ) {
    }

    public static function fromP7zip(array $data, string $archivePath = null): self
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

        return new self(
            id: $id,
            archivePath: $archivePath,

            filename: $filename,
            extension: $extension,
            path: $path,
            rootPath: $path,

            sizeHuman: BaseArchive::bytesToHuman($size),
            size: $size,
            packedSize: $packedSize,

            isDirectory: ! $isFile,
            isImage: BaseArchive::fileIsImage($extension),
            isHidden: BaseArchive::fileIsHidden($filename),

            modified: $modified,
            created: $created,
            accessed: $accessed,

            hostOS: $data['Host OS'] ?? null,
        );
    }

    /**
     * Path encoded in base64.
     */
    public function id(): ?string
    {
        return $this->id;
    }

    public function archivePath(): ?string
    {
        return $this->archivePath;
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

    public function rootPath(): ?string
    {
        return $this->rootPath;
    }

    public function sizeHuman(): ?string
    {
        return $this->sizeHuman;
    }

    public function size(): ?int
    {
        return $this->size;
    }

    public function packedSize(): ?int
    {
        return $this->packedSize;
    }

    public function isDirectory(): bool
    {
        return $this->isDirectory;
    }

    public function isImage(): bool
    {
        return $this->isImage;
    }

    public function isHidden(): bool
    {
        return $this->isHidden;
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

    public function extraInfos(): array
    {
        return $this->extraInfos;
    }

    public function hostOS(): ?string
    {
        return $this->hostOS;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'archivePath' => $this->archivePath(),

            'filename' => $this->filename(),
            'extension' => $this->extension(),
            'path' => $this->path(),
            'rootPath' => $this->rootPath(),

            'sizeHuman' => $this->sizeHuman(),
            'size' => $this->size(),
            'packedSize' => $this->packedSize(),

            'isDirectory' => $this->isDirectory(),
            'isImage' => $this->isImage(),
            'isHidden' => $this->isHidden(),

            'modified' => $this->modified(),
            'created' => $this->created(),
            'accessed' => $this->accessed(),

            'hostOS' => $this->hostOS(),
        ];
    }

    public function __toString(): string
    {
        return $this->path();
    }
}
