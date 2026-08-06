<?php

declare(strict_types=1);

namespace Moudarir\MimeType\Exceptions;

use Exception;

class MimeTypeException extends Exception
{

    public static function fileNotExistOrUnreadable(string $filepath): static
    {
        return new static("File `$filepath` does not exist or is not readable.");
    }

    public static function unableOpenFile(string $filepath): static
    {
        return new static("Unable to open file `$filepath`.");
    }

    public static function unableReadFileHeader(string $filepath): static
    {
        return new static("Unable to read file header `$filepath`.");
    }
}
