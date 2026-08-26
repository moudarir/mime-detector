<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Tests\Unit;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Enum\EnumMimeType;
use Moudarir\MimeDetector\Exceptions\MimeTypeException;
use Moudarir\MimeDetector\MimeType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ZipArchive;

final class MimeTypeTest extends TestCase
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
    public function itDetectsMagicNumberMimeType(): void
    {
        file_put_contents(
            $this->filepath,
            "%PDF-1.7\n"
        );

        $result = MimeType::detect($this->filepath);

        self::assertDetectionResult(
            $result,
            EnumMimeType::PDF,
            'Moudarir\\MimeDetector\\Detector\\MagicNumberMimeDetector'
        );
    }

    #[Test]
    public function itDetectsZipMimeType(): void
    {
        unlink($this->filepath);

        $zip = new ZipArchive();

        self::assertSame(
            true,
            $zip->open($this->filepath, ZipArchive::CREATE)
        );

        $zip->addFromString('example.txt', 'Hello world.');
        $zip->close();

        $result = MimeType::detect($this->filepath);

        self::assertDetectionResult(
            $result,
            EnumMimeType::ZIP,
            'Moudarir\\MimeDetector\\Detector\\ZipMimeDetector'
        );
    }

    #[Test]
    public function itDetectsPlainTextThroughFileInfo(): void
    {
        file_put_contents(
            $this->filepath,
            'This is a plain text document.'
        );

        $result = MimeType::detect($this->filepath);

        self::assertDetectionResult(
            $result,
            EnumMimeType::TEXT_PLAIN,
            'Moudarir\\MimeDetector\\Detector\\FileInfoMimeDetector'
        );
    }

    #[Test]
    public function itDetectsOctetStreamThroughFileInfo(): void
    {
        file_put_contents(
            $this->filepath,
            str_repeat("\0", 32)
        );

        $result = MimeType::detect($this->filepath);

        self::assertDetectionResult(
            $result,
            EnumMimeType::OCTET_STREAM,
            'Moudarir\\MimeDetector\\Detector\\FileInfoMimeDetector'
        );
    }

    #[Test]
    public function itDetectsJsonThroughTextDetector(): void
    {
        file_put_contents(
            $this->filepath,
            '{"name":"John","age":30}'
        );

        $result = MimeType::detect($this->filepath);

        self::assertDetectionResult(
            $result,
            EnumMimeType::JSON,
            'Moudarir\\MimeDetector\\Detector\\TextDetector'
        );
    }

    #[Test]
    public function itThrowsExceptionForMissingFile(): void
    {
        $filepath = $this->filepath . '-missing';

        self::expectException(MimeTypeException::class);

        MimeType::detect($filepath);
    }

    private static function assertDetectionResult(
        DetectionResult $result,
        EnumMimeType $expectedMimeType,
        string $expectedDetector
    ): void {
        self::assertSame($expectedMimeType, $result->mimeType());
        self::assertSame($expectedMimeType->value, $result->value());
        self::assertSame($expectedDetector, $result->detector());
    }
}
