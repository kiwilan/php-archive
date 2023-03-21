<?php

namespace Kiwilan\Archive;

use Symfony\Component\Console\Output\ConsoleOutput;

class ArchiveUtils
{
    public static function getExtension(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    public static function print(string $message): void
    {
        $output = new ConsoleOutput();
        $output->writeln($message);
    }
}
