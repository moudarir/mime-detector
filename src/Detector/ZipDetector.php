<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Detector;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Enum\MimeType;
use Moudarir\MimeDetector\FileResource;
use ZipArchive;

final class ZipDetector implements MimeDetector
{

    public static function detect(FileResource $inspector): ?DetectionResult
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
                ?? MimeType::ZIP;

            return DetectionResult::create($inspector, $mimeType, self::class);
        } finally {
            $zip->close();
        }
    }

    /**
     * @param array<string, true> $entries
     */
    private static function detectOffice(array $entries): ?MimeType
    {
        if (isset($entries['word/document.xml'])) {
            return MimeType::DOCX;
        }

        if (isset($entries['xl/workbook.xml'])) {
            return MimeType::XLSX;
        }

        if (isset($entries['ppt/presentation.xml'])) {
            return MimeType::PPTX;
        }

        return null;
    }

    /**
     * @param array<string, true> $entries
     */
    private static function detectOpenDocument(ZipArchive $zip, array $entries): ?MimeType
    {
        if (!isset($entries['META-INF/manifest.xml']) || !isset($entries['mimetype'])) {
            return null;
        }

        $mime = $zip->getFromName('mimetype');

        if (!is_string($mime)) {
            return null;
        }

        return match ($mime) {
            MimeType::ODT->value => MimeType::ODT,
            MimeType::ODS->value => MimeType::ODS,
            MimeType::ODP->value => MimeType::ODP,
            default => null,
        };
    }

    /**
     * @param array<string, true> $entries
     */
    private static function detectEpub(ZipArchive $zip, array $entries): ?MimeType
    {
        if (!isset($entries['META-INF/container.xml']) || !isset($entries['mimetype'])) {
            return null;
        }

        $mime = $zip->getFromName('mimetype');

        if (!is_string($mime)) {
            return null;
        }

        return $mime === MimeType::EPUB->value ? MimeType::EPUB : null;
    }

    /**
     * @param array<string, true> $entries
     */
    private static function detectApk(array $entries): ?MimeType
    {
        if (isset($entries['AndroidManifest.xml']) && isset($entries['classes.dex'])) {
            return MimeType::APK;
        }

        return null;
    }

    /**
     * @param array<string, true> $entries
     */
    private static function detectJar(array $entries): ?MimeType
    {
        return isset($entries['META-INF/MANIFEST.MF']) ? MimeType::JAR : null;
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
