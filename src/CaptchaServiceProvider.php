<?php

namespace Jason\Captcha;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;
use Jason\Captcha\Enums\ImageDriver;
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
            Route::get('captcha/api/{style?}', static function (Captcha $captcha, string $style = 'default') {
                return $captcha->create($style, true);
            })->middleware('web');

            Route::get('captcha/{style?}', static function (Captcha $captcha, string $style = 'default') {
                if (ob_get_contents()) {
                    ob_clean();
                }

                return $captcha->create($style);
            })->middleware('web');
        }

        // Validator extensions
        Validator::extend('captcha', static function ($attribute, $value) {
            return config('captcha.disable') || ($value && captcha_check($value));
        });

        Validator::extend('captcha_api', static function ($attribute, $value, $parameters) {
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
                $driver = ImageDriver::fromConfig(config('captcha.driver', 'gd'));
                $imageDriver = $driver->isImagick() ? new ImagickDriver() : new GdDriver();

                return ImageManager::withDriver($imageDriver);
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
