<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Detector;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Enum\EnumMimeType;
use Moudarir\MimeDetector\Exceptions\MimeTypeException;
use Moudarir\MimeDetector\FileInspector;

/**
 * @internal
 */
final class IsoBaseMediaDetector implements MimeDetector
{

    /**
     * @throws MimeTypeException
     */
    public static function detect(FileInspector $inspector): ?DetectionResult
    {
        if (($brand = $inspector->isoBaseMediaBrand()) === null) {
            return null;
        }

        $mimeType = match ($brand) {
            /*
             * Images HEIF / HEIC
             */
            'mif1',
            'msf1' => EnumMimeType::HEIF,

            'heic',
            'heix',
            'hevc',
            'hevx' => EnumMimeType::HEIC,

            'avif' => EnumMimeType::AVIF,

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
            'dash' => EnumMimeType::MP4,

            /*
             * MPEG-4 audio
             */
            'M4A ',
            'M4B ' => EnumMimeType::M4A,

            /*
             * 3GPP
             */
            '3gp4',
            '3gp5',
            '3gp6',
            '3gp7' => EnumMimeType::THREE_GPP,

            // QuickTime
            'qt  ' => EnumMimeType::MOV,

            default => null,
        };

        if ($mimeType === null) {
            return null;
        }

        return new DetectionResult($mimeType, self::class);
    }
}
