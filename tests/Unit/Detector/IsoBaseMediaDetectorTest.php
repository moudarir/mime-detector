<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Tests\Unit\Detector;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Detector\IsoBaseMediaDetector;
use Moudarir\MimeDetector\Enum\MimeType;
use Moudarir\MimeDetector\FileResource;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IsoBaseMediaDetectorTest extends TestCase
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
    #[DataProvider('isoBaseMediaProvider')]
    public function itDetectsIsoBaseMedia(
        string $brand,
        MimeType $expectedMimeType
    ): void {
        file_put_contents(
            $this->filepath,
            "\0\0\0\0ftyp" . $brand
        );

        $result = IsoBaseMediaDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame($expectedMimeType, $result->mimeType());
        self::assertSame($expectedMimeType->value, $result->value());
        self::assertSame(IsoBaseMediaDetector::class, $result->detector());
    }

    /**
     * @return iterable<string, array{string, MimeType}>
     */
    public static function isoBaseMediaProvider(): iterable
    {
        yield 'HEIF mif1' => ['mif1', MimeType::HEIF];
        yield 'HEIF msf1' => ['msf1', MimeType::HEIF];

        yield 'HEIC heic' => ['heic', MimeType::HEIC];
        yield 'HEIC heix' => ['heix', MimeType::HEIC];
        yield 'HEIC hevc' => ['hevc', MimeType::HEIC];
        yield 'HEIC hevx' => ['hevx', MimeType::HEIC];

        yield 'AVIF' => ['avif', MimeType::AVIF];

        yield 'MP4 isom' => ['isom', MimeType::MP4];
        yield 'MP4 iso2' => ['iso2', MimeType::MP4];
        yield 'MP4 iso3' => ['iso3', MimeType::MP4];
        yield 'MP4 iso4' => ['iso4', MimeType::MP4];
        yield 'MP4 iso5' => ['iso5', MimeType::MP4];
        yield 'MP4 iso6' => ['iso6', MimeType::MP4];
        yield 'MP4 mp41' => ['mp41', MimeType::MP4];
        yield 'MP4 mp42' => ['mp42', MimeType::MP4];
        yield 'MP4 avc1' => ['avc1', MimeType::MP4];
        yield 'MP4 dash' => ['dash', MimeType::MP4];

        yield 'M4A' => ["M4A ", MimeType::M4A];
        yield 'M4B' => ["M4B ", MimeType::M4A];

        yield '3GPP 3gp4' => ['3gp4', MimeType::THREE_GPP];
        yield '3GPP 3gp5' => ['3gp5', MimeType::THREE_GPP];
        yield '3GPP 3gp6' => ['3gp6', MimeType::THREE_GPP];
        yield '3GPP 3gp7' => ['3gp7', MimeType::THREE_GPP];

        yield 'QuickTime' => ['qt  ', MimeType::MOV];
    }

    #[Test]
    public function itReturnsNullWhenFtypSignatureIsMissing(): void
    {
        file_put_contents(
            $this->filepath,
            "\0\0\0\0xxxxisom"
        );

        $result = IsoBaseMediaDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertNull($result);
    }

    #[Test]
    public function itReturnsNullForUnknownBrand(): void
    {
        file_put_contents(
            $this->filepath,
            "\0\0\0\0ftypxxxx"
        );

        $result = IsoBaseMediaDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertNull($result);
    }
}