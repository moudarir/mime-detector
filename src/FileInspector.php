<?php

declare(strict_types=1);

namespace Moudarir\MimeType;

use finfo;
use Moudarir\MimeType\Exceptions\MimeTypeException;

final class FileInspector
{

    private const int HEADER_SIZE = 64;

    private static ?finfo $finfo = null;

    private string $header;

    private string $hexHeader;

    /**
     * @throws MimeTypeException
     */
    public function __construct(private readonly string $path)
    {
        $this->validate();
    }

    public function path(): string
    {
        return $this->path;
    }

    /**
     * Retourne les premiers octets du fichier.
     * @throws MimeTypeException
     */
    public function header(): string
    {
        if (isset($this->header)) {
            return $this->header;
        }

        if (($handle = fopen($this->path, 'rb')) === false) {
            throw MimeTypeException::unableOpenFile($this->path);
        }

        try {
            if (($header = fread($handle, self::HEADER_SIZE)) === false) {
                throw MimeTypeException::unableReadFileHeader($this->path);
            }

            $this->header = $header;
            return $this->header;
        } finally {
            fclose($handle);
        }
    }

    /**
     * Retourne le header en représentation hexadécimale.
     * @throws MimeTypeException
     */
    public function hexHeader(): string
    {
        return $this->hexHeader ??= strtoupper(bin2hex($this->header()));
    }

    /**
     * Brut detection with libmagic.
     */
    public function fileInfoMime(): ?string
    {
        self::$finfo ??= new finfo(FILEINFO_MIME_TYPE);

        $mime = self::$finfo->file($this->path);

        return $mime !== false ? $mime : null;
    }

    /**
     * Vérifie si le header commence par une signature hexadécimale.
     * @throws MimeTypeException
     */
    public function startsWithHex(string ...$signatures): bool
    {
        return array_any($signatures, fn ($signature) => str_starts_with($this->hexHeader(), strtoupper($signature)));

    }

    /**
     * Return the heder brut part.
     * @throws MimeTypeException
     */
    public function bytes(int $offset, int $length): string
    {
        return substr($this->header(), $offset, $length);
    }

    /**
     * Return the RIFF type.
     * @throws MimeTypeException
     */
    public function riffType(): ?string
    {
        if ($this->bytes(0, 4) !== 'RIFF') {
            return null;
        }

        return $this->bytes(8, 4);
    }

    /**
     * Return the brand ISO Base Media.
     * @throws MimeTypeException
     */
    public function isoBaseMediaBrand(): ?string
    {
        if ($this->bytes(4, 4) !== 'ftyp') {
            return null;
        }

        return $this->bytes(8, 4);
    }

    /**
     * @throws MimeTypeException
     */
    private function validate(): void
    {
        if (!is_file($this->path) || !is_readable($this->path)) {
            throw MimeTypeException::fileNotExistOrUnreadable($this->path);
        }
    }
}
