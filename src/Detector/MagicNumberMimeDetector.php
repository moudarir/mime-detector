<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Detector;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Enum\EnumMimeType;
use Moudarir\MimeDetector\FileInspector;

/**
 * @internal
 */
final readonly class MagicNumberMimeDetector implements MimeDetector
{

    public static function detect(FileInspector $inspector): ?DetectionResult
    {
        $mimeType = match (true) {
            $inspector->startsWithHex('25504446') => EnumMimeType::PDF,
            $inspector->startsWithHex('7F454C46') => EnumMimeType::ELF,
            $inspector->startsWithHex('4D5A') => EnumMimeType::WINDOWS_EXECUTABLE,
            $inspector->startsWithHex('494433', 'FFF3', 'FFFB', 'FFFA') => EnumMimeType::MP3,
            $inspector->startsWithHex('664C6143') => EnumMimeType::FLAC,
            $inspector->startsWithHex('4F676753') => EnumMimeType::OGG,
            $inspector->startsWithHex('1A45DFA3') => EnumMimeType::MATROSKA,
            default => null,
        };

        if ($mimeType !== null) {
            return DetectionResult::create($inspector, $mimeType, self::class);
        }

        return RiffDetector::detect($inspector)
            ?? ImageMimeDetector::detect($inspector)
            ?? IsoBaseMediaDetector::detect($inspector)
            ?? DiskImageDetector::detect($inspector);
    }
}
