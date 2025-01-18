<?php

namespace Kiwilan\Archive\Readers;

use Imagick;
use Kiwilan\Archive\Models\ArchiveItem;
use Kiwilan\Archive\Models\ArchiveStat;
use Kiwilan\Archive\Models\PdfMeta;
use Smalot\PdfParser\Page;
use Smalot\PdfParser\Parser;

class ArchivePdf extends BaseArchive
{
    protected string $pdfExt = 'jpg';

    public static function read(string $path, ?string $password = null): self
    {
        $self = new self;
        if ($password) {
            $self->password = $password;
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
        foreach ($this->files as $file) {
            if (in_array($file, $files)) {
                $content = $this->getContents($file);
                $toPathFile = "{$toPath}{$file->getPath()}.{$this->pdfExt}";

                if (! is_dir(dirname($toPathFile))) {
                    mkdir(dirname($toPathFile), 0755, true);
                }

                $paths[] = $toPathFile;
                file_put_contents($toPathFile, $content);
            }
        }

        return $paths;
    }

    /**
     * @deprecated Use `getContents()` instead
     */
    public function getContent(?ArchiveItem $file, bool $toBase64 = false): ?string
    {
        return $this->getContents($file, $toBase64);
    }

    public function getContents(?ArchiveItem $file, bool $toBase64 = false): ?string
    {
        if (! $file) {
            return null;
        }

        $this->extensionImagickTest();

        $index = (int) $file->getPath();
        $format = $this->pdfExt;
        $format = 'jpg';

        $content = null;
        try {
            $imagick = new Imagick;

            $imagick->setResolution(300, 300);
            $path = BaseArchive::pathToOsPath("{$this->path}[{$index}]");
            $imagick->readimage($path);
            $imagick->setImageFormat($format);

            $content = $imagick->getImageBlob();

            $imagick->clear();
            $imagick->destroy();
        } catch (\Throwable $th) {
            error_log("Error, {$file->getFilename()} Failed to extract page: {$th->getMessage()}");
        }

        if (! $content) {
            return null;
        }

        return $toBase64
            ? base64_encode($content)
            : $content;
    }

    public function getText(ArchiveItem $file): ?string
    {
        if ($file->isImage()) {
            throw new \Exception("Error, {$file->getFilename()} is an image");
        }

        $index = (int) $file->getPath();

        $parser = new Parser;
        $document = $parser->parseFile($this->getPath());

        $pages = $document->getPages();

        $page = array_filter($pages, fn ($page) => $page->getPageNumber() === $index);
        if ($page) {
            $page = array_shift($page);

            return $page->getText();
        }

        return null;
    }

    private function parse(): static
    {
        $parser = new Parser;
        $document = $parser->parseFile($this->getPath());

        $this->stat = ArchiveStat::make($this->path);
        $this->pdf = PdfMeta::make($document->getDetails());

        // $dictionary = $document->getDictionary();
        // $objects = $document->getObjects();
        $pages = $document->getPages();

        foreach ($pages as $page) {
            $this->files[] = $this->createArchiveItem($page);
        }

        $this->sortFiles();
        $this->count = $document->getDetails()['Pages'] ?? 0;

        return $this;
    }

    private function createArchiveItem(Page $page): ArchiveItem
    {
        $name = "page_{$page->getPageNumber()}";

        $item = new ArchiveItem(
            id: base64_encode($name),
            archivePath: $this->path,

            filename: $name,
            extension: 'pdf',
            path: "{$page->getPageNumber()}",
            rootPath: null,

            sizeHuman: null,
            size: null,
            packedSize: null,

            isDirectory: false,
            isImage: false,
            isHidden: false,

            modified: null,
            created: null,
            accessed: null,

            hostOS: PHP_OS_FAMILY,
        );

        return $item;
    }
}
