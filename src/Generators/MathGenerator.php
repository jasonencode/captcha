<?php

namespace Jason\Captcha\Generators;

use Jason\Captcha\Contracts\CaptchaGenerator;
use Random\RandomException;

class MathGenerator implements CaptchaGenerator
{
    /**
     * @throws RandomException
     */
    public function generate(array $config): array
    {
        $x = random_int(10, 30);
        $y = random_int(1, 9);

        return [
            'value' => ["$x + $y = "],
            'key' => (string) ($x + $y),
            'sensitive' => false,
        ];
    }
}
