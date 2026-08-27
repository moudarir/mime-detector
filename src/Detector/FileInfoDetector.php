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
final class FileInfoDetector implements MimeDetector
{

    public static function detect(FileResource $inspector): ?DetectionResult
    {
        if (($mime = $inspector->fileInfoMime()) === null) {
            return null;
        }

        if (($mimeType = self::mapMimeType($mime)) === null) {
            return null;
        }

        return DetectionResult::create($inspector, $mimeType, DetectorSource::FILE_INFO);
    }

    private static function mapMimeType(string $mime): ?MimeType
    {
        return match ($mime) {
            'text/x-php',
            'application/x-php',
            'application/x-httpd-php' => MimeType::PHP,
            default => MimeType::tryFrom($mime),
        };
    }
}
