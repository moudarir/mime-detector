<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Tests\Unit\Detector;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Detector\ZipMimeDetector;
use Moudarir\MimeDetector\Enum\EnumMimeType;
use Moudarir\MimeDetector\FileInspector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ZipArchive;

final class ZipMimeDetectorTest extends TestCase
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
    public function itDetectsZip(): void
    {
        $this->createZip([
            'file.txt' => 'Hello',
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, EnumMimeType::ZIP);
    }

    #[Test]
    #[DataProvider('officeProvider')]
    public function itDetectsOfficeDocument(
        string $entry,
        EnumMimeType $expectedMimeType
    ): void {
        $this->createZip([
            $entry => '<xml/>',
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, $expectedMimeType);
    }

    /**
     * @return iterable<string, array{string, EnumMimeType}>
     */
    public static function officeProvider(): iterable
    {
        yield 'DOCX' => [
            'word/document.xml',
            EnumMimeType::DOCX,
        ];

        yield 'XLSX' => [
            'xl/workbook.xml',
            EnumMimeType::XLSX,
        ];

        yield 'PPTX' => [
            'ppt/presentation.xml',
            EnumMimeType::PPTX,
        ];
    }

    #[Test]
    #[DataProvider('openDocumentProvider')]
    public function itDetectsOpenDocument(
        string $mimeType,
        EnumMimeType $expectedMimeType
    ): void {
        $this->createZip([
            'META-INF/manifest.xml' => '<manifest/>',
            'mimetype' => $mimeType,
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, $expectedMimeType);
    }

    /**
     * @return iterable<string, array{string, EnumMimeType}>
     */
    public static function openDocumentProvider(): iterable
    {
        yield 'ODT' => [
            EnumMimeType::ODT->value,
            EnumMimeType::ODT,
        ];

        yield 'ODS' => [
            EnumMimeType::ODS->value,
            EnumMimeType::ODS,
        ];

        yield 'ODP' => [
            EnumMimeType::ODP->value,
            EnumMimeType::ODP,
        ];
    }

    #[Test]
    public function itDetectsEpub(): void
    {
        $this->createZip([
            'META-INF/container.xml' => '<container/>',
            'mimetype' => EnumMimeType::EPUB->value,
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, EnumMimeType::EPUB);
    }

    #[Test]
    public function itDetectsApk(): void
    {
        $this->createZip([
            'AndroidManifest.xml' => 'manifest',
            'classes.dex' => 'dex',
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, EnumMimeType::APK);
    }

    #[Test]
    public function itDetectsJar(): void
    {
        $this->createZip([
            'META-INF/MANIFEST.MF' => 'Manifest-Version: 1.0',
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, EnumMimeType::JAR);
    }

    #[Test]
    public function itReturnsZipForUnknownZipContents(): void
    {
        $this->createZip([
            'unknown.dat' => 'content',
            'directory/file.dat' => 'content',
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, EnumMimeType::ZIP);
    }

    #[Test]
    public function itReturnsNullWhenPkSignatureIsMissing(): void
    {
        file_put_contents(
            $this->filepath,
            'not a zip file'
        );

        $result = $this->detect();

        self::assertNull($result);
    }

    #[Test]
    public function itReturnsNullWhenPkSignatureIsPresentButZipIsInvalid(): void
    {
        file_put_contents(
            $this->filepath,
            hex2bin('504B0304') . 'invalid zip'
        );

        $result = $this->detect();

        self::assertNull($result);
    }

    #[Test]
    public function itDetectsEmptyZipArchive(): void
    {
        file_put_contents(
            $this->filepath,
            hex2bin('504B0506') . str_repeat("\0", 18)
        );

        $result = $this->detect();

        self::assertDetectionResult($result, EnumMimeType::ZIP);
    }

    #[Test]
    public function itPrioritizesOfficeOverOtherZipFormats(): void
    {
        $this->createZip([
            'word/document.xml' => '<xml/>',
            'META-INF/manifest.xml' => '<manifest/>',
            'mimetype' => EnumMimeType::ODT->value,
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, EnumMimeType::DOCX);
    }

    #[Test]
    public function itRequiresManifestAndMimetypeForOpenDocument(): void
    {
        $this->createZip([
            'mimetype' => EnumMimeType::ODT->value,
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, EnumMimeType::ZIP);
    }

    #[Test]
    public function itRequiresContainerAndMimetypeForEpub(): void
    {
        $this->createZip([
            'mimetype' => EnumMimeType::EPUB->value,
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, EnumMimeType::ZIP);
    }

    #[Test]
    public function itRequiresBothAndroidEntriesForApk(): void
    {
        $this->createZip([
            'AndroidManifest.xml' => 'manifest',
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, EnumMimeType::ZIP);
    }

    #[Test]
    public function itRequiresManifestForJar(): void
    {
        $this->createZip([
            'META-INF/OTHER.MF' => 'Manifest-Version: 1.0',
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, EnumMimeType::ZIP);
    }

    private function detect(): ?DetectionResult
    {
        return ZipMimeDetector::detect(
            FileInspector::create($this->filepath)
        );
    }

    /**
     * @param array<string, string> $entries
     */
    private function createZip(array $entries = []): void
    {
        unlink($this->filepath);

        $zip = new ZipArchive();

        if ($zip->open($this->filepath, ZipArchive::CREATE) !== true) {
            self::fail('Unable to create ZIP archive.');
        }

        foreach ($entries as $name => $content) {
            if (!$zip->addFromString($name, $content)) {
                $zip->close();

                self::fail("Unable to add ZIP entry `$name`.");
            }
        }

        $zip->close();
    }

    private static function assertDetectionResult(
        ?DetectionResult $result,
        EnumMimeType $expectedMimeType
    ): void {
        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame($expectedMimeType, $result->mimeType());
        self::assertSame($expectedMimeType->value, $result->value());
        self::assertSame(ZipMimeDetector::class, $result->detector());
    }
}
