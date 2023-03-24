<?php

namespace Kiwilan\Archive\Utils;

use DateTime;

class PdfMetadata
{
    /** @var array<string> */
    protected array $keywords = [];

    protected function __construct(
        protected ?string $title,
        protected ?string $author,
        protected ?string $subject,
        protected ?string $creator,
        protected ?DateTime $creationDate,
        protected ?DateTime $modDate,
        protected ?int $pages,
    ) {
    }

    public static function make(array $details): self
    {
        $title = $details['Title'] ?? null;
        $author = $details['Author'] ?? null;
        $subject = $details['Subject'] ?? null;
        $keywords = $details['Keywords'] ?? null;
        $creator = $details['Creator'] ?? null;
        $creationDate = $details['CreationDate'] ?? null;
        $modDate = $details['ModDate'] ?? null;
        $pages = $details['Pages'] ?? null;

        if ($creationDate) {
            $creationDate = new DateTime($creationDate);
        }

        if ($modDate) {
            $modDate = new DateTime($modDate);
        }

        $self = new self(
            title: $title,
            author: $author,
            subject: $subject,
            creator: $creator,
            creationDate: $creationDate,
            modDate: $modDate,
            pages: $pages,
        );

        $self->keywords = explode(',', $keywords);

        return $self;
    }

    public function title(): ?string
    {
        return $this->title;
    }

    public function author(): ?string
    {
        return $this->author;
    }

    public function subject(): ?string
    {
        return $this->subject;
    }

    /**
     * @return array<string>
     */
    public function keywords(): array
    {
        return $this->keywords;
    }

    public function creator(): ?string
    {
        return $this->creator;
    }

    public function creationDate(): ?DateTime
    {
        return $this->creationDate;
    }

    public function modDate(): ?DateTime
    {
        return $this->modDate;
    }

    public function pages(): ?int
    {
        return $this->pages;
    }
}
