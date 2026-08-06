<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector;

use Moudarir\MimeDetector\Enum\EnumMimeType;

final readonly class DetectionResult
{

    public function __construct(private EnumMimeType $mimeType, private string $detector)
    {
    }

    public function mimeType(): EnumMimeType
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
}
