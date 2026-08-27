<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector;

use Moudarir\MimeDetector\Detector\FileInfoMimeDetector;
use Moudarir\MimeDetector\Detector\MagicNumberMimeDetector;
use Moudarir\MimeDetector\Detector\TextDetector;
use Moudarir\MimeDetector\Detector\ZipMimeDetector;
use Moudarir\MimeDetector\Enum\EnumMimeType;
use Moudarir\MimeDetector\Exceptions\MimeTypeException;

final class MimeType
{

    /**
     * @throws MimeTypeException
     */
    public static function detect(string $filepath): DetectionResult
    {
        $inspector = FileInspector::create($filepath);

        return MagicNumberMimeDetector::detect($inspector)
            ?? ZipMimeDetector::detect($inspector)
            ?? TextDetector::detect($inspector)
            ?? FileInfoMimeDetector::detect($inspector)
            ?? DetectionResult::create(
                $inspector,
                EnumMimeType::OCTET_STREAM,
                'DefaultFallback'
            );
    }
}
