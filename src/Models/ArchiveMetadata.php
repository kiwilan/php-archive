<?php

namespace Kiwilan\Archive\Models;

use DateTime;

class ArchiveMetadata
{
    /** @var array<string> */
    protected array $keywords = [];

    public function __construct(
        protected ?string $title = null,
        protected ?string $author = null,
        protected ?string $subject = null,
        protected ?string $creator = null,
        protected ?DateTime $creationDate = null,
        protected ?DateTime $modDate = null,
        protected ?string $status = null,
        protected ?string $comment = null,
    ) {
    }

    public static function fromPdf(array $details): self
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
        );

        if (is_array($keywords)) {
            $keywords = implode(',', $keywords);
        }
        $self->keywords = explode(',', $keywords);

        $self->keywords = array_map(fn ($keyword) => trim($keyword), $self->keywords);
        $self->keywords = array_filter($self->keywords);
        $self->keywords = array_unique($self->keywords);

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

    public function status(): ?string
    {
        return $this->status;
    }

    public function comment(): ?string
    {
        return $this->comment;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'author' => $this->author,
            'subject' => $this->subject,
            'keywords' => $this->keywords,
            'creator' => $this->creator,
            'creationDate' => $this->creationDate,
            'modDate' => $this->modDate,
            'status' => $this->status,
            'comment' => $this->comment,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    public function __toString(): string
    {
        return $this->toJson();
    }
}
