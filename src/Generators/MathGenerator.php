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

        $operation = random_int(0, 1) ? '+' : '-';

        if ($operation === '-') {
            // Ensure x is always greater than y for subtraction
            if ($x < $y) {
                [$x, $y] = [$y, $x];
            }
            $result = $x - $y;
        } else {
            $result = $x + $y;
        }

        $expression = "$x $operation $y = ";

        return [
            'value' => str_split($expression),
            'key' => (string) $result,
            'sensitive' => false,
        ];
    }
}
