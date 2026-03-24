# Captcha for Laravel 10/11/12/13

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://packagist.org/packages/jason/captcha)
[![PHP Version](https://img.shields.io/badge/php-%5E8.2-777bb4.svg)](https://packagist.org/packages/jason/captcha)

一个为 Laravel 10/11/12/13 深度定制的现代化验证码组件。基于 [Intervention Image v3](https://image.intervention.io/v3) 构建，采用模块化设计，易于扩展和维护。

## 特性

- **PHP 8.2+** 强类型支持。
- **Intervention Image v3**：使用最新的图像处理库，支持 GD 和 Imagick 驱动。
- **模块化架构**：解耦了验证码生成（Generators）、配置管理（Support）和图像绘制（ImageCreator）。
- **性能优化**：内置静态资源缓存，显著减少磁盘 I/O。
- **无状态 API 支持**：完美支持前后端分离项目。
- **高度可定制**：轻松定义多种验证码样式。

## 预览

![Preview Example](https://image.ibb.co/kZxMLm/image.png)

## 安装

通过 Composer 安装：

```bash
composer require jason/captcha
```

在 Windows 环境下，确保 `php.ini` 中启用了 `php_gd.dll` 或 `php_imagick.dll`。

## 配置

发布配置文件：

```bash
php artisan vendor:publish --provider="Jason\Captcha\CaptchaServiceProvider" --tag="config"
```

在 `config/captcha.php` 中，你可以定义多个验证码样式：

```php
return [
    'default' => [
        'length' => 5,
        'width' => 120,
        'height' => 36,
        'quality' => 90,
        'math' => false,
        'expire' => 60,
    ],
    'math' => [
        'length' => 9,
        'width' => 120,
        'height' => 36,
        'math' => true,
    ],
    // ... 更多样式
];
```

## 使用方法

### 1. 传统 Session 模式

在视图中显示验证码：

```html
<form method="POST" action="/register">
    @csrf
    <div>
        {!! captcha_img() !!}
        <input type="text" name="captcha" required>
    </div>
    <button type="submit">提交</button>
</form>
```

在控制器中进行验证：

```php
public function store(Request $request)
{
    $request->validate([
        'captcha' => 'required|captcha',
    ]);

    // 验证通过后的逻辑...
}
```

### 2. 无状态 API 模式

获取验证码数据（JSON 响应）：
`GET /captcha/api/default`

返回示例：
```json
{
    "sensitive": false,
    "key": "$2y$10$...", 
    "img": "data:image/jpeg;base64,..."
}
```

前端验证时，需将 `key` 一并传回后端：

```php
public function apiStore(Request $request)
{
    $request->validate([
        'captcha' => 'required|captcha_api:' . $request->input('key') . ',default',
    ]);

    // 验证通过后的逻辑...
}
```

## 辅助函数

- `captcha()`: 返回验证码图像响应。
- `captcha_src(string $style = 'default')`: 返回验证码图片的 URL 字符串。
- `captcha_img(string $style = 'default', array $attrs = [])`: 返回验证码图片的 HTML `<img>` 标签。
- `captcha_check(string $value)`: 手动验证 Session 模式下的验证码。
- `captcha_api_check(string $value, string $key, string $style = 'default')`: 手动验证 API 模式下的验证码。

## 架构

重构后的模块化设计：
- **[CaptchaGenerator](src/Contracts/CaptchaGenerator.php)**: 负责验证码内容的生成逻辑（字符串、算术等）。
- **[ImageCreator](src/Image/ImageCreator.php)**: 负责将生成的内容绘制成图像。
- **[Support/Config](src/Support/Config.php)**: 处理配置合并与默认值。
- **[Facades/Captcha](src/Facades/Captcha.php)**: 提供简洁的 Facade 访问接口。

## 鸣谢

本项目基于原 `mews/captcha` 进行现代化重构。

## 协议

[MIT License](LICENSE)
