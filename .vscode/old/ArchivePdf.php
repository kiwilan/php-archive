<?php

namespace Kiwilan\Archive;

use Imagick;
use Kiwilan\Archive\Enums\ArchiveEnum;
use Kiwilan\Archive\Pdf\PdfMetadata;
use Smalot\PdfParser\Page;
use Smalot\PdfParser\Parser;
use Smalot\PdfParser\PDFObject;

class ArchivePdf
{
    protected ?ArchiveEnum $type = null;

    protected ?PdfMetadata $metadata = null;

    /** @var array<string, mixed> */
    protected array $dictionary = [];

    /** @var PDFObject[] */
    protected array $objects = [];

    /** @var Page[] */
    protected array $pages = [];

    protected string $text = '';

    protected int $count = 0;

    protected string $outputDir = 'vendor/php-archive';

    protected function __construct(
        protected string $path,
        protected string $extension,
    ) {
    }

    public static function make(string $path): self
    {
        if (! file_exists($path)) {
            throw new \Exception("File does not exist: {$path}");
        }

        $self = new self($path, pathinfo($path, PATHINFO_EXTENSION));

        if ($self->extension !== 'pdf') {
            throw new \Exception('Use `Archive` class for no PDF files.');
        }

        $parser = new Parser();
        $document = $parser->parseFile($self->path());

        $self->metadata = PdfMetadata::make($document->getDetails());
        $self->dictionary = $document->getDictionary();
        $self->objects = $document->getObjects();
        $self->pages = $document->getPages();
        $self->text = $document->getText();

        $self->count = $self->metadata->pages() ?? 0;

        return $self;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function metadata(): ?PdfMetadata
    {
        return $this->metadata;
    }

    /**
     * @return array<string, mixed>
     */
    public function dictionary(): array
    {
        return $this->dictionary;
    }

    /**
     * @return PDFObject[]
     */
    public function objects(): array
    {
        return $this->objects;
    }

    /**
     * @return Page[]
     */
    public function pages(): array
    {
        return $this->pages;
    }

    public function text(): string
    {
        return $this->text;
    }

    public function contentPage(int $index = 0, string $format = 'jpg', bool $toBase64 = false): ?string
    {
        if (! extension_loaded('Imagick')) {
            throw new \Exception("'Imagick extension: is not installed (can't get cover)'\nCheck this guide https://gist.github.com/ewilan-riviere/3f4efd752905abe24fd1cd44412d9db9");
        }

        $imagick = new Imagick();

        $imagick->setResolution(300, 300);
        $imagick->readimage("{$this->path}[{$index}]");
        $imagick->setImageFormat($format);

        $content = $imagick->getImageBlob();

        $imagick->clear();
        $imagick->destroy();

        if ($toBase64) {
            return base64_encode($content);
        }

        return $content;
    }

    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'extension' => $this->extension,
            'count' => $this->count,
            'text' => $this->text,
        ];
    }

    public function __toString(): string
    {
        return $this->path;
    }
}
