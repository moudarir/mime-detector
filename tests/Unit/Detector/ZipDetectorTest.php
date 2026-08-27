<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Tests\Unit\Detector;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Detector\ZipDetector;
use Moudarir\MimeDetector\Enum\MimeType;
use Moudarir\MimeDetector\FileResource;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ZipArchive;

final class ZipDetectorTest extends TestCase
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

        self::assertDetectionResult($result, MimeType::ZIP);
    }

    #[Test]
    #[DataProvider('officeProvider')]
    public function itDetectsOfficeDocument(
        string $entry,
        MimeType $expectedMimeType
    ): void {
        $this->createZip([
            $entry => '<xml/>',
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, $expectedMimeType);
    }

    /**
     * @return iterable<string, array{string, MimeType}>
     */
    public static function officeProvider(): iterable
    {
        yield 'DOCX' => [
            'word/document.xml',
            MimeType::DOCX,
        ];

        yield 'XLSX' => [
            'xl/workbook.xml',
            MimeType::XLSX,
        ];

        yield 'PPTX' => [
            'ppt/presentation.xml',
            MimeType::PPTX,
        ];
    }

    #[Test]
    #[DataProvider('openDocumentProvider')]
    public function itDetectsOpenDocument(
        string $mimeType,
        MimeType $expectedMimeType
    ): void {
        $this->createZip([
            'META-INF/manifest.xml' => '<manifest/>',
            'mimetype' => $mimeType,
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, $expectedMimeType);
    }

    /**
     * @return iterable<string, array{string, MimeType}>
     */
    public static function openDocumentProvider(): iterable
    {
        yield 'ODT' => [
            MimeType::ODT->value,
            MimeType::ODT,
        ];

        yield 'ODS' => [
            MimeType::ODS->value,
            MimeType::ODS,
        ];

        yield 'ODP' => [
            MimeType::ODP->value,
            MimeType::ODP,
        ];
    }

    #[Test]
    public function itDetectsEpub(): void
    {
        $this->createZip([
            'META-INF/container.xml' => '<container/>',
            'mimetype' => MimeType::EPUB->value,
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, MimeType::EPUB);
    }

    #[Test]
    public function itDetectsApk(): void
    {
        $this->createZip([
            'AndroidManifest.xml' => 'manifest',
            'classes.dex' => 'dex',
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, MimeType::APK);
    }

    #[Test]
    public function itDetectsJar(): void
    {
        $this->createZip([
            'META-INF/MANIFEST.MF' => 'Manifest-Version: 1.0',
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, MimeType::JAR);
    }

    #[Test]
    public function itReturnsZipForUnknownZipContents(): void
    {
        $this->createZip([
            'unknown.dat' => 'content',
            'directory/file.dat' => 'content',
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, MimeType::ZIP);
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

        self::assertDetectionResult($result, MimeType::ZIP);
    }

    #[Test]
    public function itPrioritizesOfficeOverOtherZipFormats(): void
    {
        $this->createZip([
            'word/document.xml' => '<xml/>',
            'META-INF/manifest.xml' => '<manifest/>',
            'mimetype' => MimeType::ODT->value,
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, MimeType::DOCX);
    }

    #[Test]
    public function itRequiresManifestAndMimetypeForOpenDocument(): void
    {
        $this->createZip([
            'mimetype' => MimeType::ODT->value,
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, MimeType::ZIP);
    }

    #[Test]
    public function itRequiresContainerAndMimetypeForEpub(): void
    {
        $this->createZip([
            'mimetype' => MimeType::EPUB->value,
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, MimeType::ZIP);
    }

    #[Test]
    public function itRequiresBothAndroidEntriesForApk(): void
    {
        $this->createZip([
            'AndroidManifest.xml' => 'manifest',
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, MimeType::ZIP);
    }

    #[Test]
    public function itRequiresManifestForJar(): void
    {
        $this->createZip([
            'META-INF/OTHER.MF' => 'Manifest-Version: 1.0',
        ]);

        $result = $this->detect();

        self::assertDetectionResult($result, MimeType::ZIP);
    }

    private function detect(): ?DetectionResult
    {
        return ZipDetector::detect(
            FileResource::create($this->filepath)
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
        MimeType $expectedMimeType
    ): void {
        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame($expectedMimeType, $result->mimeType());
        self::assertSame($expectedMimeType->value, $result->value());
        self::assertSame(ZipDetector::class, $result->detector());
    }
}
