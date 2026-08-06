<?php

declare(strict_types=1);

namespace Moudarir\MimeType\Detector;

use Moudarir\MimeType\DetectionResult;
use Moudarir\MimeType\Enum\EnumMimeType;
use Moudarir\MimeType\FileInspector;

/**
 * @internal
 */
final class FileInfoMimeDetector implements MimeDetector
{

    public static function detect(FileInspector $inspector): ?DetectionResult
    {
        if (($mime = $inspector->fileInfoMime()) === null) {
            return null;
        }

        if (($mimeType = self::mapMimeType($mime)) === null) {
            return null;
        }

        return new DetectionResult($mimeType, self::class);
    }

    private static function mapMimeType(string $mime): ?EnumMimeType
    {
        return match ($mime) {
            'text/x-php',
            'application/x-php',
            'application/x-httpd-php' => EnumMimeType::PHP,
            default => EnumMimeType::tryFrom($mime),
        };
    }
}
