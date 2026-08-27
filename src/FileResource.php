<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector;

use finfo;
use Moudarir\MimeDetector\Exceptions\MimeDetectorException;

final class FileResource
{

    private const int HEADER_SIZE = 64;

    private static ?finfo $finfo = null;

    private ?int $filesize;

    private bool $filesizeResolved = false;

    private string $hexHeader;

    /**
     */
    private function __construct(private readonly string $path, private readonly string $header)
    {
    }

    /**
     * @throws MimeDetectorException
     */
    public static function create(string $path): self
    {
        if (!is_file($path) || !is_readable($path)) {
            throw MimeDetectorException::fileNotExistOrUnreadable($path);
        }

        return new self($path, self::getBytes($path, self::HEADER_SIZE));
    }

    public function path(): string
    {
        return $this->path;
    }

    public function filesize(): ?int
    {
        if ($this->filesizeResolved) {
            return $this->filesize;
        }

        $size = @filesize($this->path);
        $this->filesize = $size !== false ? $size : null;
        $this->filesizeResolved = true;

        return $this->filesize;
    }

    /**
     * Returns the header in hexadecimal representation.
     */
    public function hexHeader(): string
    {
        return $this->hexHeader ??= self::toHex($this->header);
    }

    /**
     * @throws MimeDetectorException
     */
    public function readHex(int $offset, int $length): ?string
    {
        if ($offset < 0 || $length < 0) {
            return null;
        }

        $bytes = self::getBytes($this->path, $length, $offset);

        if (strlen($bytes) !== $length) {
            return null;
        }

        return self::toHex($bytes);
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
     * Check if the header starts with a hexadecimal signature.
     */
    public function startsWithHex(string ...$signatures): bool
    {
        return array_any($signatures, fn ($signature) => str_starts_with($this->hexHeader(), strtoupper($signature)));

    }

    /**
     * Return the heder brut part.
     */
    public function bytes(int $offset, int $length): string
    {
        return substr($this->header, $offset, $length);
    }

    /**
     * Return the RIFF type.
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
     */
    public function isoBaseMediaBrand(): ?string
    {
        if ($this->bytes(4, 4) !== 'ftyp') {
            return null;
        }

        return $this->bytes(8, 4);
    }

    /**
     * @throws MimeDetectorException
     */
    private static function getBytes(string $path, int $length, int $offset = 0): string
    {
        if (($handle = @fopen($path, 'rb')) === false) {
            throw MimeDetectorException::unableOpenFile($path);
        }

        try {
            if (fseek($handle, $offset) !== 0) {
                throw MimeDetectorException::unableReadFileHeader($path);
            }

            if (($bytes = fread($handle, $length)) === false) {
                throw MimeDetectorException::unableReadFileHeader($path);
            }

            return $bytes;
        } finally {
            fclose($handle);
        }
    }

    public static function toHex(string $content): string
    {
        return strtoupper(bin2hex($content));
    }
}
