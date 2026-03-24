<?php

namespace Jason\Captcha;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Session\Store;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Factory;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;
use Jason\Captcha\Image\ImageCreator;
use Jason\Captcha\Support\Config;

class CaptchaServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     */
    public function boot(): void
    {
        // Publish configuration files
        $this->publishes([
            __DIR__.'/../config/captcha.php' => config_path('captcha.php'),
        ], 'config');

        // HTTP routing
        if (!config('captcha.disable')) {
            $router = $this->app['router'];
            $router->get('captcha/api/{style?}', function (Captcha $captcha, string $style = 'default') {
                return $captcha->create($style, true);
            })->middleware('web');

            $router->get('captcha/{style?}', function (Captcha $captcha, string $style = 'default') {
                if (ob_get_contents()) {
                    ob_clean();
                }

                return $captcha->create($style);
            })->middleware('web');
        }

        /* @var Factory $validator */
        $validator = $this->app['validator'];

        // Validator extensions
        $validator->extend('captcha', function ($attribute, $value) {
            return config('captcha.disable') || ($value && captcha_check($value));
        });

        // Validator extensions
        $validator->extend('captcha_api', function ($attribute, $value, $parameters) {
            return config('captcha.disable') || ($value && captcha_api_check($value, $parameters[0], $parameters[1] ?? 'default'));
        });
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        // Merge configs
        $this->mergeConfigFrom(
            __DIR__.'/../config/captcha.php',
            'captcha'
        );

        // Bind the ImageManager with an explicit driver
        if (!$this->app->bound(ImageManager::class)) {
            $this->app->singleton(ImageManager::class, function () {
                $driver = config('captcha.driver', 'gd') === 'imagick' ? new ImagickDriver() : new GdDriver();

                return new ImageManager($driver);
            });
        }

        $this->app->singleton(Config::class, function ($app) {
            return new Config($app[Repository::class]);
        });

        $this->app->singleton(ImageCreator::class, function ($app) {
            return new ImageCreator($app[Filesystem::class], $app[ImageManager::class]);
        });

        // Bind captcha
        $this->app->singleton('captcha', function ($app) {
            return new Captcha(
                $app[Config::class],
                $app[ImageCreator::class],
                $app[Store::class],
                $app[Hasher::class]
            );
        });
    }
}
