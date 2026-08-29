<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Tests\Unit;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Enum\DetectorSource;
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

        self::assertSame(DetectorSource::FILE_INFO, $result->detector());
    }

    #[Test]
    public function itReturnsFilesize(): void
    {
        $content = '%PDF-1.7';

        file_put_contents($this->filepath, $content);

        $result = $this->createResult(MimeType::PDF);

        self::assertSame(strlen($content), $result->metadata()->filesize());
    }

    #[Test]
    public function itReturnsPathInfoForFileWithExtension(): void
    {
        $filepath = $this->filepath . '.pdf';

        rename($this->filepath, $filepath);
        $this->filepath = $filepath;

        file_put_contents($this->filepath, '%PDF-1.7');

        $result = $this->createResult(MimeType::PDF);
        $metadata = $result->metadata();

        self::assertSame(
            [
                'dirname' => dirname($this->filepath),
                'basename' => basename($this->filepath),
                'extension' => 'pdf',
                'filename' => pathinfo($this->filepath, PATHINFO_FILENAME),
            ],
            [
                'dirname' => $metadata->dirname(),
                'basename' => $metadata->basename(),
                'extension' => $metadata->extension(),
                'filename' => $metadata->filename(),
            ]
        );
    }

    #[Test]
    public function itReturnsEmptyExtensionForFileWithoutExtension(): void
    {
        $result = $this->createResult(MimeType::PDF);
        $metadata = $result->metadata();

        self::assertSame(
            [
                dirname($this->filepath),
                basename($this->filepath),
                '',
                basename($this->filepath),
            ],
            [
                $metadata->dirname(),
                $metadata->basename(),
                $metadata->extension(),
                $metadata->filename(),
            ]
        );
    }

    private function createResult(MimeType $mimeType): DetectionResult
    {
        return DetectionResult::create(
            FileResource::create($this->filepath),
            $mimeType,
            DetectorSource::FILE_INFO
        );
    }
}