<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Tests\Unit\Detector;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Detector\DiskImageDetector;
use Moudarir\MimeDetector\Enum\DetectorSource;
use Moudarir\MimeDetector\Enum\MimeType;
use Moudarir\MimeDetector\FileResource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DiskImageDetectorTest extends TestCase
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
    public function itDetectsIsoImage(): void
    {
        $content = str_repeat("\0", 32769) . 'CD001';

        file_put_contents($this->filepath, $content);

        $inspector = FileResource::create($this->filepath);
        $result = DiskImageDetector::detect($inspector);

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(MimeType::ISO, $result->mimeType());
        self::assertSame(MimeType::ISO->value, $result->value());
        self::assertSame(DetectorSource::DISK_IMAGE, $result->detector());
    }

    #[Test]
    public function itDoesNotDetectIsoWhenSignatureIsAtWrongOffset(): void
    {
        $content = str_repeat("\0", 32768) . 'CD001';

        file_put_contents($this->filepath, $content);

        $inspector = FileResource::create($this->filepath);
        $result = DiskImageDetector::detect($inspector);

        self::assertNull($result);
    }

    #[Test]
    public function itDoesNotDetectIsoWhenSignatureIsMissing(): void
    {
        $content = str_repeat("\0", 32769) . 'XXXXX';

        file_put_contents($this->filepath, $content);

        $inspector = FileResource::create($this->filepath);
        $result = DiskImageDetector::detect($inspector);

        self::assertNull($result);
    }

    #[Test]
    public function itDoesNotDetectIsoWhenFileIsTooSmall(): void
    {
        $content = str_repeat("\0", 32768);

        file_put_contents($this->filepath, $content);

        $inspector = FileResource::create($this->filepath);
        $result = DiskImageDetector::detect($inspector);

        self::assertNull($result);
    }

    #[Test]
    public function itDetectsDmgImage(): void
    {
        $content = str_repeat("\0", 1024).'koly'.str_repeat("\0", 508);

        file_put_contents($this->filepath, $content);

        $inspector = FileResource::create($this->filepath);
        $result = DiskImageDetector::detect($inspector);

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame(MimeType::DMG, $result->mimeType());
        self::assertSame(MimeType::DMG->value, $result->value());
        self::assertSame(DetectorSource::DISK_IMAGE, $result->detector());
    }

    #[Test]
    public function itDoesNotDetectDmgWhenSignatureIsAtWrongOffset(): void
    {
        $content = 'koly' . str_repeat("\0", 1024);

        file_put_contents($this->filepath, $content);

        $inspector = FileResource::create($this->filepath);
        $result = DiskImageDetector::detect($inspector);

        self::assertNull($result);
    }

    #[Test]
    public function itDoesNotDetectDmgWhenSignatureIsMissing(): void
    {
        $content = str_repeat("\0", 1024) . str_repeat("\0", 508) . 'XXXX';

        file_put_contents($this->filepath, $content);

        $inspector = FileResource::create($this->filepath);
        $result = DiskImageDetector::detect($inspector);

        self::assertNull($result);
    }

    #[Test]
    public function itDoesNotDetectDmgWhenFileIsSmallerThanTrailer(): void
    {
        $content = str_repeat("\0", 511);

        file_put_contents($this->filepath, $content);

        $inspector = FileResource::create($this->filepath);
        $result = DiskImageDetector::detect($inspector);

        self::assertNull($result);
    }

    #[Test]
    public function itDoesNotDetectDmgWhenFileIsExactlyTrailerSizeWithoutSignature(): void
    {
        $content = str_repeat("\0", 512);

        file_put_contents($this->filepath, $content);

        $inspector = FileResource::create($this->filepath);
        $result = DiskImageDetector::detect($inspector);

        self::assertNull($result);
    }
}
