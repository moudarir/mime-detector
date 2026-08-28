<?php

declare(strict_types=1);

namespace Moudarir\MimeDetector\Tests\Unit\Detector;

use Moudarir\MimeDetector\DetectionResult;
use Moudarir\MimeDetector\Detector\SeparatedValuesDetector;
use Moudarir\MimeDetector\Enum\DetectorSource;
use Moudarir\MimeDetector\Enum\MimeType;
use Moudarir\MimeDetector\FileResource;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SeparatedValuesDetectorTest extends TestCase
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
    #[DataProvider('separatorProvider')]
    public function itDetectsSeparatedValues(
        string $content,
        MimeType $expectedMimeType,
    ): void {
        file_put_contents($this->filepath, $content);

        $result = SeparatedValuesDetector::detect(
            FileResource::create($this->filepath)
        );

        self::assertInstanceOf(DetectionResult::class, $result);
        self::assertSame($expectedMimeType, $result->mimeType());
        self::assertSame($expectedMimeType->value, $result->value());
        self::assertSame(
            DetectorSource::SEPARATED_VALUES,
            $result->detector()
        );
        self::assertSame(
            DetectorSource::SEPARATED_VALUES->value,
            $result->detectorValue()
        );
    }

    #[Test]
    public function itDoesNotDetectPlainTextContainingCommas(): void
    {
        file_put_contents(
            $this->filepath,
            <<<'TEXT'
            Hello World!

            Lorem, ipsum dolor sit amet consectetur, adipisicing elit.

            Lorem, ipsum dolor sit amet consectetur, adipisicing elit.
            TEXT
        );

        $result = $this->detect();

        self::assertNull($result);
    }

    #[Test]
    public function itDoesNotDetectSingleColumnData(): void
    {
        file_put_contents(
            $this->filepath,
            <<<'TEXT'
            Name
            John
            Doe
            Dolor
            TEXT
        );

        $result = $this->detect();

        self::assertNull($result);
    }

    #[Test]
    public function itDoesNotDetectInconsistentColumnCount(): void
    {
        file_put_contents(
            $this->filepath,
            <<<'TEXT'
            name,age,city
            John,23,Paris
            Doe,32
            Dolor,33,Casablanca
            TEXT
        );

        $result = $this->detect();

        self::assertNull($result);
    }

    #[Test]
    public function itDoesNotDetectWhenOnlyOneSeparatedRowExists(): void
    {
        file_put_contents(
            $this->filepath,
            'name,age,city'
        );

        $result = $this->detect();

        self::assertNull($result);
    }

    /**
     * @return array<string, array{
     *     content: string,
     *     expectedMimeType: MimeType
     * }>
     */
    public static function separatorProvider(): array
    {
        return [
            'CSV with comma' => [
                'content' => <<<'CSV'
                name,age,city
                John,23,Paris
                Doe,32,Lyon
                Dolor,33,Casablanca
                CSV,
                'expectedMimeType' => MimeType::CSV,
            ],
            'CSV with semicolon' => [
                'content' => <<<'CSV'
                name;age;city
                John;23;Paris
                Doe;32;Lyon
                Dolor;33;Casablanca
                CSV,
                'expectedMimeType' => MimeType::CSV,
            ],
            'CSV with pipe' => [
                'content' => <<<'CSV'
                name|age|city
                John|23|Paris
                Doe|32|Lyon
                Dolor|33|Casablanca
                CSV,
                'expectedMimeType' => MimeType::CSV,
            ],
            'TSV with tab' => [
                'content' => "name\tage\tcity\nJohn\t23\tParis\nDoe\t32\tLyon\nDolor\t33\tCasablanca",
                'expectedMimeType' => MimeType::TSV,
            ],
        ];
    }

    private function detect(): ?DetectionResult
    {
        return SeparatedValuesDetector::detect(
            FileResource::create($this->filepath)
        );
    }
}
