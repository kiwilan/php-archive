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
    ) {}

    public static function fromP7zip(array $data, ?string $archivePath = null): self
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
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getArchivePath(): ?string
    {
        return $this->archivePath;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getRootPath(): ?string
    {
        return $this->rootPath;
    }

    public function getSizeHuman(): ?string
    {
        return $this->sizeHuman;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getPackedSize(): ?int
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

    public function getModified(): ?DateTime
    {
        return $this->modified;
    }

    public function getCreated(): ?DateTime
    {
        return $this->created;
    }

    public function getAccessed(): ?DateTime
    {
        return $this->accessed;
    }

    public function getExtraInfos(): array
    {
        return $this->extraInfos;
    }

    public function getHostOS(): ?string
    {
        return $this->hostOS;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'archivePath' => $this->getArchivePath(),

            'filename' => $this->getFilename(),
            'extension' => $this->getExtension(),
            'path' => $this->getPath(),
            'rootPath' => $this->getRootPath(),

            'sizeHuman' => $this->getSizeHuman(),
            'size' => $this->getSize(),
            'packedSize' => $this->getPackedSize(),

            'isDirectory' => $this->isDirectory(),
            'isImage' => $this->isImage(),
            'isHidden' => $this->isHidden(),

            'modified' => $this->getModified(),
            'created' => $this->getCreated(),
            'accessed' => $this->getAccessed(),

            'hostOS' => $this->getHostOS(),
        ];
    }

    public function __toString(): string
    {
        return $this->getPath();
    }
}
