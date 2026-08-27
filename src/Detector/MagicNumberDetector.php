<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Detector;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Enum\DetectorSource;
use Moudarir\MimeDetector\Enum\MimeType;
use Moudarir\MimeDetector\Exceptions\MimeDetectorException;
use Moudarir\MimeDetector\FileResource;

/**
 * @internal
 */
final readonly class MagicNumberDetector implements MimeDetector
{

    /**
     * @throws MimeDetectorException
     */
    public static function detect(FileResource $inspector): ?DetectionResult
    {
        $mimeType = match (true) {
            $inspector->startsWithHex('25504446') => MimeType::PDF,
            $inspector->startsWithHex('7F454C46') => MimeType::ELF,
            $inspector->startsWithHex('4D5A') => MimeType::WINDOWS_EXECUTABLE,
            $inspector->startsWithHex('494433', 'FFF3', 'FFFB', 'FFFA') => MimeType::MP3,
            $inspector->startsWithHex('664C6143') => MimeType::FLAC,
            $inspector->startsWithHex('4F676753') => MimeType::OGG,
            $inspector->startsWithHex('1A45DFA3') => MimeType::MATROSKA,
            default => null,
        };

        if ($mimeType !== null) {
            return DetectionResult::create($inspector, $mimeType, DetectorSource::MAGIC_NUMBER);
        }

        return RiffDetector::detect($inspector)
            ?? ImageDetector::detect($inspector)
            ?? IsoBaseMediaDetector::detect($inspector)
            ?? DiskImageDetector::detect($inspector);
    }
}
