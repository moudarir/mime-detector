<?php

declare(strict_types=1);

namespace Moudarir\MimeType\Detector;

use Moudarir\MimeType\DetectionResult;
use Moudarir\MimeType\FileInspector;

interface MimeDetector
{

    public static function detect(FileInspector $inspector): ?DetectionResult;
}
