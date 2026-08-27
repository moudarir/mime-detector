<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector;

use Moudarir\MimeDetector\Enum\MimeType;

final readonly class DetectionResult
{

    private function __construct(
        private MimeType $mimeType,
        private string   $detector,
        private ?int     $filesize,
        private array    $pathInfo,
    )
    {
    }

    public static function create(FileResource $inspector, MimeType $mimeType, string $detector): self
    {
        $info = pathinfo($inspector->path());

        if (!array_key_exists('extension', $info)) {
            $info['extension'] = '';
        }

        if (!array_key_exists('filename', $info)) {
            $info['filename'] = '';
        }

        return new self(
            $mimeType,
            $detector,
            $inspector->filesize(),
            $info
        );
    }

    public function mimeType(): MimeType
    {
        return $this->mimeType;
    }

    public function detector(): string
    {
        return $this->detector;
    }

    public function value(): string
    {
        return $this->mimeType->value;
    }

    public function filesize(): ?int
    {
        return $this->filesize;
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

    /**
     * @return array{
     *     dirname: string,
     *     basename: string,
     *     extension: string,
     *     filename: string
     * }
     */
    public function pathInfo(): array
    {
        return $this->pathInfo;
    }
}
