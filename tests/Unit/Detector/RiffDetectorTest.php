<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Tests\Unit\Detector;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Detector\RiffDetector;
use Moudarir\MimeDetector\Enum\DetectorSource;
use Moudarir\MimeDetector\Enum\MimeType;
use Moudarir\MimeDetector\FileResource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RiffDetectorTest extends TestCase
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
    public function itDetectsWebp(): void
    {
        file_put_contents(
            $this->filepath,
            'RIFF' . pack('V', 0) . 'WEBP'
        );

        $result = RiffDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(MimeType::WEBP, $result->mimeType());
        self::assertSame(MimeType::WEBP->value, $result->value());
        self::assertSame(DetectorSource::RIFF, $result->detector());
    }

    #[Test]
    public function itDetectsWav(): void
    {
        file_put_contents(
            $this->filepath,
            'RIFF' . pack('V', 0) . 'WAVE'
        );

        $result = RiffDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(MimeType::WAV, $result->mimeType());
        self::assertSame(MimeType::WAV->value, $result->value());
        self::assertSame(DetectorSource::RIFF, $result->detector());
    }

    #[Test]
    public function itDetectsAvi(): void
    {
        file_put_contents(
            $this->filepath,
            'RIFF' . pack('V', 0) . 'AVI '
        );

        $result = RiffDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(MimeType::AVI, $result->mimeType());
        self::assertSame(MimeType::AVI->value, $result->value());
        self::assertSame(DetectorSource::RIFF, $result->detector());
    }

    #[Test]
    public function itReturnsNullForUnknownRiffType(): void
    {
        file_put_contents(
            $this->filepath,
            'RIFF' . pack('V', 0) . 'TEST'
        );

        $result = RiffDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertNull($result);
    }

    #[Test]
    public function itReturnsNullWhenRiffSignatureIsMissing(): void
    {
        file_put_contents(
            $this->filepath,
            'XXXX' . pack('V', 0) . 'WEBP'
        );

        $result = RiffDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertNull($result);
    }
}
