<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Tests\Unit\Detector;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Detector\DiskImageDetector;
use Moudarir\MimeDetector\Detector\ImageMimeDetector;
use Moudarir\MimeDetector\Detector\IsoBaseMediaDetector;
use Moudarir\MimeDetector\Detector\MagicNumberMimeDetector;
use Moudarir\MimeDetector\Detector\RiffDetector;
use Moudarir\MimeDetector\Enum\EnumMimeType;
use Moudarir\MimeDetector\FileInspector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MagicNumberMimeDetectorTest extends TestCase
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
        EnumMimeType $expectedMimeType
    ): void {
        file_put_contents(
            $this->filepath,
            hex2bin($signature)
        );

        $result = MagicNumberMimeDetector::detect(
            FileInspector::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame($expectedMimeType, $result->mimeType());
        self::assertSame($expectedMimeType->value, $result->value());
        self::assertSame(MagicNumberMimeDetector::class, $result->detector());
    }

    /**
     * @return iterable<string, array{string, EnumMimeType}>
     */
    public static function magicNumberProvider(): iterable
    {
        yield 'PDF' => ['25504446', EnumMimeType::PDF];
        yield 'ELF' => ['7F454C46', EnumMimeType::ELF];
        yield 'Windows executable' => ['4D5A', EnumMimeType::WINDOWS_EXECUTABLE];
        yield 'MP3 ID3' => ['494433', EnumMimeType::MP3];
        yield 'MP3 MPEG 2.5' => ['FFF3', EnumMimeType::MP3];
        yield 'MP3 MPEG 2' => ['FFFB', EnumMimeType::MP3];
        yield 'MP3 MPEG 1' => ['FFFA', EnumMimeType::MP3];
        yield 'FLAC' => ['664C6143', EnumMimeType::FLAC];
        yield 'OGG' => ['4F676753', EnumMimeType::OGG];
        yield 'Matroska' => ['1A45DFA3', EnumMimeType::MATROSKA];
    }

    #[Test]
    public function itDelegatesToRiffDetector(): void
    {
        file_put_contents(
            $this->filepath,
            'RIFF' . pack('V', 0) . 'WEBP'
        );

        $result = MagicNumberMimeDetector::detect(
            FileInspector::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(EnumMimeType::WEBP, $result->mimeType());
        self::assertSame(RiffDetector::class, $result->detector());
    }

    #[Test]
    public function itDelegatesToImageMimeDetector(): void
    {
        file_put_contents(
            $this->filepath,
            hex2bin('89504E470D0A1A0A')
        );

        $result = MagicNumberMimeDetector::detect(
            FileInspector::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(EnumMimeType::PNG, $result->mimeType());
        self::assertSame(ImageMimeDetector::class, $result->detector());
    }

    #[Test]
    public function itDelegatesToIsoBaseMediaDetector(): void
    {
        file_put_contents(
            $this->filepath,
            "\0\0\0\0ftypisom"
        );

        $result = MagicNumberMimeDetector::detect(
            FileInspector::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(EnumMimeType::MP4, $result->mimeType());
        self::assertSame(IsoBaseMediaDetector::class, $result->detector());
    }

    #[Test]
    public function itDelegatesToDiskImageDetector(): void
    {
        $content = str_repeat("\0", 32769)
            . 'CD001';

        file_put_contents($this->filepath, $content);

        $result = MagicNumberMimeDetector::detect(
            FileInspector::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(EnumMimeType::ISO, $result->mimeType());
        self::assertSame(DiskImageDetector::class, $result->detector());
    }

    #[Test]
    public function itReturnsNullForUnknownSignature(): void
    {
        file_put_contents(
            $this->filepath,
            'UNKNOWN'
        );

        $result = MagicNumberMimeDetector::detect(
            FileInspector::create($this->filepath)
        );

        self::assertNull($result);
    }
}