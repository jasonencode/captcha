<?php

namespace Jason\Captcha\Image;

use Illuminate\Filesystem\Filesystem;
use Intervention\Image\Alignment;
use Intervention\Image\Geometry\Factories\LineFactory;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Typography\FontFactory;
use Random\RandomException;

class ImageCreator
{
    protected Filesystem $files;

    protected ImageManager $imageManager;

    protected array $fonts = [];

    protected array $chineseFonts = [];

    protected array $backgrounds = [];

    protected static array $assetsCache = [];

    public function __construct(Filesystem $files, ImageManager $imageManager)
    {
        $this->files = $files;
        $this->imageManager = $imageManager;
    }

    public function make(array $config, array $generatorResult): ImageInterface
    {
        $this->loadAssets($config);

        $width = $config['width'] ?? 120;
        $height = $config['height'] ?? 36;
        $bgColor = $config['bgColor'] ?? '#ffffff';
        $bgImage = $config['bgImage'] ?? true;

        $canvas = $this->imageManager->createImage($width, $height)->fill($bgColor);

        if ($bgImage && !empty($this->backgrounds)) {
            $bg = $this->imageManager->decodePath($this->getRandomBackground())->resize($width, $height);
            $canvas->insert($bg);
        }

        if (isset($config['contrast']) && $config['contrast'] !== 0) {
            $canvas->contrast($config['contrast']);
        }

        $this->drawText($canvas, $generatorResult['value'], $config);
        $this->drawLines($canvas, $config);

        if ($config['sharpen'] ?? 0) {
            $canvas->sharpen($config['sharpen']);
        }
        if ($config['invert'] ?? false) {
            $canvas->invert();
        }
        if ($config['blur'] ?? 0) {
            $canvas->blur($config['blur']);
        }

        return $canvas;
    }

    protected function loadAssets(array $config): void
    {
        $fontsDir = $config['fontsDirectory'] ?? dirname(__DIR__, 2).'/assets/fonts';
        $bgsDir = $config['bgsDirectory'] ?? dirname(__DIR__, 2).'/assets/backgrounds';

        $cacheKey = md5($fontsDir.$bgsDir);

        if (!isset(self::$assetsCache[$cacheKey])) {
            $allFonts = array_map(static fn ($file) => $file->getPathName(), $this->files->files($fontsDir));

            $chineseFonts = array_filter($allFonts, static function ($font) {
                $lower = strtolower($font);

                return str_contains($lower, 'chinese') || str_contains($lower, 'zh') || str_contains($lower, 'cjk') || str_contains($lower, 'sc');
            });

            $fonts = array_diff($allFonts, $chineseFonts);

            self::$assetsCache[$cacheKey] = [
                'fonts' => $fonts,
                'chineseFonts' => $chineseFonts,
                'backgrounds' => array_map(static fn ($file) => $file->getPathName(), $this->files->files($bgsDir)),
            ];
        }

        $this->fonts = self::$assetsCache[$cacheKey]['fonts'];
        $this->chineseFonts = self::$assetsCache[$cacheKey]['chineseFonts'];
        $this->backgrounds = self::$assetsCache[$cacheKey]['backgrounds'];
    }

    protected function getRandomBackground(): string
    {
        return $this->backgrounds[array_rand($this->backgrounds)];
    }

    protected function getRandomFont(bool $isChinese = false): string
    {
        if ($isChinese && !empty($this->chineseFonts)) {
            return $this->chineseFonts[array_rand($this->chineseFonts)];
        }

        return $this->fonts[array_rand($this->fonts)];
    }

    protected function drawText(ImageInterface $image, array $text, array $config): void
    {
        $length = count($text);
        $width = $image->width();
        $height = $image->height();
        $padding = $config['textLeftPadding'] ?? 4;
        $marginTop = $config['marginTop'] ?? (int) ($height / $length);
        $angle = $config['angle'] ?? 15;

        foreach ($text as $key => $char) {
            $marginLeft = $padding + ($key * ($width - $padding) / $length);
            $isChinese = $this->containsChinese($char);

            $image->text($char, (int) $marginLeft, (int) $marginTop, function (FontFactory $font) use ($config, $height, $angle, $isChinese) {
                $font->filepath($this->getRandomFont($isChinese));
                $font->size($isChinese ? random_int($height - 20, $height - 6) : random_int($height - 10, $height));
                $font->color($this->getRandomColor($config['fontColors'] ?? []));
                $font->align(Alignment::LEFT, Alignment::TOP);
                $font->angle(random_int(-$angle, $angle));
            });
        }
    }

    protected function containsChinese(string $char): bool
    {
        return preg_match('/[\x{4e00}-\x{9fa5}]/u', $char) > 0;
    }

    protected function drawLines(ImageInterface $image, array $config): void
    {
        $lines = $config['lines'] ?? 3;
        $lineColor = $config['lineColor'] ?? '#ff00ff';
        $lineWidth = $config['lineWidth'] ?? 1;

        for ($i = 0; $i <= $lines; $i++) {
            $image->drawLine(function (LineFactory $line) use ($image, $i, $lineColor, $lineWidth) {
                $line->from(
                    random_int(0, $image->width()) + $i * random_int(0, $image->height()),
                    random_int(0, $image->height())
                );
                $line->to(random_int(0, $image->width()), random_int(0, $image->height()));
                $line->color($lineColor);
                $line->width($lineWidth);
            });
        }
    }

    /**
     * @throws RandomException
     */
    protected function getRandomColor(array $colors): string
    {
        if (!empty($colors)) {
            return $colors[array_rand($colors)];
        }

        return '#'.str_pad(dechex(random_int(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }
}
