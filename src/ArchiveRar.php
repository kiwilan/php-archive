<?php

namespace Kiwilan\Archive;

use Closure;
use DateTime;
use RarArchive;
use RarEntry;

class ArchiveRar extends BaseArchive
{
    protected function __construct(
    ) {
    }

    public static function make(string $path): self
    {
        $self = new self();
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
                $toPathFile = "{$toPath}{$file->rootPath()}";

                if (! is_dir(dirname($toPathFile))) {
                    mkdir(dirname($toPathFile), 0755, true);
                }

                $paths[] = $toPathFile;
                file_put_contents($toPathFile, $this->convertStream($stream));
            }
        });

        return $paths;
    }

    public function content(ArchiveItem $item, bool $toBase64 = false): ?string
    {
        $content = $this->parser(function (ArchiveItem $file, $stream) use ($item) {
            if ($file->rootPath() === $item->rootPath()) {
                return $this->convertStream($stream);
            }
        });

        return $toBase64
            ? base64_encode($content)
            : $content;
    }

    public function text(ArchiveItem $file): ?string
    {
        if ($file->isImage()) {
            throw new \Exception("Error, {$file->filename()} is an image");
        }

        return $this->content($file);
    }

    private function parse(): static
    {
        if (! extension_loaded('rar')) {
            throw new \Exception('rar extension: is not installed, check this guide https://gist.github.com/ewilan-riviere/3f4efd752905abe24fd1cd44412d9db9');
        }

        $archive = RarArchive::open($this->path());
        $this->metadata = new ArchiveMetadata(
            comment: $archive->getComment()
        );
        $archive->close();

        $this->parser(function (ArchiveItem $file) use (&$i) {
            $this->files[] = $file;
        });

        $this->count = count($this->files);

        return $this;
    }

    /**
     * @param Closure(ArchiveItem $file, resource $stream): mixed $closure
     */
    private function parser(Closure $closure): mixed
    {
        $archive = RarArchive::open($this->path());

        if ($archive->isBroken()) {
            throw new \Exception("Archive is broken {$this->path()}");
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
            isImage: BaseArchive::fileIsImage($name),
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

            hostOS: $entry->getHostOS(),
        );

        return $item;
    }
}
