<?php

namespace Jason\Captcha\Generators;

use Illuminate\Support\Str;
use Jason\Captcha\Contracts\CaptchaGenerator;
use Random\RandomException;

class StringGenerator implements CaptchaGenerator
{
    /**
     * @throws RandomException
     */
    public function generate(array $config): array
    {
        $length = $config['length'] ?? 5;
        $characters = $config['characters'] ?? ['1', '2', '3', '4', '6', '7', '8', '9'];
        $sensitive = $config['sensitive'] ?? false;

        $characters = is_string($characters) ? str_split($characters) : $characters;

        $bag = [];
        for ($i = 0; $i < $length; $i++) {
            $char = $characters[random_int(0, count($characters) - 1)];
            $bag[] = $sensitive ? $char : Str::lower($char);
        }

        return [
            'value' => $bag,
            'key' => implode('', $bag),
            'sensitive' => $sensitive,
        ];
    }
}
