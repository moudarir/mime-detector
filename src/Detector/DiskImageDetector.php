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
final class DiskImageDetector implements MimeDetector
{

    private const int ISO_OFFSET = 32769; // 0x8001
    private const string ISO_MAGIC = '4344303031'; // ASCII "CD001"

    private const int DMG_TRAILER_SIZE = 512;
    private const string DMG_MAGIC = '6B6F6C79'; // ASCII "koly"

    /**
     * @throws MimeTypeException
     */
    public static function detect(FileInspector $inspector): ?DetectionResult
    {
        $mimeType = self::detectIso($inspector) ?? self::detectDmg($inspector);

        if ($mimeType === null) {
            return null;
        }

        return DetectionResult::create($inspector, $mimeType, self::class);
    }

    /**
     * @throws MimeTypeException
     */
    private static function detectIso(FileInspector $inspector): ?EnumMimeType
    {
        if ($inspector->readHex(self::ISO_OFFSET, 5) === self::ISO_MAGIC) {
            return EnumMimeType::ISO;
        }

        return null;
    }

    /**
     * @throws MimeTypeException
     */
    private static function detectDmg(FileInspector $inspector): ?EnumMimeType
    {
        $filesize = $inspector->filesize();

        if ($filesize === null || $filesize < self::DMG_TRAILER_SIZE) {
            return null;
        }

        if ($inspector->readHex($filesize - self::DMG_TRAILER_SIZE, 4) === self::DMG_MAGIC) {
            return EnumMimeType::DMG;
        }

        return null;
    }
}
