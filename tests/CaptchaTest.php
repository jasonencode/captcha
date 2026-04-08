<?php

namespace Jason\Captcha\Tests;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Session\Store as Session;
use Illuminate\Support\Facades\Cache;
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
}
