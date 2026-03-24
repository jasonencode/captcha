<?php

namespace Jason\Captcha;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\Response;
use Illuminate\Session\Store as Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Jason\Captcha\Generators\MathGenerator;
use Jason\Captcha\Generators\StringGenerator;
use Jason\Captcha\Image\ImageCreator;
use Jason\Captcha\Support\Config;

class Captcha
{
    protected Config $config;

    protected ImageCreator $imageCreator;

    protected Session $session;

    protected Hasher $hasher;

    public function __construct(
        Config $config,
        ImageCreator $imageCreator,
        Session $session,
        Hasher $hasher
    ) {
        $this->config = $config;
        $this->imageCreator = $imageCreator;
        $this->session = $session;
        $this->hasher = $hasher;
    }

    /**
     * Create captcha image or API response.
     */
    public function create(string $style = 'default', bool $api = false): Response|array
    {
        $config = $this->config->get($style);

        $generator = $config['math'] ? new MathGenerator() : new StringGenerator();
        $generatorResult = $generator->generate($config);

        $image = $this->imageCreator->make($config, $generatorResult);

        $hash = $this->hasher->make($generatorResult['key']);
        if ($config['encrypt']) {
            $hash = Crypt::encrypt($hash);
        }

        $this->session->put('captcha', [
            'sensitive' => $generatorResult['sensitive'],
            'key' => $hash,
            'encrypt' => $config['encrypt'],
        ]);

        Cache::put($this->getCacheKey($hash), $generatorResult['key'], $config['expire']);

        if ($api) {
            return [
                'sensitive' => $generatorResult['sensitive'],
                'key' => $hash,
                'img' => $image->toJpg($config['quality'])->toDataUri(),
            ];
        }

        return new Response($image->toJpg($config['quality']), 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => 'Fri, 01 Jan 1990 00:00:00 GMT',
        ]);
    }

    /**
     * Validate captcha.
     */
    public function check(string $value): bool
    {
        if (!$this->session->has('captcha')) {
            return false;
        }

        $stored = $this->session->get('captcha');
        $key = $stored['key'];

        if (!Cache::pull($this->getCacheKey($key))) {
            $this->session->forget('captcha');

            return false;
        }

        if (!$stored['sensitive']) {
            $value = Str::lower($value);
        }

        $hash = $stored['encrypt'] ? Crypt::decrypt($key) : $key;
        $isValid = $this->hasher->check($value, $hash);

        if ($isValid) {
            $this->session->forget('captcha');
        }

        return $isValid;
    }

    /**
     * Validate captcha for API.
     */
    public function checkApi(string $value, string $key, string $style = 'default'): bool
    {
        if (!Cache::pull($this->getCacheKey($key))) {
            return false;
        }

        $config = $this->config->get($style);

        if (!$config['sensitive']) {
            $value = Str::lower($value);
        }

        $hash = $config['encrypt'] ? Crypt::decrypt($key) : $key;

        return $this->hasher->check($value, $hash);
    }

    /**
     * Get the cache key for a captcha.
     */
    protected function getCacheKey(string $key): string
    {
        return 'captcha_'.md5($key);
    }

    /**
     * Generate captcha image source URL.
     */
    public function src(string $style = 'default'): string
    {
        return url('captcha/'.$style).'?'.Str::random(8);
    }

    /**
     * Generate captcha image HTML tag.
     */
    public function img(string $style = 'default', array $attrs = []): HtmlString
    {
        $attrsStr = '';
        foreach ($attrs as $attr => $value) {
            if ($attr === 'src') {
                continue;
            }
            $attrsStr .= " $attr=\"$value\"";
        }

        return new HtmlString('<img src="'.$this->src($style).'"'.trim($attrsStr).'>');
    }
}
