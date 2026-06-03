<?php

/**
 * 独立预览脚本 - 无需 Laravel 项目即可查看所有验证码样式
 *
 * 用法:
 *   php preview.php                    # 生成所有样式
 *   php preview.php flat               # 生成指定样式
 *   php preview.php math chinese       # 生成多个样式
 *   php preview.php --open             # 生成后自动打开预览页
 *
 * 输出目录: preview/
 */

$styles = array_slice($argv, 1);
$openAfter = false;

if (($key = array_search('--open', $styles, true)) !== false) {
    $openAfter = true;
    unset($styles[$key]);
    $styles = array_values($styles);
}

require __DIR__ . '/vendor/autoload.php';

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Geometry\Factories\LineFactory;
use Intervention\Image\Typography\FontFactory;
use Intervention\Image\Alignment;
use Jason\Captcha\Generators\StringGenerator;
use Jason\Captcha\Generators\MathGenerator;
use Jason\Captcha\Generators\ChineseGenerator;

// ── 1. 创建依赖 ──────────────────────────────────────────────
$imageManager = new ImageManager(new Driver());

// ── 2. 加载配置 ──────────────────────────────────────────────
$configs = require __DIR__ . '/config/captcha.php';

$defaults = [
    'fontsDirectory' => __DIR__ . '/assets/fonts',
    'bgsDirectory' => __DIR__ . '/assets/backgrounds',
    'length' => 4,
    'width' => 120,
    'height' => 36,
    'angle' => 15,
    'lines' => 3,
    'lineWidth' => 1,
    'lineColor' => '#ff00ff',
    'quality' => 90,
    'bgImage' => true,
    'bgColor' => '#ffffff',
    'sensitive' => false,
    'math' => false,
    'expire' => 60,
    'encrypt' => false,
];

// ── 3. 定义样式列表 ──────────────────────────────────────────
$allStyles = [
    'default' => ['label' => '默认样式 (default)'],
    'math'    => ['label' => '算术验证码 (math)'],
    'number'  => ['label' => '纯数字 (number)'],
    'flat'    => ['label' => '扁平风格 (flat)'],
    'mini'    => ['label' => '迷你版 (mini)'],
    'inverse' => ['label' => '反色风格 (inverse)'],
    'admin'   => ['label' => '管理员 (admin)'],
    'chinese' => ['label' => '中文汉字 (chinese)'],
];

if (!empty($styles)) {
    $selected = [];
    foreach ($styles as $name) {
        if (isset($allStyles[$name])) {
            $selected[$name] = $allStyles[$name];
        } else {
            echo "⚠️  未知样式: $name （可选: " . implode(', ', array_keys($allStyles)) . "）\n";
        }
    }
    $allStyles = $selected;
}

if (empty($allStyles)) {
    echo "❌ 没有有效的样式可生成。\n";
    exit(1);
}

// ── 4. 创建输出目录 ─────────────────────────────────────────
$outputDir = __DIR__ . '/assets/preview';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

/**
 * 加载字体和背景资源
 */
function loadResources(): array
{
    static $cache = null;

    if ($cache !== null) {
        return $cache;
    }

    $fontsDir = __DIR__ . '/assets/fonts';
    $bgsDir = __DIR__ . '/assets/backgrounds';

    $allFonts = glob($fontsDir . '/*.{ttf,otf,ttc}', GLOB_BRACE) ?: [];
    $chineseFonts = array_values(array_filter($allFonts, static function ($font) {
        $lower = strtolower(basename($font));
        return str_contains($lower, 'chinese') || str_contains($lower, 'zh') || str_contains($lower, 'cjk') || str_contains($lower, 'sc');
    }));
    $fonts = array_values(array_diff($allFonts, $chineseFonts));
    $bgs = glob($bgsDir . '/*.{png,jpg,jpeg,gif}', GLOB_BRACE) ?: [];

    $cache = [$fonts, $chineseFonts, $bgs];
    return $cache;
}

/**
 * 渲染验证码图片，返回 JPEG 二进制数据
 */
