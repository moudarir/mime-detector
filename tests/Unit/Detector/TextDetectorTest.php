<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Tests\Unit\Detector;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Detector\TextDetector;
use Moudarir\MimeDetector\Enum\DetectorSource;
use Moudarir\MimeDetector\Enum\MimeType;
use Moudarir\MimeDetector\FileResource;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TextDetectorTest extends TestCase
{

    private string $filepath;

    protected function setUp(): void
    {
        $filepath = tempnam(sys_get_temp_dir(), 'mime-detector-');

        if ($filepath === false) {
            self::fail('Unable to create temporary file.');
        }

        $this->filepath = $filepath;
    }

    protected function tearDown(): void
    {
        if (is_file($this->filepath)) {
            unlink($this->filepath);
        }
    }

    #[Test]
    #[DataProvider('textContentProvider')]
    public function itDetectsTextContent(
        string $content,
        MimeType $expectedMimeType
    ): void {
        file_put_contents($this->filepath, $content);

        $result = $this->detect();

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame($expectedMimeType, $result->mimeType());
        self::assertSame($expectedMimeType->value, $result->value());
        self::assertSame(DetectorSource::TEXT, $result->detector());
    }

    /**
     * @return iterable<string, array{string, MimeType}>
     */
    public static function textContentProvider(): iterable
    {
        yield 'PHP' => [
            '<?php echo "Hello";',
            MimeType::PHP,
        ];

        yield 'PHP short echo tag' => [
            '<?= $value ?>',
            MimeType::PHP,
        ];

        yield 'SVG' => [
            '<svg xmlns="http://www.w3.org/2000/svg"></svg>',
            MimeType::SVG,
        ];

        yield 'HTML' => [
            '<!DOCTYPE html>
<html>
<head>
<title>Test</title>
</head>
<body>
Hello
</body>
</html>',
            MimeType::HTML,
        ];

        yield 'JSON object' => [
            '{"name":"John","age":20}',
            MimeType::JSON,
        ];

        yield 'JSON array' => [
            '[{"id":1},{"id":2}]',
            MimeType::JSON,
        ];

        yield 'XML' => [
            '<?xml version="1.0"?>
<root>
    <item>value</item>
</root>',
            MimeType::XML,
        ];

        yield 'SQL' => [
            'SELECT id, name FROM users WHERE active = 1',
            MimeType::SQL,
        ];

        yield 'JavaScript' => [
            'const value = 10;
function test() {
    return value;
}',
            MimeType::JAVASCRIPT,
        ];

        yield 'CSS' => [
            'body {
    margin: 0;
}

.container {
    display: block;
}',
            MimeType::CSS,
        ];

        yield 'YAML' => [
            'name: application
version: 1.0
environment: production',
            MimeType::YAML,
        ];

        yield 'YAML document' => [
            '---
name: application
version: 1.0',
            MimeType::YAML,
        ];

        yield 'Markdown' => [
            '# Title

This is a paragraph.

## Section',
            MimeType::MARKDOWN,
        ];
    }

    #[Test]
    public function itReturnsNullForEmptyFile(): void
    {
        file_put_contents($this->filepath, '');

        $result = $this->detect();

        self::assertNull($result);
    }

    #[Test]
    public function itReturnsNullForBinaryContent(): void
    {
        file_put_contents(
            $this->filepath,
            "text\0\1\2\3\4"
        );

        $result = $this->detect();

        self::assertNull($result);
    }

    #[Test]
    public function itReturnsNullForUnknownTextContent(): void
    {
        file_put_contents(
            $this->filepath,
            'This is just an ordinary sentence with no special structure.'
        );

        $result = $this->detect();

        self::assertNull($result);
    }

    #[Test]
    public function itIgnoresLeadingWhitespace(): void
    {
        file_put_contents(
            $this->filepath,
            "   \n\t  <?php echo 'test';"
        );

        $result = $this->detect();

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(MimeType::PHP, $result->mimeType());
    }

    #[Test]
    public function itDoesNotDetectHtmlAsXml(): void
    {
        file_put_contents(
            $this->filepath,
            '<!doctype html>
<html>
<head>
<title>Test</title>
</head>
<body>
Hello
</body>
</html>'
        );

        $result = $this->detect();

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(MimeType::HTML, $result->mimeType());
    }

    #[Test]
    public function itRequiresMultipleLinesForSeparatedValues(): void
    {
        file_put_contents(
            $this->filepath,
            'name,John'
        );

        $result = $this->detect();

        self::assertNull($result);
    }

    private function detect(): ?DetectionResult
    {
        return TextDetector::detect(
            FileResource::create($this->filepath)
        );
    }
}
