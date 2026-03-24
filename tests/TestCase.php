<?php

namespace Jason\Captcha\Tests;

use Jason\Captcha\CaptchaServiceProvider;
use Jason\Captcha\Facades\Captcha;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            CaptchaServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Captcha' => Captcha::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default config
        $app['config']->set('captcha.default', [
            'length' => 5,
            'width' => 120,
            'height' => 36,
            'quality' => 90,
            'math' => false,
            'expire' => 60,
        ]);
    }
}
