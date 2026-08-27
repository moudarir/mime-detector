<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Detector;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Enum\DetectorSource;
use Moudarir\MimeDetector\Enum\MimeType;
use Moudarir\MimeDetector\FileResource;

/**
 * @internal
 */
final class RiffDetector implements MimeDetector
{

    public static function detect(FileResource $inspector): ?DetectionResult
    {
        if (($fourCc = $inspector->riffType()) === null) {
            return null;
        }

        $mimeType = match ($fourCc) {
            'WEBP' => MimeType::WEBP,
            'WAVE' => MimeType::WAV,
            'AVI ' => MimeType::AVI,
            default => null,
        };

        if ($mimeType === null) {
            return null;
        }

        return DetectionResult::create($inspector, $mimeType, DetectorSource::RIFF);
    }
}
