<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Tests\Integration;

use Moudarir\MimeDetector\Detector;
use Moudarir\MimeDetector\Enum\DetectorSource;
use Moudarir\MimeDetector\Enum\MimeType;
use Moudarir\MimeDetector\Exceptions\MimeDetectorException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DetectorTest extends TestCase
{

    #[Test]
    #[DataProvider('detectionProvider')]
    public function detectsMimeType(
        string $fixture,
        MimeType $expectedMimeType,
        DetectorSource $expectedDetector,
    ): void {
        $result = Detector::detect($this->fixture($fixture));

        self::assertSame($expectedMimeType, $result->mimeType());
        self::assertSame($expectedMimeType->value, $result->value());
        self::assertSame($expectedDetector, $result->detector());
        self::assertSame($expectedDetector->value, $result->detectorValue());
    }

    #[Test]
    public function detectionResultContainsFileMetadata(): void
    {
        $path = $this->fixture('document.pdf');

        $result = Detector::detect($path);

        self::assertSame(filesize($path), $result->filesize());
        self::assertSame(dirname($path), $result->dirname());
        self::assertSame('document.pdf', $result->basename());
        self::assertSame('document', $result->filename());
        self::assertSame('pdf', $result->extension());

        self::assertSame(
            [
                'dirname' => dirname($path),
                'basename' => 'document.pdf',
                'extension' => 'pdf',
                'filename' => 'document',
            ],
            $result->pathInfo()
        );
    }

    #[Test]
    public function detectsPlainTextWithoutKnownExtension(): void
    {
        $result = Detector::detect($this->fixture('LICENCE'));

        self::assertSame(MimeType::TEXT_PLAIN, $result->mimeType());
        self::assertSame(DetectorSource::FILE_INFO, $result->detector());
    }

    #[Test]
    public function throwsExceptionForNonExistingFile(): void
    {
        $this->expectException(MimeDetectorException::class);

        Detector::detect($this->fixture('does-not-exist'));
    }

    #[Test]
    public function detectsElfBinary(): void
    {
        $result = Detector::detect($this->fixture('resource.bin'));

        self::assertSame(MimeType::ELF, $result->mimeType());
        self::assertSame(DetectorSource::MAGIC_NUMBER, $result->detector());
    }

    #[Test]
    public function fallsBackForUnsupportedFileType(): void
    {
        $result = Detector::detect($this->fixture('Unsupported/filename.sh'));

        self::assertSame(MimeType::OCTET_STREAM, $result->mimeType());
        self::assertSame(DetectorSource::FALLBACK, $result->detector());
    }

    /**
     * @return array<string, array{
     *     fixture: string,
     *     mimeType: MimeType,
     *     detector: DetectorSource
     * }>
     */
    public static function detectionProvider(): array
    {
        return [
            'PHP source' => [
                'fixture' => 'code.php',
                'expectedMimeType' => MimeType::PHP,
                'expectedDetector' => DetectorSource::TEXT,
            ],
            'PDF document' => [
                'fixture' => 'document.pdf',
                'expectedMimeType' => MimeType::PDF,
                'expectedDetector' => DetectorSource::MAGIC_NUMBER,
            ],
            'JPEG image' => [
                'fixture' => 'image.jpeg',
                'expectedMimeType' => MimeType::JPEG,
                'expectedDetector' => DetectorSource::IMAGE,
            ],
            'plain text' => [
                'fixture' => 'text.txt',
                'expectedMimeType' => MimeType::TEXT_PLAIN,
                'expectedDetector' => DetectorSource::FILE_INFO,
            ],
            'QuickTime video' => [
                'fixture' => 'video.mov',
                'expectedMimeType' => MimeType::MOV,
                'expectedDetector' => DetectorSource::ISO_BASE_MEDIA,
            ],
            'CSV file' => [
                'fixture' => 'data.csv',
                'expectedMimeType' => MimeType::CSV,
                'expectedDetector' => DetectorSource::SEPARATED_VALUES,
            ],
        ];
    }

    private function fixture(string $filename): string
    {
        return dirname(__DIR__) . '/Fixtures/' . $filename;
    }
}