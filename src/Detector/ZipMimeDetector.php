<?php

declare(strict_types=1);

namespace Moudarir\MimeType\Detector;

use Moudarir\MimeType\DetectionResult;
use Moudarir\MimeType\Enum\EnumMimeType;
use Moudarir\MimeType\Exceptions\MimeTypeException;
use Moudarir\MimeType\FileInspector;
use ZipArchive;

final class ZipMimeDetector implements MimeDetector
{

    /**
     * @throws MimeTypeException
     */
    public static function detect(FileInspector $inspector): ?DetectionResult
    {
        /**
         * No need to open ZIP file if missing PK signature.
         *
         * '504B0304' → Local file header
         * '504B0506' → Empty archive
         * '504B0708' → Spanned archive
         */
        if (!$inspector->startsWithHex('504B0304', '504B0506', '504B0708')) {
            return null;
        }

        $zip = new ZipArchive();

        if ($zip->open($inspector->path()) !== true) {
            return null;
        }

        try {
            $entries = self::indexEntries($zip);
            $mimeType =
                self::detectOffice($entries)
                ?? self::detectOpenDocument($zip, $entries)
                ?? self::detectEpub($zip, $entries)
                ?? self::detectApk($entries)
                ?? self::detectJar($entries)
                ?? EnumMimeType::ZIP;

            return new DetectionResult($mimeType, self::class);
        } finally {
            $zip->close();
        }
    }

    /**
     * @param array<string, true> $entries
     */
    private static function detectOffice(array $entries): ?EnumMimeType
    {
        if (isset($entries['word/document.xml'])) {
            return EnumMimeType::DOCX;
        }

        if (isset($entries['xl/workbook.xml'])) {
            return EnumMimeType::XLSX;
        }

        if (isset($entries['ppt/presentation.xml'])) {
            return EnumMimeType::PPTX;
        }

        return null;
    }

    /**
     * @param array<string, true> $entries
     */
    private static function detectOpenDocument(ZipArchive $zip, array $entries): ?EnumMimeType
    {
        if (!isset($entries['META-INF/manifest.xml']) || !isset($entries['mimetype'])) {
            return null;
        }

        $mime = $zip->getFromName('mimetype');

        if (!is_string($mime)) {
            return null;
        }

        return match ($mime) {
            EnumMimeType::ODT->value => EnumMimeType::ODT,
            EnumMimeType::ODS->value => EnumMimeType::ODS,
            EnumMimeType::ODP->value => EnumMimeType::ODP,
            default => null,
        };
    }

    /**
     * @param array<string, true> $entries
     */
    private static function detectEpub(ZipArchive $zip, array $entries): ?EnumMimeType
    {
        if (!isset($entries['META-INF/container.xml']) || !isset($entries['mimetype'])) {
            return null;
        }

        $mime = $zip->getFromName('mimetype');

        if (!is_string($mime)) {
            return null;
        }

        return $mime === EnumMimeType::EPUB->value ? EnumMimeType::EPUB : null;
    }

    /**
     * @param array<string, true> $entries
     */
    private static function detectApk(array $entries): ?EnumMimeType
    {
        if (isset($entries['AndroidManifest.xml']) && isset($entries['classes.dex'])) {
            return EnumMimeType::APK;
        }

        return null;
    }

    /**
     * @param array<string, true> $entries
     */
    private static function detectJar(array $entries): ?EnumMimeType
    {
        return isset($entries['META-INF/MANIFEST.MF']) ? EnumMimeType::JAR : null;
    }

    /**
     * @return array<string, true>
     */
    private static function indexEntries(ZipArchive $zip): array
    {
        $entries = [];

        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $name = $zip->getNameIndex($i);

            if (!is_string($name)) {
                continue;
            }

            $entries[$name] = true;
        }

        return $entries;
    }
}
