<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector;

use Moudarir\MimeDetector\Enum\DetectorSource;
use Moudarir\MimeDetector\Enum\MimeType;

final readonly class DetectionResult
{

    private function __construct(
        private MimeType       $mimeType,
        private DetectorSource $detector,
        private FileMetadata   $metadata,
    )
    {
    }

    public static function create(FileResource $inspector, MimeType $mimeType, DetectorSource $detector): self
    {
        return new self(
            $mimeType,
            $detector,
            FileMetadata::create($inspector),
        );
    }

    public function mimeType(): MimeType
    {
        return $this->mimeType;
    }

    public function detector(): DetectorSource
    {
        return $this->detector;
    }

    public function value(): string
    {
        return $this->mimeType->value;
    }

    public function detectorValue(): string
    {
        return $this->detector->value;
    }

    public function metadata(): FileMetadata
    {
        return $this->metadata;
    }
}
