<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Tests\Unit\Detector;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Detector\ImageMimeDetector;
use Moudarir\MimeDetector\Enum\EnumMimeType;
use Moudarir\MimeDetector\FileInspector;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ImageMimeDetectorTest extends TestCase
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
        if (isset($this->filepath) && is_file($this->filepath)) {
            unlink($this->filepath);
        }
    }

    #[Test]
    public function itDetectsGif(): void
    {
        file_put_contents($this->filepath, hex2bin('47494638'));

        $result = ImageMimeDetector::detect(
            FileInspector::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(EnumMimeType::GIF, $result->mimeType());
        self::assertSame(EnumMimeType::GIF->value, $result->value());
        self::assertSame(ImageMimeDetector::class, $result->detector());
    }

    #[Test]
    public function itDetectsJpeg(): void
    {
        file_put_contents($this->filepath, hex2bin('FFD8FF'));

        $result = ImageMimeDetector::detect(
            FileInspector::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(EnumMimeType::JPEG, $result->mimeType());
        self::assertSame(EnumMimeType::JPEG->value, $result->value());
        self::assertSame(ImageMimeDetector::class, $result->detector());
    }

    #[Test]
    public function itDetectsPng(): void
    {
        file_put_contents($this->filepath, hex2bin('89504E470D0A1A0A'));

        $result = ImageMimeDetector::detect(
            FileInspector::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(EnumMimeType::PNG, $result->mimeType());
        self::assertSame(EnumMimeType::PNG->value, $result->value());
        self::assertSame(ImageMimeDetector::class, $result->detector());
    }

    #[Test]
    public function itDetectsBmp(): void
    {
        file_put_contents($this->filepath, hex2bin('424D'));

        $result = ImageMimeDetector::detect(
            FileInspector::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(EnumMimeType::BMP, $result->mimeType());
        self::assertSame(EnumMimeType::BMP->value, $result->value());
        self::assertSame(ImageMimeDetector::class, $result->detector());
    }

    #[Test]
    public function itDetectsTiffWithLittleEndianSignature(): void
    {
        file_put_contents($this->filepath, hex2bin('49492A00'));

        $result = ImageMimeDetector::detect(
            FileInspector::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(EnumMimeType::TIFF, $result->mimeType());
        self::assertSame(EnumMimeType::TIFF->value, $result->value());
        self::assertSame(ImageMimeDetector::class, $result->detector());
    }

    #[Test]
    public function itDetectsTiffWithBigEndianSignature(): void
    {
        file_put_contents($this->filepath, hex2bin('4D4D002A'));

        $result = ImageMimeDetector::detect(
            FileInspector::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(EnumMimeType::TIFF, $result->mimeType());
        self::assertSame(EnumMimeType::TIFF->value, $result->value());
        self::assertSame(ImageMimeDetector::class, $result->detector());
    }

    #[Test]
    public function itDetectsIco(): void
    {
        file_put_contents($this->filepath, hex2bin('00000100'));

        $result = ImageMimeDetector::detect(
            FileInspector::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(EnumMimeType::ICO, $result->mimeType());
        self::assertSame(EnumMimeType::ICO->value, $result->value());
        self::assertSame(ImageMimeDetector::class, $result->detector());
    }

    #[Test]
    public function itReturnsNullForUnknownSignature(): void
    {
        file_put_contents($this->filepath, 'UNKNOWN');

        $result = ImageMimeDetector::detect(
            FileInspector::create($this->filepath)
        );

        self::assertNull($result);
    }
}
