<?php

namespace Jason\Captcha\Tests;

use Illuminate\Filesystem\Filesystem;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Jason\Captcha\Image\ImageCreator;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

#[RequiresPhpExtension('gd')]
class ImageCreatorTest extends PHPUnitTestCase
{
    protected ImageCreator $creator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->creator = new ImageCreator(
            new Filesystem(),
            ImageManager::usingDriver(GdDriver::class)
        );
    }

    public function testMakeReturnsImageInterface(): void
    {
        $result = $this->creator->make(
            ['bgImage' => false, 'bgColor' => '#ffffff'],
            ['value' => ['A', 'B', 'C', 'D'], 'key' => 'ABCD']
        );

        $this->assertInstanceOf(ImageInterface::class, $result);
    }

    public function testMakeCreatesCorrectDimensions(): void
    {
        $result = $this->creator->make(
            ['width' => 200, 'height' => 60, 'bgImage' => false],
            ['value' => ['X'], 'key' => 'X']
        );

        $this->assertEquals(200, $result->width());
        $this->assertEquals(60, $result->height());
    }

    public function testMakeWithBackgroundColor(): void
    {
        $result = $this->creator->make(
            ['bgImage' => false, 'bgColor' => '#ff0000'],
            ['value' => ['A'], 'key' => 'A']
        );

        $this->assertInstanceOf(ImageInterface::class, $result);
        $this->assertEquals(120, $result->width());
        $this->assertEquals(36, $result->height());
    }

    public function testMakeWithBackgroundImage(): void
    {
        $result = $this->creator->make(
            ['bgImage' => true],
            ['value' => ['A'], 'key' => 'A']
        );

        $this->assertInstanceOf(ImageInterface::class, $result);
    }

    public function testMakeWithContrast(): void
    {
        $result = $this->creator->make(
            ['bgImage' => false, 'contrast' => 10],
            ['value' => ['A'], 'key' => 'A']
        );

        $this->assertInstanceOf(ImageInterface::class, $result);
    }

    public function testMakeWithSharpen(): void
    {
        $result = $this->creator->make(
            ['bgImage' => false, 'sharpen' => 5],
            ['value' => ['A'], 'key' => 'A']
        );

        $this->assertInstanceOf(ImageInterface::class, $result);
    }

    public function testMakeWithInvert(): void
    {
        $result = $this->creator->make(
            ['bgImage' => false, 'invert' => true],
            ['value' => ['A'], 'key' => 'A']
        );

        $this->assertInstanceOf(ImageInterface::class, $result);
    }

    public function testMakeWithBlur(): void
    {
        $result = $this->creator->make(
            ['bgImage' => false, 'blur' => 2],
            ['value' => ['A'], 'key' => 'A']
        );

        $this->assertInstanceOf(ImageInterface::class, $result);
    }

    public function testMakeWithChineseCharacters(): void
    {
        $result = $this->creator->make(
            ['bgImage' => false, 'width' => 150, 'height' => 50],
            ['value' => ['天', '地', '人', '和'], 'key' => '天地人和']
        );

        $this->assertInstanceOf(ImageInterface::class, $result);
        $this->assertEquals(150, $result->width());
        $this->assertEquals(50, $result->height());
    }

    public function testMakeWithLines(): void
    {
        $result = $this->creator->make(
            ['bgImage' => false, 'lines' => 5, 'lineColor' => '#0000ff', 'lineWidth' => 2],
            ['value' => ['A', 'B'], 'key' => 'AB']
        );

        $this->assertInstanceOf(ImageInterface::class, $result);
    }

    public function testMakeEncodesToJpeg(): void
    {
        $image = $this->creator->make(
            ['bgImage' => false],
            ['value' => ['T'], 'key' => 'T']
        );

        $encoded = $image->encodeUsingFormat(Format::JPEG, quality: 90);

        $this->assertNotEmpty((string) $encoded);
        $this->assertStringStartsWith("\xFF\xD8", (string) $encoded);
    }

    public function testMakeWithCustomFontColors(): void
    {
        $result = $this->creator->make(
            [
                'bgImage' => false,
                'fontColors' => ['#ff0000', '#00ff00', '#0000ff'],
            ],
            ['value' => ['A', 'B', 'C'], 'key' => 'ABC']
        );

        $this->assertInstanceOf(ImageInterface::class, $result);
    }

    public function testMakeWithCustomDirectories(): void
    {
        $fontsDir = dirname(__DIR__).'/assets/fonts';
        $bgsDir = dirname(__DIR__).'/assets/backgrounds';

        $result = $this->creator->make(
            [
                'fontsDirectory' => $fontsDir,
                'bgsDirectory' => $bgsDir,
                'bgImage' => false,
            ],
            ['value' => ['X'], 'key' => 'X']
        );

        $this->assertInstanceOf(ImageInterface::class, $result);
    }
}
