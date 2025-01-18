<?php

namespace Kiwilan\Archive\Readers;

use Closure;
use DateTime;
use Kiwilan\Archive\Models\ArchiveItem;
use Kiwilan\Archive\Models\ArchiveStat;
use RarArchive;
use RarEntry;

class ArchiveRar extends BaseArchive
{
    public static function read(string $path, ?string $password = null): BaseArchive
    {
        $self = new self;
        if ($password) {
            $self->password = $password;
        }

        if (! BaseArchive::extensionRarTest(false)) {
            BaseArchive::binaryP7zipTest();

            $self = ArchiveSevenZip::read($path, $password);

            return $self;
        }

        $self->setup($path);
        $self->parse();

        return $self;
    }

    public function extractAll(string $toPath): array
    {
        return $this->extract($toPath, $this->files);
    }

    public function extract(string $toPath, array $files): array
    {
        $paths = [];
        $this->parser(function (ArchiveItem $file, $stream) use ($files, $toPath, &$paths) {
            if (in_array($file, $files)) {
                $toPathFile = "{$toPath}{$file->getRootPath()}";

                if (! is_dir(dirname($toPathFile))) {
                    mkdir(dirname($toPathFile), 0755, true);
                }

                $paths[] = $toPathFile;
                file_put_contents($toPathFile, $this->convertStream($stream));
            }
        });

        return $paths;
    }

    /**
     * @deprecated Use `getContents()` instead
     */
    public function getContent(?ArchiveItem $item, bool $toBase64 = false): ?string
    {
        return $this->getContents($item, $toBase64);
    }

    public function getContents(?ArchiveItem $item, bool $toBase64 = false): ?string
    {
        if (! $item) {
            return null;
        }

        $content = $this->parser(function (ArchiveItem $file, $stream) use ($item) {
            if ($file->getRootPath() === $item->getRootPath()) {
                return $this->convertStream($stream);
            }
        });

        return $toBase64
            ? base64_encode($content)
            : $content;
    }

    public function getText(ArchiveItem $file): ?string
    {
        if ($file->isImage()) {
            throw new \Exception("Error, {$file->getFilename()} is an image");
        }

        return $this->getContents($file);
    }

    private function parse(): static
    {
        $this->extensionRarTest();

        $archive = RarArchive::open($this->path, $this->password);
        $this->stat = ArchiveStat::make($this->path);
        $this->stat->setComment($archive->getComment());
        $archive->close();

        $this->parser(function (ArchiveItem $file) {
            $this->files[] = $file;
        });

        $this->sortFiles();
        $this->count = count($this->files);

        return $this;
    }

    /**
     * @param  Closure(ArchiveItem $file, resource $stream): mixed  $closure
     */
    private function parser(Closure $closure): mixed
    {
        $archive = RarArchive::open($this->getPath(), $this->password);

        if ($archive->isBroken()) {
            throw new \Exception("Archive is broken {$this->getPath()}");
        }

        foreach ($archive->getEntries() as $key => $entry) {
            $item = $this->createArchiveItem($entry);
            if ($item->isDirectory()) {
                continue;
            }

            $res = $closure($item, $entry->getStream());
            if ($res) {
                $archive->close();

                return $res;
            }
        }

        $archive->close();

        return null;
    }

    private function createArchiveItem(RarEntry $entry): ArchiveItem
    {
        $name = $entry->getName();
        $size = $entry->getUnpackedSize();
        $packedSize = $entry->getPackedSize();
        $dateTime = $entry->getFileTime();
        $isDirectory = $entry->isDirectory();

        if ($dateTime) {
            $dateTime = new DateTime($dateTime);
        }

        $item = new ArchiveItem(
            id: base64_encode($name),
            archivePath: $this->path,

            filename: pathinfo($name, PATHINFO_BASENAME),
            extension: pathinfo($name, PATHINFO_EXTENSION),
            path: $name,
            rootPath: $name,

            sizeHuman: BaseArchive::bytesToHuman($size),
            size: $size,
            packedSize: $packedSize,

            isDirectory: $isDirectory,
            isImage: BaseArchive::fileIsImage(pathinfo($name, PATHINFO_EXTENSION)),
            isHidden: BaseArchive::fileIsHidden($name),

            modified: $dateTime,
            created: null,
            accessed: null,

            extraInfos: [
                'attr' => $entry->getAttr(),
                'crc' => $entry->getCrc(),
                'method' => $entry->getMethod(),
                'version' => $entry->getVersion(),
                'isEncrypted' => $entry->isEncrypted(),
            ],

            hostOS: "{$entry->getHostOS()}",
        );

        return $item;
    }
}
