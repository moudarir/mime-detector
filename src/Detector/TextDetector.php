<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Detector;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Enum\EnumMimeType;
use Moudarir\MimeDetector\FileInspector;

/**
 * @internal
 */
final class TextDetector implements MimeDetector
{

    private const int SAMPLE_SIZE = 16 * 1024;

    public static function detect(FileInspector $inspector): ?DetectionResult
    {
        $content = @file_get_contents($inspector->path(), context: null, length: self::SAMPLE_SIZE);

        if (!is_string($content) || $content === '') {
            return null;
        }

        if (self::isBinary($content)) {
            return null;
        }

        $content = ltrim($content);
        $mimeType = self::detectPhp($content)
            ?? self::detectSvg($content)
            ?? self::detectHtml($content)
            ?? self::detectJson($content)
            ?? self::detectXml($content)
            ?? self::detectSql($content)
            ?? self::detectJavascript($content)
            ?? self::detectCss($content)
            ?? self::detectYaml($content)
            ?? self::detectMarkdown($content)
            ?? self::detectSeparatedValues($content);

        if ($mimeType === null) {
            return null;
        }

        return new DetectionResult($mimeType, self::class);
    }

    private static function isBinary(string $content): bool
    {
        $binaryBytes = 0;
        $length = strlen($content);

        for ($i = 0; $i < $length; ++$i) {
            $byte = ord($content[$i]);

            if ($byte < 9 || ($byte > 13 && $byte < 32)) {
                ++$binaryBytes;

                if ($binaryBytes > 4) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function detectPhp(string $content): ?EnumMimeType
    {
        return self::startsWithAny($content, '<?php', '<?=') ? EnumMimeType::PHP : null;
    }

    private static function detectSvg(string $content): ?EnumMimeType
    {
        return self::containsAny($content, '<svg', 'xmlns="http://www.w3.org/2000/svg"')
            ? EnumMimeType::SVG
            : null;
    }

    private static function detectHtml(string $content): ?EnumMimeType
    {
        return self::countContains(strtolower($content), '<html', '<head', '<body', '<title', '<meta', '<!doctype html') >= 2
            ? EnumMimeType::HTML
            : null;
    }

    private static function detectJson(string $content): ?EnumMimeType
    {
        if (!str_starts_with($content, '{') && !str_starts_with($content, '[')) {
            return null;
        }

        return self::countContains($content, '":', ',', '{', '}', '[', ']') >= 3
            ? EnumMimeType::JSON
            : null;
    }

    private static function detectXml(string $content): ?EnumMimeType
    {
        if (str_starts_with($content, '<!doctype html')) {
            return null;
        }

        return preg_match('/<([A-Za-z_][\w:.-]*)([\s>])/', $content) === 1
            ? EnumMimeType::XML
            : null;
    }

    private static function detectSql(string $content): ?EnumMimeType
    {
        return self::countContains(
            strtoupper($content),
            'SELECT ', 'INSERT ', 'UPDATE ', 'DELETE ', 'CREATE TABLE', 'CREATE DATABASE', 'ALTER TABLE',
            'DROP TABLE', 'FROM ', 'WHERE ', 'VALUES ', 'ENGINE=', 'AUTO_INCREMENT') >= 2
            ? EnumMimeType::SQL
            : null;
    }

    private static function detectJavascript(string $content): ?EnumMimeType
    {
        return self::countContains($content,
            'function ', 'const ', 'let ', 'var ', '=>', 'import ', 'export ',
            'class ', 'console.', 'document.', 'window.', 'require(') >= 2
            ? EnumMimeType::JAVASCRIPT
            : null;
    }

    private static function detectCss(string $content): ?EnumMimeType
    {
        return self::containsAny($content, '@media', '@supports', '@font-face', ':root')
        || preg_match('/[.#]?[a-zA-Z_-][\w-]*\s*\{/', $content) === 1
            ? EnumMimeType::CSS
            : null;
    }

    private static function detectYaml(string $content): ?EnumMimeType
    {
        return str_starts_with($content, "---")
        || preg_match('/^[a-zA-Z0-9_.-]+\s*:\s*.+$/m', $content) === 1
            ? EnumMimeType::YAML
            : null;
    }

    private static function detectMarkdown(string $content): ?EnumMimeType
    {
        return self::countContains($content, '# ', '## ', '```', '](', '![', '> ') >= 2
            ? EnumMimeType::MARKDOWN
            : null;
    }

    private static function detectSeparatedValues(string $content): ?EnumMimeType
    {
        foreach (["\t", ',', ';', '|'] as $separator) {
            if (self::hasSeparator($content, $separator)) {
                return $separator === "\t" ? EnumMimeType::TSV : EnumMimeType::TEXT_CSV;
            }
        }

        return null;
    }

    private static function hasSeparator(string $content, string $separator): bool
    {
        $lines = preg_split('/\R/', trim($content));

        if (!is_array($lines)) {
            return false;
        }

        $matches = 0;

        foreach (array_slice($lines, 0, 5) as $line) {
            if (str_contains($line, $separator)) {
                ++$matches;
            }
        }

        return $matches >= 2;
    }

    private static function startsWithAny(string $content, string ...$values): bool
    {
        return array_any(
            $values,
            static fn (string $value): bool => str_starts_with($content, $value)
        );
    }

    private static function containsAny(string $content, string ...$values): bool
    {
        return array_any(
            $values,
            static fn (string $value): bool => str_contains($content, $value)
        );
    }

    private static function countContains(string $content, string ...$values): int
    {
        return count(array_filter(
            $values,
            static fn (string $value): bool => str_contains($content, $value)
        ));
    }
}
