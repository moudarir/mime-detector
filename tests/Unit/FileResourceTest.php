<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Tests\Unit;

use Moudarir\MimeDetector\Exceptions\MimeDetectorException;
use Moudarir\MimeDetector\FileResource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FileResourceTest extends TestCase
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
    public function itCreatesInspectorForExistingReadableFile(): void
    {
        file_put_contents($this->filepath, 'ABCDEFGHIJ');

        $inspector = FileResource::create($this->filepath);

        self::assertSame($this->filepath, $inspector->path());
    }

    #[Test]
    public function itThrowsExceptionForNonExistingFile(): void
    {
        $filepath = $this->filepath . '-missing';

        $this->expectException(MimeDetectorException::class);

        FileResource::create($filepath);
    }

    #[Test]
    public function itThrowsExceptionForDirectory(): void
    {
        $directory = sys_get_temp_dir();

        $this->expectException(MimeDetectorException::class);

        FileResource::create($directory);
    }

    #[Test]
    public function itReturnsFilesize(): void
    {
        $content = 'ABCDEFGHIJ';

        file_put_contents($this->filepath, $content);

        $inspector = FileResource::create($this->filepath);

        self::assertSame(strlen($content), $inspector->filesize());
    }

    #[Test]
    public function itCachesFileSize(): void
    {
        file_put_contents(
            $this->filepath,
            '12345'
        );

        $inspector = FileResource::create($this->filepath);

        self::assertSame(5, $inspector->filesize());

        file_put_contents(
            $this->filepath,
            '1234567890'
        );

        self::assertSame(
            5,
            $inspector->filesize()
        );
    }

    #[Test]
    public function itReturnsHeaderAsHexadecimal(): void
    {
        file_put_contents($this->filepath, 'ABCDEFGHIJ');

        $inspector = FileResource::create($this->filepath);

        self::assertSame(
            '4142434445464748494A',
            $inspector->hexHeader()
        );
    }

    #[Test]
    public function itDetectsHexadecimalSignatureAtStartOfHeader(): void
    {
        file_put_contents($this->filepath, 'ABCDEFGHIJ');

        $inspector = FileResource::create($this->filepath);

        self::assertTrue($inspector->startsWithHex('414243'));
        self::assertTrue($inspector->startsWithHex('4142434445'));
    }

    #[Test]
    public function itDoesNotDetectHexadecimalSignatureWhenHeaderDoesNotStartWithIt(): void
    {
        file_put_contents($this->filepath, 'ABCDEFGHIJ');

        $inspector = FileResource::create($this->filepath);

        self::assertFalse($inspector->startsWithHex('444546'));
    }

    #[Test]
    public function itAcceptsLowercaseHexadecimalSignatures(): void
    {
        file_put_contents($this->filepath, 'ABCDEFGHIJ');

        $inspector = FileResource::create($this->filepath);

        self::assertTrue($inspector->startsWithHex('414243'));
        self::assertTrue($inspector->startsWithHex('414243444546'));
    }

    #[Test]
    public function itReturnsBytesFromHeader(): void
    {
        file_put_contents($this->filepath, 'ABCDEFGHIJ');

        $inspector = FileResource::create($this->filepath);

        self::assertSame('ABC', $inspector->bytes(0, 3));
        self::assertSame('DEF', $inspector->bytes(3, 3));
        self::assertSame('HIJ', $inspector->bytes(7, 3));
    }

    #[Test]
    public function itReadsHexadecimalBytesAtGivenOffset(): void
    {
        file_put_contents($this->filepath, 'ABCDEFGHIJ');

        $inspector = FileResource::create($this->filepath);

        self::assertSame('414243', $inspector->readHex(0, 3));
        self::assertSame('444546', $inspector->readHex(3, 3));
        self::assertSame('48494A', $inspector->readHex(7, 3));
    }

    #[Test]
    public function itReturnsNullWhenRequestedBytesAreNotAvailable(): void
    {
        file_put_contents($this->filepath, 'ABCDEFGHIJ');

        $inspector = FileResource::create($this->filepath);

        self::assertNull($inspector->readHex(8, 3));
        self::assertNull($inspector->readHex(10, 1));
    }

    #[Test]
    public function itReturnsNullForNegativeReadHexArguments(): void
    {
        file_put_contents($this->filepath, 'ABCDEFGHIJ');

        $inspector = FileResource::create($this->filepath);

        self::assertNull($inspector->readHex(-1, 1));
        self::assertNull($inspector->readHex(0, -1));
    }

    #[Test]
    public function itDetectsRiffType(): void
    {
        $content = 'RIFF' . '1234' . 'WAVE' . str_repeat("\0", 10);

        file_put_contents($this->filepath, $content);

        $inspector = FileResource::create($this->filepath);

        self::assertSame('WAVE', $inspector->riffType());
    }

    #[Test]
    public function itReturnsNullWhenFileIsNotRiff(): void
    {
        file_put_contents($this->filepath, 'ABCDEFGHIJ');

        $inspector = FileResource::create($this->filepath);

        self::assertNull($inspector->riffType());
    }

    #[Test]
    public function itDetectsIsoBaseMediaBrand(): void
    {
        $content = "\0\0\0\0ftypisom" . str_repeat("\0", 10);

        file_put_contents($this->filepath, $content);

        $inspector = FileResource::create($this->filepath);

        self::assertSame('isom', $inspector->isoBaseMediaBrand());
    }

    #[Test]
    public function itReturnsNullWhenFileIsNotIsoBaseMedia(): void
    {
        file_put_contents($this->filepath, 'ABCDEFGHIJ');

        $inspector = FileResource::create($this->filepath);

        self::assertNull($inspector->isoBaseMediaBrand());
    }

    #[Test]
    public function itReturnsFileInfoMimeType(): void
    {
        file_put_contents($this->filepath, "<?php echo 'test';");

        $inspector = FileResource::create($this->filepath);

        self::assertIsString($inspector->fileInfoMime());
    }
}
