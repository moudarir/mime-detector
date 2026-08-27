<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Tests\Unit;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Detector\FileInfoDetector;
use Moudarir\MimeDetector\Enum\MimeType;
use Moudarir\MimeDetector\FileResource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DetectionResultTest extends TestCase
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
    public function itReturnsMimeType(): void
    {
        $result = $this->createResult(MimeType::PDF);

        self::assertSame(
            MimeType::PDF,
            $result->mimeType()
        );
    }

    #[Test]
    public function itReturnsMimeTypeValue(): void
    {
        $result = $this->createResult(MimeType::PDF);

        self::assertSame(
            MimeType::PDF->value,
            $result->value()
        );
    }

    #[Test]
    public function itReturnsDetector(): void
    {
        $result = $this->createResult(MimeType::PDF);

        self::assertSame(
            FileInfoDetector::class,
            $result->detector()
        );
    }

    #[Test]
    public function itReturnsFilesize(): void
    {
        $content = '%PDF-1.7';

        file_put_contents($this->filepath, $content);

        $result = $this->createResult(MimeType::PDF);

        self::assertSame(
            strlen($content),
            $result->filesize()
        );
    }

    #[Test]
    public function itReturnsPathInfoForFileWithExtension(): void
    {
        $filepath = $this->filepath . '.pdf';

        rename($this->filepath, $filepath);
        $this->filepath = $filepath;

        file_put_contents($this->filepath, '%PDF-1.7');

        $result = $this->createResult(MimeType::PDF);

        self::assertSame(
            [
                'dirname' => dirname($this->filepath),
                'basename' => basename($this->filepath),
                'extension' => 'pdf',
                'filename' => pathinfo($this->filepath, PATHINFO_FILENAME),
            ],
            $result->pathInfo()
        );
    }

    #[Test]
    public function itReturnsEmptyExtensionForFileWithoutExtension(): void
    {
        $result = $this->createResult(MimeType::PDF);

        $pathInfo = $result->pathInfo();

        self::assertSame(dirname($this->filepath), $pathInfo['dirname']);
        self::assertSame(basename($this->filepath), $pathInfo['basename']);
        self::assertSame('', $pathInfo['extension']);
        self::assertSame(basename($this->filepath), $pathInfo['filename']);
    }

    private function createResult(MimeType $mimeType): DetectionResult
    {
        return DetectionResult::create(
            FileResource::create($this->filepath),
            $mimeType,
            FileInfoDetector::class
        );
    }
}