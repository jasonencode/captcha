<?php

namespace Jason\Captcha\Contracts;

interface CaptchaGenerator
{
    /**
     * Generate captcha value and characters.
     *
     * @param  array  $config
     * @return array{value: string|array, key: string, sensitive: bool}
     */
    public function generate(array $config): array;
}
