<?php

namespace Jason\Captcha\Support;

use Illuminate\Contracts\Config\Repository;

class Config
{
    protected Repository $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * Get configuration for a specific style.
     */
    public function get(string $style): array
    {
        $baseConfig = [
            'characters' => $this->config->get('captcha.characters', ['1', '2', '3', '4', '6', '7', '8', '9']),
            'fontsDirectory' => dirname(__DIR__, 2).'/assets/fonts',
            'bgsDirectory' => dirname(__DIR__, 2).'/assets/backgrounds',
        ];

        $styleConfig = $this->config->get("captcha.$style", []);

        // Merge defaults if not present in style
        $defaults = [
            'length' => 5,
            'width' => 120,
            'height' => 36,
            'angle' => 15,
            'lines' => 3,
            'lineWidth' => 2,
            'lineColor' => '#ff00ff',
            'quality' => 90,
            'bgImage' => true,
            'bgColor' => '#ffffff',
            'sensitive' => false,
            'math' => false,
            'expire' => 60,
            'encrypt' => false,
        ];

        return array_merge($baseConfig, $defaults, $styleConfig);
    }
}
