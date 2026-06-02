<?php

namespace Jason\Captcha\Tests;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\Response;
use Illuminate\Session\Store as Session;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\EncodedImage;
use Intervention\Image\Interfaces\DataUriInterface;
use Intervention\Image\Interfaces\ImageInterface;
use Jason\Captcha\Captcha;
use Jason\Captcha\Image\ImageCreator;
use Jason\Captcha\Support\Config;
use Mockery;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class CaptchaTest extends PHPUnitTestCase
{
    protected $captcha;
    protected $config;
    protected $imageCreator;
    protected $session;
    protected $hasher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = Mockery::mock(Config::class);
        $this->imageCreator = Mockery::mock(ImageCreator::class);
        $this->session = Mockery::mock(Session::class);
        $this->hasher = Mockery::mock(Hasher::class);

        $this->captcha = new Captcha(
            $this->config,
            $this->imageCreator,
            $this->session,
            $this->hasher
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testCheckReturnsFalseWhenNoCaptchaInSession(): void
    {
        $this->session->shouldReceive('has')->with('captcha')->andReturn(false);

        $result = $this->captcha->check('test');

        $this->assertFalse($result);
    }

    public function testCheckReturnsFalseWhenCacheEmpty(): void
    {
        $stored = [
            'sensitive' => false,
            'key' => 'hashed_value',
            'encrypt' => false,
        ];

        $this->session->shouldReceive('has')->with('captcha')->andReturn(true);
        $this->session->shouldReceive('get')->with('captcha')->andReturn($stored);
        $this->session->shouldReceive('forget')->with('captcha')->once();

        Cache::shouldReceive('pull')->once()->andReturn(false);

        $result = $this->captcha->check('test');

        $this->assertFalse($result);
    }

    public function testCheckReturnsTrueWhenValid(): void
    {
        $stored = [
            'sensitive' => false,
            'key' => 'hashed_value',
            'encrypt' => false,
        ];

        $this->session->shouldReceive('has')->with('captcha')->andReturn(true);
        $this->session->shouldReceive('get')->with('captcha')->andReturn($stored);
        $this->session->shouldReceive('forget')->with('captcha')->once();

        Cache::shouldReceive('pull')->once()->andReturn('test');

        $this->hasher->shouldReceive('check')->with('test', 'hashed_value')->andReturn(true);

        $result = $this->captcha->check('test');

        $this->assertTrue($result);
    }

    public function testCheckApiReturnsFalseWhenCacheEmpty(): void
    {
        Cache::shouldReceive('pull')->once()->andReturn(false);

        $result = $this->captcha->checkApi('test', 'key');

        $this->assertFalse($result);
    }

    public function testCheckApiReturnsTrueWhenValid(): void
    {
        $config = [
            'sensitive' => false,
            'encrypt' => false,
        ];

        $this->config->shouldReceive('get')->with('default')->andReturn($config);

        Cache::shouldReceive('pull')->once()->andReturn('test');

        $this->hasher->shouldReceive('check')->with('test', 'key')->andReturn(true);

        $result = $this->captcha->checkApi('test', 'key');

        $this->assertTrue($result);
    }

    public function testCheckWithSensitiveMode(): void
    {
        $stored = [
            'sensitive' => true,
            'key' => 'hashed_value',
            'encrypt' => false,
        ];

        $this->session->shouldReceive('has')->with('captcha')->andReturn(true);
        $this->session->shouldReceive('get')->with('captcha')->andReturn($stored);
        $this->session->shouldReceive('forget')->with('captcha')->once();

        Cache::shouldReceive('pull')->once()->andReturn('TestValue');

        $this->hasher->shouldReceive('check')->with('TestValue', 'hashed_value')->andReturn(true);

        $result = $this->captcha->check('TestValue');

        $this->assertTrue($result);
    }

    public function testCheckConvertsToLowerCaseWhenNonSensitive(): void
    {
        $stored = [
            'sensitive' => false,
            'key' => 'hashed_value',
            'encrypt' => false,
        ];

        $this->session->shouldReceive('has')->with('captcha')->andReturn(true);
        $this->session->shouldReceive('get')->with('captcha')->andReturn($stored);
        $this->session->shouldReceive('forget')->with('captcha')->once();

        Cache::shouldReceive('pull')->once()->andReturn('ABC');

        $this->hasher->shouldReceive('check')->with('abc', 'hashed_value')->andReturn(true);

        $result = $this->captcha->check('ABC');

        $this->assertTrue($result);
    }

    public function testCheckApiWithSensitiveStyle(): void
    {
        $config = [
            'sensitive' => true,
            'encrypt' => false,
        ];

        $this->config->shouldReceive('get')->with('chinese')->andReturn($config);

        Cache::shouldReceive('pull')->once()->andReturn('ABC');

        $this->hasher->shouldReceive('check')->with('ABC', 'key')->andReturn(true);

        $result = $this->captcha->checkApi('ABC', 'key', 'chinese');

        $this->assertTrue($result);
    }

    public function testCheckReturnsFalseWhenInvalid(): void
    {
        $stored = [
            'sensitive' => false,
            'key' => 'hashed_value',
            'encrypt' => false,
        ];

        $this->session->shouldReceive('has')->with('captcha')->andReturn(true);
        $this->session->shouldReceive('get')->with('captcha')->andReturn($stored);

        Cache::shouldReceive('pull')->once()->andReturn('wrong');

        $this->hasher->shouldReceive('check')->with('wrong', 'hashed_value')->andReturn(false);

        $result = $this->captcha->check('wrong');

        $this->assertFalse($result);
        $this->session->shouldNotHaveReceived('forget', ['captcha']);
    }

    public function testCreateReturnsResponse(): void
    {
        $config = [
            'math' => false,
            'expire' => 60,
            'encrypt' => false,
            'quality' => 90,
        ];

        $encodedImage = Mockery::mock(EncodedImage::class);
        $encodedImage->shouldReceive('__toString')->andReturn('fake-jpeg-data');

        $image = Mockery::mock(ImageInterface::class);
        $image->shouldReceive('encodeUsingFormat')->once()->andReturn($encodedImage);

        $this->config->shouldReceive('get')->with('default')->andReturn($config);
        $this->imageCreator->shouldReceive('make')->once()->andReturn($image);
        $this->hasher->shouldReceive('make')->once()->andReturn('hashed_key');
        $this->session->shouldReceive('put')->once();
        Cache::shouldReceive('put')->once();

        $result = $this->captcha->create('default');

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals('image/jpeg', $result->headers->get('Content-Type'));
        $this->assertEquals('fake-jpeg-data', $result->getContent());
    }

    public function testCreateReturnsApiResponse(): void
    {
        $config = [
            'math' => false,
            'expire' => 60,
            'encrypt' => false,
            'quality' => 90,
        ];

        $dataUri = Mockery::mock(DataUriInterface::class);
        $dataUri->shouldReceive('__toString')->andReturn('data:image/jpeg;base64,fake');

        $encodedImage = Mockery::mock(EncodedImage::class);
        $encodedImage->shouldReceive('toDataUri')->once()->andReturn($dataUri);

        $image = Mockery::mock(ImageInterface::class);
        $image->shouldReceive('encodeUsingFormat')->once()->andReturn($encodedImage);

        $this->config->shouldReceive('get')->with('default')->andReturn($config);
        $this->imageCreator->shouldReceive('make')->once()->andReturn($image);
        $this->hasher->shouldReceive('make')->once()->andReturn('hashed_key');
        $this->session->shouldReceive('put')->once();
        Cache::shouldReceive('put')->once();

        $result = $this->captcha->create('default', true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('sensitive', $result);
        $this->assertArrayHasKey('key', $result);
        $this->assertArrayHasKey('img', $result);
        $this->assertInstanceOf(DataUriInterface::class, $result['img']);
        $this->assertEquals('data:image/jpeg;base64,fake', (string) $result['img']);
    }

    public function testCreateWithMathStyle(): void
    {
        $config = [
            'math' => true,
            'expire' => 60,
            'encrypt' => false,
            'quality' => 90,
        ];

        $encodedImage = Mockery::mock(EncodedImage::class);
        $encodedImage->shouldReceive('__toString')->andReturn('fake-data');

        $image = Mockery::mock(ImageInterface::class);
        $image->shouldReceive('encodeUsingFormat')->once()->andReturn($encodedImage);

        $this->config->shouldReceive('get')->with('math')->andReturn($config);
        $this->imageCreator->shouldReceive('make')->once()->andReturn($image);
        $this->hasher->shouldReceive('make')->once()->andReturn('hashed_key');
        $this->session->shouldReceive('put')->once();
        Cache::shouldReceive('put')->once();

        $result = $this->captcha->create('math');

        $this->assertInstanceOf(Response::class, $result);
    }

    public function testCreateWithChineseStyle(): void
    {
        $config = [
            'math' => false,
            'expire' => 60,
            'encrypt' => false,
            'quality' => 90,
            'sensitive' => true,
        ];

        $encodedImage = Mockery::mock(EncodedImage::class);
        $encodedImage->shouldReceive('__toString')->andReturn('fake-data');

        $image = Mockery::mock(ImageInterface::class);
        $image->shouldReceive('encodeUsingFormat')->once()->andReturn($encodedImage);

        $this->config->shouldReceive('get')->with('chinese')->andReturn($config);
        $this->imageCreator->shouldReceive('make')->once()->andReturn($image);
        $this->hasher->shouldReceive('make')->once()->andReturn('hashed_key');
        $this->session->shouldReceive('put')->once();
        Cache::shouldReceive('put')->once();

        $result = $this->captcha->create('chinese');

        $this->assertInstanceOf(Response::class, $result);
    }
}
