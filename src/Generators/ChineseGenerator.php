<?php

namespace Jason\Captcha\Generators;

use Jason\Captcha\Contracts\CaptchaGenerator;
use Random\RandomException;

class ChineseGenerator implements CaptchaGenerator
{
    public static array $defaultCharacters = [
        // 方位·大小·数量
        '东', '西', '南', '北', '上', '下', '左', '右',
        '大', '小', '多', '少', '长', '短', '高', '低',
        // 天地·自然
        '天', '地', '山', '水', '火', '风', '云', '雨',
        '雪', '冰', '雷', '电', '日', '月', '星', '光',
        // 动物
        '龙', '凤', '虎', '马', '牛', '羊', '猪', '狗',
        '猫', '兔', '蛇', '猴', '鸡', '鼠', '鸟', '鱼',
        '虫', '龟', '鹤', '鹰', '鸽', '蝶', '蜂', '鸭',
        // 植物·花木
        '花', '草', '树', '木', '竹', '松', '柏', '梅',
        '兰', '菊', '荷', '桂', '桃', '李', '杏', '梨',
        // 颜色
        '红', '黄', '蓝', '绿', '黑', '白', '金', '银',
        '紫', '橙', '灰', '粉',
        // 季节·时间
        '春', '夏', '秋', '冬', '年', '月', '日', '时',
        // 数字·单位
        '一', '二', '三', '四', '五', '六', '七', '八',
        '九', '十', '百', '千', '万', '亿', '元', '角',
        // 身体
        '人', '口', '手', '足', '目', '耳', '鼻', '心',
        '头', '面', '牙', '舌',
        // 食物
        '米', '面', '豆', '糖', '盐', '茶', '酒', '饭',
        // 材料·器物
        '石', '田', '土', '铁', '铜', '纸', '笔', '刀',
        // 建筑
        '门', '窗', '墙', '桥', '路', '城', '塔', '亭',
        // 品质·情感
        '忠', '孝', '仁', '义', '礼', '智', '信', '勇',
        '爱', '恨', '喜', '怒', '哀', '乐', '福', '寿',
        // 动作
        '飞', '走', '跑', '跳', '看', '听', '说', '读',
        '写', '画', '唱', '舞',
        // 方正·常用
        '中', '华', '国', '家', '王', '玉', '文', '武',
        '明', '亮', '新', '旧', '好', '坏', '真', '善',
    ];

    /**
     * @throws RandomException
     */
    public function generate(array $config): array
    {
        $length = $config['length'] ?? 4;
        $characters = $config['characters'] ?? self::$defaultCharacters;
        $sensitive = $config['sensitive'] ?? true;

        $bag = [];
        $used = [];
        for ($i = 0; $i < $length; $i++) {
            do {
                $index = random_int(0, count($characters) - 1);
            } while (in_array($index, $used, true) && count($used) < count($characters));

            $used[] = $index;
            $bag[] = $characters[$index];
        }

        return [
            'value' => $bag,
            'key' => implode('', $bag),
            'sensitive' => $sensitive,
        ];
    }
}
