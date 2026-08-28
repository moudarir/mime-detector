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
final class SeparatedValuesDetector implements MimeDetector
{

    private const int MAX_ROWS = 5;

    /**
     * @var array<string>
     */
    private const array SEPARATORS = [',', ';', '|', "\t"];

    public static function detect(FileResource $inspector): ?DetectionResult
    {
        foreach (self::SEPARATORS as $separator) {
            if (!self::hasStructure($inspector, $separator)) {
                continue;
            }

            $mimeType = $separator === "\t" ? MimeType::TSV : MimeType::CSV;

            return DetectionResult::create($inspector, $mimeType, DetectorSource::SEPARATED_VALUES);
        }

        return null;
    }

    private static function hasStructure(FileResource $resource, string $separator): bool
    {
        $stream = $resource->stream();

        if (!is_resource($stream) || fseek($stream, 0) !== 0) {
            return false;
        }

        $columnCount = null;
        $rows = 0;

        while ($rows < self::MAX_ROWS && ($row = fgetcsv($stream, separator: $separator, escape: '')) !== false) {
            if ($row === [null] || self::isEmptyRow($row)) {
                continue;
            }

            $columns = count($row);

            /*
             * The first non-empty row defines the expected number of columns.
             */
            if ($columnCount === null) {
                if ($columns < 2) {
                    return false;
                }

                $columnCount = $columns;
                $rows = 1;

                continue;
            }

            if ($columns !== $columnCount) {
                return false;
            }

            ++$rows;
        }

        return $rows >= 2;
    }

    /**
     * @param array<int, string|null> $row
     */
    private static function isEmptyRow(array $row): bool
    {
        return array_all($row, fn ($value) => $value === null || trim($value) === '');
    }
}
