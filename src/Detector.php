<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector;

use Moudarir\MimeDetector\Detector\FileInfoDetector;
use Moudarir\MimeDetector\Detector\MagicNumberDetector;
use Moudarir\MimeDetector\Detector\TextDetector;
use Moudarir\MimeDetector\Detector\ZipDetector;
use Moudarir\MimeDetector\Enum\MimeType;
use Moudarir\MimeDetector\Exceptions\MimeDetectorException;

final class Detector
{

    /**
     * @throws MimeDetectorException
     */
    public static function detect(string $filepath): DetectionResult
    {
        $inspector = FileResource::create($filepath);

        return MagicNumberDetector::detect($inspector)
            ?? ZipDetector::detect($inspector)
            ?? TextDetector::detect($inspector)
            ?? FileInfoDetector::detect($inspector)
            ?? DetectionResult::create(
                $inspector,
                MimeType::OCTET_STREAM,
                'DefaultFallback'
            );
    }
}