function renderCaptcha(ImageManager $manager, array $text, array $config): string
{
    [$fonts, $chineseFonts, $backgrounds] = loadResources();

    $width = $config['width'] ?? 120;
    $height = $config['height'] ?? 36;
    $bgColor = $config['bgColor'] ?? '#ffffff';
    $bgImage = $config['bgImage'] ?? true;

    // 创建画布 (v4 API: createImage)
    $canvas = $manager->createImage($width, $height)->fill($bgColor);

    // 随机背景图 (v4 API: decodePath)
    if ($bgImage && !empty($backgrounds)) {
        $bgPath = $backgrounds[array_rand($backgrounds)];
        $bg = $manager->decodePath($bgPath)->resize($width, $height);
        $canvas->insert($bg);
    }

    // 对比度
    if (isset($config['contrast']) && $config['contrast'] !== 0) {
        $canvas->contrast($config['contrast']);
    }

    // 绘制文字
    $padding = $config['textLeftPadding'] ?? 4;
    $angle = $config['angle'] ?? 15;
    $fontColors = $config['fontColors'] ?? [];

    foreach ($text as $key => $char) {
        $marginLeft = $padding + ($key * ($width - $padding) / count($text));
        $isChinese = preg_match('/[\x{4e00}-\x{9fa5}]/u', $char) > 0;

        $fontFile = $isChinese && !empty($chineseFonts)
            ? $chineseFonts[array_rand($chineseFonts)]
            : $fonts[array_rand($fonts)];

        $fontSize = $isChinese
            ? random_int($height - 20, $height - 6)
            : random_int($height - 10, $height);

        // text() y 坐标是基线(baseline)，所以要下移让文字完整显示
        $baselineY = (int) ($height * 0.5 + $fontSize * 0.35);

        $color = !empty($fontColors)
            ? $fontColors[array_rand($fontColors)]
            : '#' . str_pad(dechex(random_int(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);

        $canvas->text($char, (int) $marginLeft, $baselineY, function (FontFactory $font) use ($fontFile, $fontSize, $color, $angle) {
            $font->filepath($fontFile);
            $font->size($fontSize);
            $font->color($color);
            $font->align(Alignment::LEFT);
            $font->angle(random_int(-$angle, $angle));
        });
    }

    // 绘制干扰线
    $lines = $config['lines'] ?? 3;
    $lineColor = $config['lineColor'] ?? '#ff00ff';
    $lineWidth = $config['lineWidth'] ?? 1;

    for ($i = 0; $i <= $lines; $i++) {
        $canvas->drawLine(function (LineFactory $line) use ($canvas, $i, $lineColor, $lineWidth) {
            $line->from(
                random_int(0, $canvas->width()) + $i * random_int(0, $canvas->height()),
                random_int(0, $canvas->height())
            );
            $line->to(random_int(0, $canvas->width()), random_int(0, $canvas->height()));
            $line->color($lineColor);
            $line->width($lineWidth);
        });
    }

    // 特效
    if ($config['sharpen'] ?? 0) {
        $canvas->sharpen($config['sharpen']);
    }
    if ($config['invert'] ?? false) {
        $canvas->invert();
    }
    if ($config['blur'] ?? 0) {
        $canvas->blur($config['blur']);
    }

    // 输出 JPEG
    return $canvas->encode(
        new Intervention\Image\Encoders\JpegEncoder($config['quality'] ?? 90)
    )->toString();
}

// ── 5. 生成预览 ──────────────────────────────────────────────
$generated = [];

foreach ($allStyles as $name => $info) {
    $styleConfig = $configs[$name] ?? [];
    $config = array_merge($defaults, $styleConfig);
    $config['characters'] ??= $configs['characters'] ?? [];

    // 选择生成器
    $generator = match (true) {
        $config['math'] => new MathGenerator(),
        $name === 'chinese' => new ChineseGenerator(),
        default => new StringGenerator(),
    };

    try {
        $result = $generator->generate($config);
        $jpegData = renderCaptcha($imageManager, $result['value'], $config);

        $filename = "{$name}.jpg";
        file_put_contents("{$outputDir}/{$filename}", $jpegData);

        $generated[] = [
            'name' => $name,
            'label' => $info['label'],
            'file' => $filename,
            'answer' => $result['key'],
        ];

        echo "✅ {$info['label']}  →  assets/preview/{$filename}  (答案: {$result['key']})\n";
    } catch (\Throwable $e) {
        echo "❌ {$info['label']}  生成失败: {$e->getMessage()}\n";
    }
}

// ── 6. 生成 HTML 预览页 ─────────────────────────────────────
$cards = '';
foreach ($generated as $item) {
    $cards .= <<<CARD
<div class="card">
  <h3>{$item['label']}</h3>
  <img src="{$item['file']}" alt="{$item['label']}">
  <div class="answer">答案: <code>{$item['answer']}</code></div>
</div>

CARD;
}

$count = count($generated);
file_put_contents($outputDir . '/index.html', <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Captcha 预览</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: -apple-system, "Segoe UI", "PingFang SC", "Microsoft YaHei", sans-serif; background: #f5f7fa; padding: 40px 20px; color: #333; }
  h1 { text-align: center; font-weight: 600; font-size: 24px; margin-bottom: 8px; }
  .sub { text-align: center; color: #999; font-size: 14px; margin-bottom: 32px; }
  .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; max-width: 960px; margin: 0 auto; }
  .card { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 20px; text-align: center; transition: box-shadow .2s; }
  .card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
  .card h3 { font-size: 15px; font-weight: 500; margin-bottom: 12px; color: #555; }
  .card img { max-width: 100%; height: auto; border-radius: 6px; border: 1px solid #eee; display: block; margin: 0 auto; }
  .card .answer { margin-top: 10px; font-size: 13px; color: #999; }
  .card .answer code { background: #f0f4f8; padding: 2px 8px; border-radius: 4px; color: #2c3e50; font-size: 14px; }
  .footer { text-align: center; margin-top: 32px; font-size: 13px; color: #bbb; }
</style>
</head>
<body>
<h1>🖼️ Captcha 预览</h1>
<p class="sub">jason/captcha &mdash; 共 {$count} 种样式</p>
<div class="grid">
{$cards}</div>
<div class="footer">Generated by preview.php</div>
</body>
</html>
HTML);

echo "\n📄 HTML 预览页: assets/preview/index.html\n";

// ── 7. 可选：打开预览 ──────────────────────────────────────
if ($openAfter) {
    $index = realpath($outputDir . '/index.html');
    if (PHP_OS_FAMILY === 'Windows') {
        exec("start \"\" \"{$index}\"");
    } elseif (PHP_OS_FAMILY === 'Darwin') {
        exec("open \"{$index}\"");
    } else {
        exec("xdg-open \"{$index}\"");
    }
    echo "🌐 已在浏览器中打开预览页。\n";
}
