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
        $operations = ['+', '-', '*'];
        $operation = $operations[random_int(0, 2)];

        if ($operation === '*') {
            $x = random_int(1, 9);
            $y = random_int(1, 9);
            $result = $x * $y;
        } else {
            $x = random_int(10, 30);
            $y = random_int(1, 9);

            if ($operation === '-') {
                $result = $x - $y;
            } else {
                $result = $x + $y;
            }
        }

        $expression = "$x $operation $y = ";

        return [
            'value' => str_split($expression),
            'key' => (string) $result,
            'sensitive' => false,
        ];
    }
}
