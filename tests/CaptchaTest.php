<?php

namespace Jason\Captcha\Tests;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\HtmlString;
use Jason\Captcha\Facades\Captcha;

class CaptchaTest extends TestCase
{
    public function testCreateCaptchaResponse(): void
    {
        $response = Captcha::create();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('image/jpeg', $response->headers->get('Content-Type'));
        $this->assertTrue(Session::has('captcha'));
    }

    public function testCreateCaptchaApi(): void
    {
        $apiData = Captcha::create('default', true);

        $this->assertIsArray($apiData);
        $this->assertArrayHasKey('sensitive', $apiData);
        $this->assertArrayHasKey('key', $apiData);
        $this->assertArrayHasKey('img', $apiData);
        $this->assertStringStartsWith('data:image/jpeg;base64,', $apiData['img']);
    }

    public function testCheckCaptcha(): void
    {
        // For testing we'll mock the internal session and cache

        // Use create to set the initial session/cache
        Captcha::create();

        $stored = Session::get('captcha');
        $key = $stored['key'];

        // Retrieve the real value from cache for testing check
        $value = Cache::get('captcha_'.md5($key));

        $this->assertTrue(Captcha::check($value));
        $this->assertFalse(Session::has('captcha')); // Session should be cleared
    }

    public function testHelpers(): void
    {
        $this->assertIsString(captcha_src());
        $this->assertInstanceOf(HtmlString::class, captcha_img());
    }
}
