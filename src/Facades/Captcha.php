<?php

namespace Jason\Captcha\Facades;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\HtmlString;

/**
 * @method static Response|array create(string $style = 'default', bool $api = false)
 * @method static bool check(string $value)
 * @method static bool checkApi(string $value, string $key, string $style = 'default')
 * @method static string src(string $style = 'default')
 * @method static HtmlString img(string $style = 'default', array $attrs = [])
 *
 * @see \Jason\Captcha\Captcha
 */
class Captcha extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'captcha';
    }
}
