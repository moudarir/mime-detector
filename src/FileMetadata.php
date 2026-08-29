<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector;

final readonly class FileMetadata
{

    private function __construct(
        private int   $filesize,
        private int   $lastModified,
        private mixed $stream,
        private array $pathInfo,
    )
    {
    }

    public function __destruct()
    {
        if (isset($this->stream) && is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

    public static function create(FileResource $inspector): self
    {
        $info = pathinfo($inspector->path());

        if (!array_key_exists('extension', $info)) {
            $info['extension'] = '';
        }

        if (!array_key_exists('filename', $info)) {
            $info['filename'] = '';
        }

        return new self(
            $inspector->filesize(),
            $inspector->lastModified(),
            $inspector->stream(),
            $info
        );
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
     * @return resource
     */
    public function stream(): mixed
    {
        return $this->stream;
    }

    public function dirname(): string
    {
        return $this->pathInfo['dirname'];
    }

    public function basename(): string
    {
        return $this->pathInfo['basename'];
    }

    public function filename(): string
    {
        return $this->pathInfo['filename'];
    }

    public function extension(): string
    {
        return $this->pathInfo['extension'];
    }
}
