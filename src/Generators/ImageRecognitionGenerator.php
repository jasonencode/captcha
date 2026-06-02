<?php

namespace Jason\Captcha\Generators;

use Jason\Captcha\Contracts\CaptchaGenerator;
use Random\RandomException;

class ImageRecognitionGenerator implements CaptchaGenerator
{
    private const ANIMAL = 1;
    private const FRUIT = 2;
    private const OBJECT = 3;

    /**
     * @throws RandomException
     */
    public function generate(array $config): array
    {
        $type = random_int(1, 3);

        return match ($type) {
            self::ANIMAL => $this->animalRecognition(),
            self::FRUIT => $this->fruitRecognition(),
            self::OBJECT => $this->objectRecognition(),
        };
    }

    /**
     * 动物识别
     */
    private function animalRecognition(): array
    {
        $animals = [
            'cat' => '猫',
            'dog' => '狗',
            'bird' => '鸟',
            'fish' => '鱼',
            'rabbit' => '兔子',
        ];

        return $this->generateImageRecognition($animals, '请选择');
    }

    /**
     * 水果识别
     */
    private function fruitRecognition(): array
    {
        $fruits = [
            'apple' => '苹果',
            'banana' => '香蕉',
            'orange' => '橙子',
            'grape' => '葡萄',
            'strawberry' => '草莓',
        ];

        return $this->generateImageRecognition($fruits, '请选择');
    }

    /**
     * 物体识别
     */
    private function objectRecognition(): array
    {
        $objects = [
            'car' => '汽车',
            'bicycle' => '自行车',
            'airplane' => '飞机',
            'ship' => '船',
            'train' => '火车',
        ];

        return $this->generateImageRecognition($objects, '请选择');
    }

    /**
     * 生成图片识别验证码
     */
    private function generateImageRecognition(array $items, string $prefix): array
    {
        // 随机选择一个目标
        $keys = array_keys($items);
        $targetKey = $keys[random_int(0, count($keys) - 1)];
        $targetName = $items[$targetKey];

        // 生成问题
        $question = "{$prefix} {$targetName}";

        // 生成选项（包含目标和其他干扰项）
        $options = [$targetKey];
        while (count($options) < 4) {
            $randomKey = $keys[random_int(0, count($keys) - 1)];
            if (!in_array($randomKey, $options)) {
                $options[] = $randomKey;
            }
        }
        shuffle($options);

        // 生成显示内容
        $displayItems = array_map(function ($key) use ($items) {
            return $items[$key];
        }, $options);

        return [
            'value' => $this->formatQuestion($question, $displayItems),
            'key' => $targetKey,
            'sensitive' => false,
        ];
    }

    /**
     * 格式化问题显示
     */
    private function formatQuestion(string $question, array $options): array
    {
        $optionsStr = implode(' | ', $options);
        $parts = array_merge($this->mbStrSplit($question), [' '], $this->mbStrSplit($optionsStr));

        return $parts;
    }

    /**
     * 中文字符串分割
     */
    private function mbStrSplit(string $string): array
    {
        return preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }
}