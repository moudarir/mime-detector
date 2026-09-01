<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Tests\Unit\Detector;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Detector\MagicNumberDetector;
use Moudarir\MimeDetector\Enum\DetectorSource;
use Moudarir\MimeDetector\Enum\MimeType;
use Moudarir\MimeDetector\FileResource;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MagicNumberDetectorTest extends TestCase
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
    #[DataProvider('magicNumberProvider')]
    public function itDetectsMagicNumber(
        string $signature,
        MimeType $expectedMimeType
    ): void {
        file_put_contents(
            $this->filepath,
            hex2bin($signature)
        );

        $result = MagicNumberDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame($expectedMimeType, $result->mimeType());
        self::assertSame($expectedMimeType->value, $result->value());
        self::assertSame(DetectorSource::MAGIC_NUMBER, $result->detector());
    }

    /**
     * @return iterable<string, array{string, MimeType}>
     */
    public static function magicNumberProvider(): iterable
    {
        yield 'PDF' => ['25504446', MimeType::PDF];
        yield 'ELF' => ['7F454C46', MimeType::ELF];
        yield 'Windows executable' => ['4D5A', MimeType::WINDOWS_EXECUTABLE];
        yield 'MP3 ID3' => ['494433', MimeType::MP3];
        yield 'MP3 MPEG 2.5' => ['FFF3', MimeType::MP3];
        yield 'MP3 MPEG 2' => ['FFFB', MimeType::MP3];
        yield 'MP3 MPEG 1' => ['FFFA', MimeType::MP3];
        yield 'FLAC' => ['664C6143', MimeType::FLAC];
        yield 'OGG' => ['4F676753', MimeType::OGG];
        yield 'Matroska' => ['1A45DFA3', MimeType::MATROSKA];
    }

    #[Test]
    public function itDelegatesToRiffDetector(): void
    {
        file_put_contents(
            $this->filepath,
            'RIFF' . pack('V', 0) . 'WEBP'
        );

        $result = MagicNumberDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(MimeType::WEBP, $result->mimeType());
        self::assertSame(DetectorSource::RIFF, $result->detector());
    }

    #[Test]
    public function itDelegatesToImageMimeDetector(): void
    {
        file_put_contents(
            $this->filepath,
            hex2bin('89504E470D0A1A0A')
        );

        $result = MagicNumberDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(MimeType::PNG, $result->mimeType());
        self::assertSame(DetectorSource::IMAGE, $result->detector());
    }

    #[Test]
    public function itDelegatesToIsoBaseMediaDetector(): void
    {
        file_put_contents(
            $this->filepath,
            "\0\0\0\0ftypisom"
        );

        $result = MagicNumberDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(MimeType::MP4, $result->mimeType());
        self::assertSame(DetectorSource::ISO_BASE_MEDIA, $result->detector());
    }

    #[Test]
    public function itDelegatesToDiskImageDetector(): void
    {
        $content = str_repeat("\0", 32769)
            . 'CD001';

        file_put_contents($this->filepath, $content);

        $result = MagicNumberDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(MimeType::ISO, $result->mimeType());
        self::assertSame(DetectorSource::DISK_IMAGE, $result->detector());
    }

    #[Test]
    public function itReturnsNullForUnknownSignature(): void
    {
        file_put_contents(
            $this->filepath,
            'UNKNOWN'
        );

        $result = MagicNumberDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertNull($result);
    }
}