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
        if (!function_exists('exif_imagetype')) {
            return null;
        }

        if (($imageType = @exif_imagetype($inspector->path())) === false) {
            return null;
        }

        $mimeType = match ($imageType) {
            IMAGETYPE_GIF  => EnumMimeType::GIF,
            IMAGETYPE_JPEG => EnumMimeType::JPEG,
            IMAGETYPE_PNG  => EnumMimeType::PNG,
            IMAGETYPE_BMP  => EnumMimeType::BMP,
            IMAGETYPE_TIFF_II,
            IMAGETYPE_TIFF_MM => EnumMimeType::TIFF,
            IMAGETYPE_WEBP => EnumMimeType::WEBP,
            IMAGETYPE_AVIF => EnumMimeType::AVIF,
            IMAGETYPE_ICO => EnumMimeType::ICO,
            default => null,
        };

        if ($mimeType === null) {
            return null;
        }

        return new DetectionResult($mimeType, self::class);
    }
}
