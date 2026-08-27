<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Tests\Unit\Detector;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Detector\FileInfoDetector;
use Moudarir\MimeDetector\Enum\DetectorSource;
use Moudarir\MimeDetector\Enum\MimeType;
use Moudarir\MimeDetector\FileResource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FileInfoDetectorTest extends TestCase
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
    public function itDetectsPdf(): void
    {
        file_put_contents(
            $this->filepath,
            "%PDF-1.7\n"
        );

        $result = $this->detect();

        self::assertDetectionResult($result, MimeType::PDF);
    }

    #[Test]
    public function itDetectsPlainText(): void
    {
        file_put_contents(
            $this->filepath,
            'This is plain text.'
        );

        $result = $this->detect();

        self::assertDetectionResult($result, MimeType::TEXT_PLAIN);
    }

    #[Test]
    public function itDetectsPhp(): void
    {
        file_put_contents(
            $this->filepath,
            '<?php echo "Hello";'
        );

        $result = $this->detect();

        self::assertDetectionResult($result, MimeType::PHP);
    }

    #[Test]
    public function itReturnsNullForUnsupportedMimeType(): void
    {
        $filepath = dirname(__DIR__, 2) . '/Fixtures/Unsupported/filename.sh';

        $inspector = FileResource::create($filepath);

        self::assertSame('text/x-shellscript', $inspector->fileInfoMime());

        $result = FileInfoDetector::detect($inspector);

        self::assertNull($result);
    }

    #[Test]
    public function itDetectsJsonUsingFileInfo(): void
    {
        file_put_contents(
            $this->filepath,
            '{"name":"John"}'
        );

        $result = $this->detect();

        self::assertDetectionResult($result, MimeType::JSON);
    }

    private function detect(): ?DetectionResult
    {
        return FileInfoDetector::detect(
            FileResource::create($this->filepath)
        );
    }

    private static function assertDetectionResult(
        ?DetectionResult $result,
        MimeType $expectedMimeType
    ): void {
        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame($expectedMimeType, $result->mimeType());
        self::assertSame($expectedMimeType->value, $result->value());
        self::assertSame(DetectorSource::FILE_INFO, $result->detector());
    }
}
