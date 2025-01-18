<?php

namespace Kiwilan\Archive\Processes;

use Exception;
use Kiwilan\Archive\ArchiveTemporaryDirectory;
use Kiwilan\Archive\Models\ArchiveItem;
use Kiwilan\Archive\Readers\BaseArchive;

class SevenZipProcess
{
    protected ?string $outputDir = null;

    protected bool $isRar = false;

    protected bool $isDarwin = false;

    protected function __construct(
        protected string $path,
        protected ?string $password = null,
        protected ?string $binaryPath = null,
    ) {}

    public static function make(string $path, ?string $password = null, ?string $binaryPath = null): self
    {
        if (! file_exists($path)) {
            throw new Exception("File does not exist: {$path}");
        }

        $self = new self($path, $password, $binaryPath);
        $temp = ArchiveTemporaryDirectory::make();
        $self->outputDir = $temp->path();
        $self->isDarwin = PHP_OS_FAMILY === 'Darwin';

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $rarExts = ['rar', 'cbr'];
        $self->isRar = in_array($ext, $rarExts);

        return $self;
    }

    public static function test(bool $exception = true): bool
    {
        exec('7z', $output, $res);

        $isValid = $res === 0;

        // check if 7z is installed
        if (! $isValid) {
            if ($exception) {
                $osFamily = PHP_OS_FAMILY;
                $isDarwin = $osFamily === 'Darwin';
                $message = "p7zip is not installed or not in the PATH. Please install p7zip and try again.\nYou can check this guide: https://gist.github.com/ewilan-riviere/85d657f9283fa6af255531d97da5d71d";

                if ($isDarwin) {
                    $message .= "\nYou have to install `rar` binary with brew on macOS.";
                }

                throw new Exception($message);
            }

            return false;
        }

        return true;
    }

    /**
     * Escapes a string to be used as a shell argument.
     */
    private function escapeArgument(?string $argument): string
    {
        if ($argument === '' || $argument === null) {
            return '""';
        }
        if ('\\' !== \DIRECTORY_SEPARATOR) {
            return "'".str_replace("'", "'\\''", $argument)."'";
        }
        if (str_contains($argument, "\0")) {
            $argument = str_replace("\0", '?', $argument);
        }
        if (! preg_match('/[()%!^"<>&|\s]/', $argument)) {
            return $argument;
        }
        $argument = preg_replace('/(\\\\+)$/', '$1$1', $argument);

        return '"'.str_replace(['"', '^', '%', '!', "\n"], ['""', '"^^"', '"^%"', '"^!"', '!LF!'], $argument).'"';
    }

    /**
     * @param  string[]  $args
     * @return string[]
     */
    public function execute(string $command, array $args): array
    {
        SevenZipProcess::test();

        if ($this->password) {
            $args = ['-p'.$this->password, ...$args];
        }

        if ($this->binaryPath) {
            $command = $this->binaryPath;
        }

        $command = "{$command} ".implode(' ', array_map($this->escapeArgument(...), $args));

        try {
            exec($command, $output, $res);
        } catch (\Throwable $th) {
            throw new \Error($th->getMessage());
        }

        array_unshift($output, '');

        return $output;
    }

    /**
     * @return ArchiveItem[]
     */
    public function list(): array
    {
        $output = $this->execute('7z', ['l', '-ba', '-slt', $this->path]);

        $temp = [];
        foreach ($output as $string) {
            if (empty($string)) {
                $id = uniqid();
                $temp[$id] = [];
            }

            if (! empty($string) && isset($id)) {
                $temp[$id][] = $string;
            }
        }

        $files = [];
        foreach ($temp as $sublist) {
            if (! empty($sublist)) {
                $files[] = $sublist;
            }
        }

        /** @var ArchiveItem[] $items */
        $items = [];

        foreach ($files as $file) {
            $item = [];
            foreach ($file as $attr) {
                $data = explode(' = ', $attr);
                $key = array_key_exists(0, $data) ? $data[0] : null;
                $value = array_key_exists(1, $data) ? $data[1] : null;

                if ($key) {
                    $key = trim($key);
                }

                if ($value) {
                    $value = trim($value);
                }

                $item[$key] = $value;
            }

            $item = ArchiveItem::fromP7zip($item, $this->path);
            $items[] = $item;
        }

        $items = array_filter($items, fn (ArchiveItem $item) => ! $item->isDirectory());
        $items = array_filter($items, fn (ArchiveItem $item) => ! $item->isHidden());

        return $items;
    }

    /**
     * @param  ArchiveItem[]  $files
     */
    public function extract(string $toPath, ?array $files = null): bool
    {
        if ($this->isRar && $this->isDarwin) {
            if ($files) {
                $list = array_map(fn (ArchiveItem $item) => $item->getPath(), $files);
                $this->execute('rar', ['x', '-y', $this->path, ...$list, "{$toPath}/"]);
            } else {
                $this->execute('rar', ['x', '-y', $this->path, "{$toPath}"]);
            }
        } else {
            if ($files) {
                $list = array_map(fn (ArchiveItem $item) => $item->getPath(), $files);
                $this->execute('7z', ['x', '-y', $this->path, "-o{$toPath}", ...$list, '-r']);
            } else {
                $this->execute('7z', ['x', '-y', $this->path, "-o{$toPath}", '-r']);
            }
        }

        return true;
    }

    public function content(ArchiveItem $file)
    {
        $archive = pathinfo($this->path, PATHINFO_BASENAME);
        $output = "{$this->outputDir}/{$archive}";
        $filePath = "{$output}/{$file->getRootPath()}";
        $filePath = BaseArchive::pathToOsPath($filePath);

        if (! file_exists($filePath)) {
            mkdir($output, 0755, true);
        }

        if ($this->isRar && $this->isDarwin) {
            $this->execute('rar', ['x', '-y', $this->path, $file->getPath(), "{$output}/"]);
        } else {
            $this->execute('7z', ['x', '-y', $this->path, "-o{$output}", $file->getPath(), '-r']);
        }
        $content = file_get_contents($filePath);
        BaseArchive::recurseRmdir($this->outputDir);

        return $content;
    }
}
