<?php

namespace Kiwilan\Archive\Readers;

use Imagick;
use Kiwilan\Archive\Models\ArchiveItem;
use Kiwilan\Archive\Models\ArchiveMetadata;
use Smalot\PdfParser\Page;
use Smalot\PdfParser\Parser;

class ArchivePdf extends BaseArchive
{
    protected string $pdfExt = 'jpg';

    public static function read(string $path): self
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
        foreach ($this->files as $file) {
            if (in_array($file, $files)) {
                $content = $this->content($file);
                $toPathFile = "{$toPath}{$file->path()}.{$this->pdfExt}";

                if (! is_dir(dirname($toPathFile))) {
                    mkdir(dirname($toPathFile), 0755, true);
                }

                $paths[] = $toPathFile;
                file_put_contents($toPathFile, $content);
            }
        }

        return $paths;
    }

    public function content(ArchiveItem $file, bool $toBase64 = false): ?string
    {
        $this->extensionImagickTest();

        $index = (int) $file->path();
        $format = $this->pdfExt;

        $imagick = new Imagick();

        $imagick->setResolution(600, 600);
        $imagick->readimage("{$this->path}[{$index}]");
        $imagick->setImageFormat($format);

        $content = $imagick->getImageBlob();

        $imagick->clear();
        $imagick->destroy();

        return $toBase64
            ? base64_encode($content)
            : $content;
    }

    public function text(ArchiveItem $file): ?string
    {
        if ($file->isImage()) {
            throw new \Exception("Error, {$file->filename()} is an image");
        }

        $index = (int) $file->path();

        $parser = new Parser();
        $document = $parser->parseFile($this->path());

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
        $parser = new Parser();
        $document = $parser->parseFile($this->path());

        $this->metadata = ArchiveMetadata::fromPdf($document->getDetails());
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
