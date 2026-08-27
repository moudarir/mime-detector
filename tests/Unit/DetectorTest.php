<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Tests\Unit;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Enum\DetectorSource;
use Moudarir\MimeDetector\Enum\MimeType;
use Moudarir\MimeDetector\Exceptions\MimeDetectorException;
use Moudarir\MimeDetector\Detector;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ZipArchive;

final class DetectorTest extends TestCase
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

        $result = Detector::detect($this->filepath);

        self::assertDetectionResult(
            $result,
            MimeType::PDF,
            DetectorSource::MAGIC_NUMBER
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

        $result = Detector::detect($this->filepath);

        self::assertDetectionResult(
            $result,
            MimeType::ZIP,
            DetectorSource::ZIP
        );
    }

    #[Test]
    public function itDetectsPlainTextThroughFileInfo(): void
    {
        file_put_contents(
            $this->filepath,
            'This is a plain text document.'
        );

        $result = Detector::detect($this->filepath);

        self::assertDetectionResult(
            $result,
            MimeType::TEXT_PLAIN,
            DetectorSource::FILE_INFO
        );
    }

    #[Test]
    public function itDetectsOctetStreamThroughFileInfo(): void
    {
        file_put_contents(
            $this->filepath,
            str_repeat("\0", 32)
        );

        $result = Detector::detect($this->filepath);

        self::assertDetectionResult(
            $result,
            MimeType::OCTET_STREAM,
            DetectorSource::FILE_INFO
        );
    }

    #[Test]
    public function itDetectsJsonThroughTextDetector(): void
    {
        file_put_contents(
            $this->filepath,
            '{"name":"John","age":30}'
        );

        $result = Detector::detect($this->filepath);

        self::assertDetectionResult(
            $result,
            MimeType::JSON,
            DetectorSource::TEXT
        );
    }

    #[Test]
    public function itThrowsExceptionForMissingFile(): void
    {
        $filepath = $this->filepath . '-missing';

        self::expectException(MimeDetectorException::class);

        Detector::detect($filepath);
    }

    private static function assertDetectionResult(
        DetectionResult $result,
        MimeType        $expectedMimeType,
        DetectorSource  $expectedDetector
    ): void {
        self::assertSame($expectedMimeType, $result->mimeType());
        self::assertSame($expectedMimeType->value, $result->value());
        self::assertSame($expectedDetector, $result->detector());
    }
}
