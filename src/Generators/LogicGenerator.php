<?php

namespace Jason\Captcha\Generators;

use Jason\Captcha\Contracts\CaptchaGenerator;
use Random\RandomException;

class LogicGenerator implements CaptchaGenerator
{
    private const ODD_NUMBER    = 1;
    private const EVEN_NUMBER  = 2;
    private const COMPARE      = 3;
    private const SEQUENCE     = 4;
    private const WORD_GAME    = 5;

    /**
     * @throws RandomException
     */
    public function generate(array $config): array
    {
        $type = random_int(1, 5);

        return match ($type) {
            self::ODD_NUMBER  => $this->oddOrEven(),
            self::EVEN_NUMBER => $this->oddOrEven(isEven: true),
            self::COMPARE    => $this->compare(),
            self::SEQUENCE   => $this->sequence(),
            self::WORD_GAME  => $this->wordGame(),
        };
    }

    /**
     * 奇偶数判断
     */
    private function oddOrEven(bool $isEven = false): array
    {
        $num  = random_int(10, 99);
        $isEvenNum = $num % 2 === 0;

        $question = $isEven ? '偶数是哪个？' : '奇数是哪个？';

        $numbers = $this->generateOptions($num, $isEvenNum);
        $answerKey = (string) $num; // 使用数字本身作为key，确保类型一致

        return [
            'value'     => $this->formatQuestion($question, $numbers),
            'key'       => $answerKey,
            'sensitive' => false,
        ];
    }

    /**
     * 数字比较大小
     */
    private function compare(): array
    {
        $a = random_int(1, 50);
        $b = random_int(51, 99);

        $larger = max($a, $b);

        $question = "哪个数字更大？";

        return [
            'value'     => $this->formatQuestion($question, [$a, $b]),
            'key'       => (string) $larger,
            'sensitive' => false,
        ];
    }

    /**
     * 找规律填数字
     */
    private function sequence(): array
    {
        $type = random_int(1, 3);

        $question = match ($type) {
            1 => '找规律，下一个数字是？',
            2 => '数列规律，下一个？',
            3 => '找规律，？处填几？',
        };

        // 等差数列
        if ($type === 1) {
            $start = random_int(1, 5);
            $step  = random_int(2, 5);
            $seq   = [$start, $start + $step, $start + $step * 2];
            $next  = $start + $step * 3;
            $answer = $next;
            $displaySeq = implode(', ', $seq) . ', ?';
        }
        // 倍数数列
        elseif ($type === 2) {
            $start = random_int(1, 3);
            $multiplier = random_int(2, 3);
            $seq = [$start, $start * $multiplier, $start * $multiplier * $multiplier];
            $next = $start * pow($multiplier, 3);
            $answer = $next;
            $displaySeq = implode(', ', $seq) . ', ?';
        }
        // 简单加减
        else {
            $a = random_int(5, 15);
            $b = random_int(1, 9);
            $seq = [$a, $a + $b, $a + $b * 2];
            $next = $a + $b * 3;
            $answer = $next;
            $displaySeq = implode(', ', $seq) . ', ?';
        }

        return [
            'value'     => $this->formatSequence($question, $displaySeq),
            'key'       => (string) $answer,
            'sensitive' => false,
        ];
    }

    /**
     * 文字游戏/脑筋急转弯
     */
    private function wordGame(): array
    {
        $games = [
            [
                'question' => '1 + 1 = ？',
                'answer'   => '2',
            ],
            [
                'question' => '2 × 3 = ？',
                'answer'   => '6',
            ],
            [
                'question' => '3 的平方 = ？',
                'answer'   => '9',
            ],
            [
                'question' => '10 ÷ 2 = ？',
                'answer'   => '5',
            ],
            [
                'question' => '5 - 2 = ？',
                'answer'   => '3',
            ],
            [
                'question' => '7 + 8 = ？',
                'answer'   => '15',
            ],
            [
                'question' => '12 - 5 = ？',
                'answer'   => '7',
            ],
            [
                'question' => '4 × 4 = ？',
                'answer'   => '16',
            ],
        ];

        $game = $games[random_int(0, count($games) - 1)];

        return [
            'value'     => $this->mbStrSplit($game['question']),
            'key'       => $game['answer'],
            'sensitive' => false,
        ];
    }

    /**
     * 生成选项数字
     */
    private function generateOptions(int $target, bool $isTargetEven): array
    {
        $numbers = [$target];

        while (count($numbers) < 4) {
            $num = random_int(10, 99);
            $numIsEven = $num % 2 === 0;

            if ($numIsEven !== $isTargetEven && ! in_array($num, $numbers)) {
                $numbers[] = $num;
            }
        }

        shuffle($numbers);

        return $numbers;
    }

    /**
     * 格式化问题显示
     */
    private function formatQuestion(string $question, array $numbers): array
    {
        $options = implode(' | ', $numbers);
        $parts = array_merge($this->mbStrSplit($question), [' '], $this->mbStrSplit($options));

        return $parts;
    }

    /**
     * 格式化数列显示
     */
    private function formatSequence(string $question, string $sequence): array
    {
        $parts = array_merge($this->mbStrSplit($question), [' '], $this->mbStrSplit($sequence));

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
