<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector;

use Moudarir\MimeDetector\Exceptions\MimeDetectorException;

final class FileResource
{

    private const int HEADER_SIZE = 64;

    private string $hexHeader;

    /**
     * @param string $path
     * @param string $header
     * @param int $filesize
     * @param int $lastModified
     * @param resource $stream
     */
    private function __construct(
        private readonly string $path,
        private readonly string $header,
        private readonly int $filesize,
        private readonly int $lastModified,
        private readonly mixed $stream,
    )
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

        if (($stream = @fopen($path, 'rb')) === false) {
            throw MimeDetectorException::unableOpenFile($path);
        }

        if (($filesize = @filesize($path)) === false) {
            throw MimeDetectorException::unableToRetrieveFilesize($path);
        }

        if (($lastModified = filemtime($path)) === false) {
            throw MimeDetectorException::unableToDetermineLastModified();
        }

        return new self(
            $path,
            self::getBytes($path, $stream, self::HEADER_SIZE),
            $filesize,
            $lastModified,
            $stream,
        );
    }

    public function path(): string
    {
        return $this->path;
    }

    /**
     * @return resource
     */
    public function stream(): mixed
    {
        return $this->stream;
    }

    public function filesize(): int
    {
        return $this->filesize;
    }

    public function lastModified(): int
    {
        return $this->lastModified;
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

        $bytes = self::getBytes($this->path, $this->stream,  $length, $offset);

        if (strlen($bytes) !== $length) {
            return null;
        }

        return self::toHex($bytes);
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
    private static function getBytes(string $path, mixed $stream, int $length, int $offset = 0): string
    {
        if (fseek($stream, $offset) !== 0) {
            throw MimeDetectorException::unableReadFileHeader($path);
        }

        if (($bytes = fread($stream, $length)) === false) {
            throw MimeDetectorException::unableReadFileHeader($path);
        }

        return $bytes;
    }

    public static function toHex(string $content): string
    {
        return strtoupper(bin2hex($content));
    }
}
