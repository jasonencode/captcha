<?php

namespace Jason\Captcha\Enums;

enum ImageDriver: string
{
    case GD = 'gd';

    case IMAGICK = 'imagick';

    /**
     * 获取驱动名称（用于显示）
     */
    public function label(): string
    {
        return match ($this) {
            self::GD => 'GD Library',
            self::IMAGICK => 'ImageMagick',
        };
    }

    /**
     * 判断是否为 GD 驱动
     */
    public function isGd(): bool
    {
        return $this === self::GD;
    }

    /**
     * 判断是否为 ImageMagick 驱动
     */
    public function isImagick(): bool
    {
        return $this === self::IMAGICK;
    }

    /**
     * 从配置值创建枚举
     */
    public static function fromConfig(string $value): self
    {
        return self::from(strtolower(trim($value)));
    }
}