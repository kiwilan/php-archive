<?php

namespace Kiwilan\Archive\Parsers;

use Closure;
use Kiwilan\Archive\ArchiveFile;
use Kiwilan\Archive\Readers\ReaderRarFile;
use Phar;
use PharData;
use RarArchive;
use RecursiveIteratorIterator;

class ParserTar extends ParserArchive
{
    protected $fh;

    protected function __construct(
    ) {
    }

    public static function make(ArchiveFile $file): self
    {
        $self = new self();

        $archive = new PharData($file->path(), 0, null, Phar::TAR);
        dump($archive->getChildren());

        // $files = $archive->buildFromIterator(new RecursiveIteratorIterator($archive), $file->path());
        // dump($archive->getSize());
        // dump($files);
        // foreach ($archive as $file) {
        //     echo "$file\n";
        // }
        // $phar->extractTo('./tests/output');

        // $phar->

        // $archive = new PharData($file->path());

        // dump($file->path());
        // $p = new PharData($file->path(), 0);
        // // Phar extends SPL's DirectoryIterator class
        // foreach (new RecursiveIteratorIterator($p) as $file) {
        //     // $file is a PharFileInfo class, and inherits from SplFileInfo
        //     echo $file->getFileName()."\n";
        //     echo file_get_contents($file->getPathName())."\n"; // display contents;
        // }

        return $self;
    }

    public function parse(Closure $closure): mixed
    {
        // $result = [];

        // foreach ($this->yieldContents() as $fileinfo) {
        //     $result[] = $fileinfo;
        // }

        // return $result;

        return null;
    }

    protected function open(): void
    {
    }

    protected function close(): void
    {
    }

    public function opened($file)
    {
        $this->file = $file;

        // update compression to mach file
        // if ($this->comptype == Tar::COMPRESS_AUTO) {
        //     $this->setCompression($this->complevel, $this->filetype($file));
        // }

        // open file handles
        // if ($this->comptype === Archive::COMPRESS_GZIP) {
        //     $this->fh = @gzopen($this->file, 'rb');
        // } elseif ($this->comptype === Archive::COMPRESS_BZIP) {
        //     $this->fh = @bzopen($this->file, 'r');
        // } else {
        //     $this->fh = @fopen($this->file, 'rb');
        // }
        $this->fh = @fopen($this->file, 'rb');

        if (! $this->fh) {
            throw new \Exception('Unable to open file');
        }
        $this->closed = false;
    }

    protected function readbytes($length)
    {
        if ($this->comptype === Archive::COMPRESS_GZIP) {
            return @gzread($this->fh, $length);
        } elseif ($this->comptype === Archive::COMPRESS_BZIP) {
            return @bzread($this->fh, $length);
        } else {
            return @fread($this->fh, $length);
        }
    }

    public function yieldContents()
    {
        if ($this->closed || ! $this->file) {
            throw new \Exception('File is not opened');
        }

        while ($read = $this->readbytes(512)) {
            // $header = $this->parseHeader($read);
            // if (! is_array($header)) {
            //     continue;
            // }

            // $this->skipbytes(ceil($header['size'] / 512) * 512);
            // yield $this->header2fileinfo($header);
        }

        $this->close();
    }
    // public function open()
    // {
    //     $this->archive = RarArchive::open($this->file->path());
    // }

    // public function close()
    // {
    //     $this->archive->close();
    // }

    // public function parse(Closure $closure): mixed
    // {
    //     $this->open();

    //     if ($this->archive->isBroken()) {
    //         throw new \Exception("Archive is broken {$this->file->path()}");
    //     }

    //     foreach ($this->archive->getEntries() as $key => $entry) {
    //         $reader = ReaderRarFile::make($entry);
    //         if ($reader->isDirectory()) {
    //             continue;
    //         }

    //         $res = $closure($reader);
    //         if ($res) {
    //             $this->close();

    //             return $res;
    //         }
    //     }

    //     $this->close();

    //     return null;
    // }
}
