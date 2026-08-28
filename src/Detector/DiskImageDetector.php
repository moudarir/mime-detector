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
final class DiskImageDetector implements MimeDetector
{

    private const int ISO_OFFSET = 32769; // 0x8001
    private const string ISO_MAGIC = '4344303031'; // ASCII "CD001"

    private const int DMG_TRAILER_SIZE = 512;
    private const string DMG_MAGIC = '6B6F6C79'; // ASCII "koly"

    /**
     * @throws MimeDetectorException
     */
    public static function detect(FileResource $inspector): ?DetectionResult
    {
        $mimeType = self::detectIso($inspector) ?? self::detectDmg($inspector);

        if ($mimeType === null) {
            return null;
        }

        return DetectionResult::create($inspector, $mimeType, DetectorSource::DISK_IMAGE);
    }

    /**
     * @throws MimeDetectorException
     */
    private static function detectIso(FileResource $inspector): ?MimeType
    {
        if ($inspector->readHex(self::ISO_OFFSET, 5) === self::ISO_MAGIC) {
            return MimeType::ISO;
        }

        return null;
    }

    /**
     * @throws MimeDetectorException
     */
    private static function detectDmg(FileResource $inspector): ?MimeType
    {
        $filesize = $inspector->filesize();

        if ($filesize < self::DMG_TRAILER_SIZE) {
            return null;
        }

        if ($inspector->readHex($filesize - self::DMG_TRAILER_SIZE, 4) === self::DMG_MAGIC) {
            return MimeType::DMG;
        }

        return null;
    }
}
