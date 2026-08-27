<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Detector;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Enum\EnumMimeType;
use Moudarir\MimeDetector\FileInspector;

/**
 * @internal
 */
final class ImageMimeDetector implements MimeDetector
{

    public static function detect(FileInspector $inspector): ?DetectionResult
    {
        $mimeType = match (true) {
            $inspector->startsWithHex('47494638') => EnumMimeType::GIF,
            $inspector->startsWithHex('FFD8FF') => EnumMimeType::JPEG,
            $inspector->startsWithHex('89504E470D0A1A0A') => EnumMimeType::PNG,
            $inspector->startsWithHex('424D') => EnumMimeType::BMP,
            $inspector->startsWithHex('49492A00', '4D4D002A') => EnumMimeType::TIFF,
            $inspector->startsWithHex('00000100') => EnumMimeType::ICO,
            default => null,
        };

        return $mimeType !== null
            ? DetectionResult::create($inspector, $mimeType, self::class)
            : null;
    }
}
