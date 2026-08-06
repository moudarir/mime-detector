<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Detector;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\FileInspector;

interface MimeDetector
{

    public static function detect(FileInspector $inspector): ?DetectionResult;
}
