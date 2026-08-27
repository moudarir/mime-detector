<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Detector;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Enum\MimeType;
use Moudarir\MimeDetector\FileResource;

/**
 * @internal
 */
final class ImageDetector implements MimeDetector
{

    public static function detect(FileResource $inspector): ?DetectionResult
    {
        $mimeType = match (true) {
            $inspector->startsWithHex('47494638') => MimeType::GIF,
            $inspector->startsWithHex('FFD8FF') => MimeType::JPEG,
            $inspector->startsWithHex('89504E470D0A1A0A') => MimeType::PNG,
            $inspector->startsWithHex('424D') => MimeType::BMP,
            $inspector->startsWithHex('49492A00', '4D4D002A') => MimeType::TIFF,
            $inspector->startsWithHex('00000100') => MimeType::ICO,
            default => null,
        };

        return $mimeType !== null
            ? DetectionResult::create($inspector, $mimeType, self::class)
            : null;
    }
}
