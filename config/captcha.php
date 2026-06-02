<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 全局设置
    |--------------------------------------------------------------------------
    |
    | disable:    是否禁用验证码功能。为 true 时跳过生成与验证，方便本地开发调试。
    | driver:     图片处理驱动，可选值参考 ImageDriver 枚举：
    |             - 'gd'      : GD 库（PHP 自带，无需额外安装）
    |             - 'imagick' : ImageMagick（需安装扩展，功能更强大）
    | characters: 默认验证码字符集，排除了易混淆字符（0/O、1/I/l、5/S 等）。
    |
    */

    'disable' => env('CAPTCHA_DISABLE', false),

    'driver' => env('CAPTCHA_DRIVER', 'gd'),

    'characters' => [
        '2', '3', '4', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'm', 'n', 'p', 'q', 'r', 't',
        'u', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'M', 'N', 'P', 'Q', 'R', 'T', 'U', 'X', 'Y',
        'Z',
    ],

    /*
    |--------------------------------------------------------------------------
    | 预设配置组
    |--------------------------------------------------------------------------
    |
    | 以下为各预设样式，每组可独立使用，如 captcha_src('default')、captcha_src('flat') 等。
    | 未显式设置的选项将回退到代码中的默认值。
    |
    | 可用选项一览：
    |   length          - int    验证码字符个数（默认 4）
    |   width           - int    图片宽度，单位 px（默认 120）
    |   height          - int    图片高度，单位 px（默认 36）
    |   quality         - int    JPEG 输出质量，0-100（默认 90）
    |   math            - bool   是否启用算术模式，生成加减乘算式（默认 false）
    |   expire          - int    验证码有效期，单位秒（默认 60）
    |   encrypt         - bool   是否加密验证码 key 后再发送到客户端（默认 false）
    |   sensitive       - bool   验证时是否区分大小写（默认 false）
    |   angle           - int    字符最大旋转角度，每个字符随机 [-angle, +angle] 旋转（默认 15）
    |   lines           - int    干扰线数量（默认 3）
    |   lineWidth       - int    干扰线宽度，单位 px（默认 1）
    |   lineColor       - string 干扰线颜色，十六进制格式（默认 '#ff00ff'）
    |   bgImage         - bool   是否使用随机背景图片（默认 true）
    |   bgColor         - string 背景颜色，十六进制格式，bgImage 为 false 时生效（默认 '#ffffff'）
    |   fontColors      - array  字体颜色列表，每个字符随机从中选取一个
    |   contrast        - int    对比度调整，负值降低、正值增加（默认 0，即不调整）
    |   sharpen         - int    锐化强度，增加文字边缘清晰度（默认 0，即不锐化）
    |   blur            - int    模糊强度，使图片略微模糊增加识别难度（默认 0，即不模糊）
    |   invert          - bool   是否反转图片颜色（默认 false）
    |   characters      - array  自定义字符集，覆盖全局 characters（如纯数字场景）
    |   marginTop       - int    文字上边距，单位 px（默认自动计算 height / length）
    |   textLeftPadding - int    文字左边距，单位 px（默认 4）
    |   fontsDirectory  - string 自定义字体文件目录路径（默认包内 assets/fonts）
    |   bgsDirectory    - string 自定义背景图片目录路径（默认包内 assets/backgrounds）
    |
    */

    'default' => [
        'length' => 4,
        'width' => 120,
        'height' => 36,
        'quality' => 90,
        'math' => false,
        'expire' => 60,
        'encrypt' => false,
        'fontColors' => [
            '#2c3e50',
            '#c0392b',
            '#16a085',
            '#c0392b',
            '#8e44ad',
            '#303f9f',
            '#f57c00',
            '#795548',
            '#ff5252',
            '#ff4081',
            '#e040fb',
            '#7c4dff',
            '#536dfe',
            '#448aff',
            '#40c4ff',
        ],
    ],

    'math' => [
        'length' => 9,
        'width' => 120,
        'height' => 36,
        'quality' => 90,
        'math' => true,
    ],

    'number' => [
        'length' => 4,
        'width' => 120,
        'height' => 36,
        'quality' => 90,
        'expire' => 60,
        'math' => false,
        'encrypt' => false,
        'bgImage' => false,
        'contrast' => 1,
        'characters' => ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'],
        'fontColors' => [
            '#2c3e50',
            '#c0392b',
            '#16a085',
            '#c0392b',
            '#8e44ad',
            '#303f9f',
            '#f57c00',
            '#795548',
            '#ff5252',
            '#ff4081',
            '#e040fb',
            '#7c4dff',
            '#536dfe',
            '#448aff',
            '#40c4ff',
        ],
    ],

    'flat' => [
        'length' => 6,
        'width' => 160,
        'height' => 46,
        'quality' => 90,
        'lines' => 6,
        'bgImage' => false,
        'bgColor' => '#ecf2f4',
        'fontColors' => ['#2c3e50', '#c0392b', '#16a085', '#c0392b', '#8e44ad', '#303f9f', '#f57c00', '#795548'],
        'contrast' => -5,
    ],

    'mini' => [
        'length' => 3,
        'width' => 60,
        'height' => 32,
    ],

    'inverse' => [
        'length' => 5,
        'width' => 120,
        'height' => 36,
        'quality' => 90,
        'sensitive' => true,
        'angle' => 12,
        'sharpen' => 10,
        'blur' => 2,
        'invert' => true,
        'contrast' => -5,
    ],

    'admin' => [
        'length' => 9,
        'width' => 120,
        'height' => 36,
        'quality' => 90,
        'math' => true,
        'expire' => 60,
        'encrypt' => false,
        'fontColors' => [
            '#2c3e50',
            '#c0392b',
            '#16a085',
            '#c0392b',
            '#8e44ad',
            '#303f9f',
            '#f57c00',
            '#795548',
            '#ff5252',
            '#ff4081',
            '#e040fb',
            '#7c4dff',
            '#536dfe',
            '#448aff',
            '#40c4ff',
        ],
    ],

    'chinese' => [
        'length' => 4,
        'width' => 120,
        'height' => 36,
        'quality' => 90,
        'math' => false,
        'expire' => 60,
        'encrypt' => false,
        'sensitive' => true,
        'angle' => 20,
        'lines' => 4,
        'lineWidth' => 1,
        'bgColor' => '#ffffff',
        'lineColor' => '#cccccc',
        'marginTop' => 10,
        'textLeftPadding' => 10,
        'characters' => Jason\Captcha\Generators\ChineseGenerator::$defaultCharacters,
        'fontColors' => [
            '#2c3e50',
            '#c0392b',
            '#16a085',
            '#8e44ad',
            '#303f9f',
            '#f57c00',
            '#795548',
            '#ff5252',
            '#ff4081',
            '#e040fb',
        ],
    ],
];
