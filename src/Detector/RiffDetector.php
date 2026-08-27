<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Detector;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Enum\EnumMimeType;
use Moudarir\MimeDetector\FileInspector;

/**
 * @internal
 */
final class RiffDetector implements MimeDetector
{

    public static function detect(FileInspector $inspector): ?DetectionResult
    {
        if (($fourCc = $inspector->riffType()) === null) {
            return null;
        }

        $mimeType = match ($fourCc) {
            'WEBP' => EnumMimeType::WEBP,
            'WAVE' => EnumMimeType::WAV,
            'AVI ' => EnumMimeType::AVI,
            default => null,
        };

        if ($mimeType === null) {
            return null;
        }

        return DetectionResult::create($inspector, $mimeType, self::class);
    }
}
