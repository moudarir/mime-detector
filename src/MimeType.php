<?php

declare(strict_types=1);

namespace Moudarir\MimeType;

use Moudarir\MimeType\Detector\FileInfoMimeDetector;
use Moudarir\MimeType\Detector\MagicNumberMimeDetector;
use Moudarir\MimeType\Detector\TextDetector;
use Moudarir\MimeType\Detector\ZipMimeDetector;
use Moudarir\MimeType\Enum\EnumMimeType;
use Moudarir\MimeType\Exceptions\MimeTypeException;

/**
 * Point d'entrée principal de détection MIME.
 */
final class MimeType
{

    /**
     * @throws MimeTypeException
     */
    public static function detect(string $filepath): ?DetectionResult
    {
        $inspector = new FileInspector($filepath);

        return ZipMimeDetector::detect($inspector)
            ?? MagicNumberMimeDetector::detect($inspector)
            ?? TextDetector::detect($inspector)
            ?? FileInfoMimeDetector::detect($inspector)
            ?? new DetectionResult(EnumMimeType::OCTET_STREAM, 'DefaultFallback');
    }
}
