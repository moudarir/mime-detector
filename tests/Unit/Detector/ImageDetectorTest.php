<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Tests\Unit\Detector;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Detector\ImageDetector;
use Moudarir\MimeDetector\Enum\MimeType;
use Moudarir\MimeDetector\FileResource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ImageDetectorTest extends TestCase
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

        $result = ImageDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(MimeType::GIF, $result->mimeType());
        self::assertSame(MimeType::GIF->value, $result->value());
        self::assertSame(ImageDetector::class, $result->detector());
    }

    #[Test]
    public function itDetectsJpeg(): void
    {
        file_put_contents($this->filepath, hex2bin('FFD8FF'));

        $result = ImageDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(MimeType::JPEG, $result->mimeType());
        self::assertSame(MimeType::JPEG->value, $result->value());
        self::assertSame(ImageDetector::class, $result->detector());
    }

    #[Test]
    public function itDetectsPng(): void
    {
        file_put_contents($this->filepath, hex2bin('89504E470D0A1A0A'));

        $result = ImageDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(MimeType::PNG, $result->mimeType());
        self::assertSame(MimeType::PNG->value, $result->value());
        self::assertSame(ImageDetector::class, $result->detector());
    }

    #[Test]
    public function itDetectsBmp(): void
    {
        file_put_contents($this->filepath, hex2bin('424D'));

        $result = ImageDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(MimeType::BMP, $result->mimeType());
        self::assertSame(MimeType::BMP->value, $result->value());
        self::assertSame(ImageDetector::class, $result->detector());
    }

    #[Test]
    public function itDetectsTiffWithLittleEndianSignature(): void
    {
        file_put_contents($this->filepath, hex2bin('49492A00'));

        $result = ImageDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(MimeType::TIFF, $result->mimeType());
        self::assertSame(MimeType::TIFF->value, $result->value());
        self::assertSame(ImageDetector::class, $result->detector());
    }

    #[Test]
    public function itDetectsTiffWithBigEndianSignature(): void
    {
        file_put_contents($this->filepath, hex2bin('4D4D002A'));

        $result = ImageDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(MimeType::TIFF, $result->mimeType());
        self::assertSame(MimeType::TIFF->value, $result->value());
        self::assertSame(ImageDetector::class, $result->detector());
    }

    #[Test]
    public function itDetectsIco(): void
    {
        file_put_contents($this->filepath, hex2bin('00000100'));

        $result = ImageDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(MimeType::ICO, $result->mimeType());
        self::assertSame(MimeType::ICO->value, $result->value());
        self::assertSame(ImageDetector::class, $result->detector());
    }

    #[Test]
    public function itReturnsNullForUnknownSignature(): void
    {
        file_put_contents($this->filepath, 'UNKNOWN');

        $result = ImageDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertNull($result);
    }
}
