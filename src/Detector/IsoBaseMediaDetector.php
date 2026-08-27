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
final class IsoBaseMediaDetector implements MimeDetector
{

    public static function detect(FileResource $inspector): ?DetectionResult
    {
        if (($brand = $inspector->isoBaseMediaBrand()) === null) {
            return null;
        }

        $mimeType = match ($brand) {
            /*
             * Images HEIF / HEIC
             */
            'mif1',
            'msf1' => MimeType::HEIF,

            'heic',
            'heix',
            'hevc',
            'hevx' => MimeType::HEIC,

            'avif' => MimeType::AVIF,

            /*
             * MPEG-4 vidéo
             */
            'isom',
            'iso2',
            'iso3',
            'iso4',
            'iso5',
            'iso6',
            'mp41',
            'mp42',
            'avc1',
            'dash' => MimeType::MP4,

            /*
             * MPEG-4 audio
             */
            'M4A ',
            'M4B ' => MimeType::M4A,

            /*
             * 3GPP
             */
            '3gp4',
            '3gp5',
            '3gp6',
            '3gp7' => MimeType::THREE_GPP,

            // QuickTime
            'qt  ' => MimeType::MOV,

            default => null,
        };

        if ($mimeType === null) {
            return null;
        }

        return DetectionResult::create($inspector, $mimeType, DetectorSource::ISO_BASE_MEDIA);
    }
}
