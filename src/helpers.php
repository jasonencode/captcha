<?php

use Illuminate\Support\HtmlString;

if (!function_exists('captcha')) {
    /**
     * Create captcha image or API response.
     */
    function captcha(string $style = 'default', bool $api = false): mixed
    {
        return app('captcha')->create($style, $api);
    }
}

if (!function_exists('captcha_src')) {
    /**
     * Generate captcha image source URL.
     */
    function captcha_src(string $style = 'default'): string
    {
        return app('captcha')->src($style);
    }
}

if (!function_exists('captcha_img')) {
    /**
     * Generate captcha image HTML tag.
     */
    function captcha_img(string $style = 'default', array $attrs = []): HtmlString
    {
        return app('captcha')->img($style, $attrs);
    }
}

if (!function_exists('captcha_check')) {
    /**
     * Validate captcha.
     */
    function captcha_check(string $value): bool
    {
        return app('captcha')->check($value);
    }
}

if (!function_exists('captcha_api_check')) {
    /**
     * Validate captcha for API.
     */
    function captcha_api_check(string $value, string $key, string $style = 'default'): bool
    {
        return app('captcha')->checkApi($value, $key, $style);
    }
}
