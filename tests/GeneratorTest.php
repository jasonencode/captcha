<?php

namespace Jason\Captcha\Tests;

use Jason\Captcha\Generators\MathGenerator;
use Jason\Captcha\Generators\StringGenerator;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class GeneratorTest extends PHPUnitTestCase
{
    public function testStringGenerator(): void
    {
        $generator = new StringGenerator();
        $config = ['length' => 6, 'characters' => ['A'], 'sensitive' => true];
        
        $result = $generator->generate($config);
        
        $this->assertIsArray($result['value']);
        $this->assertCount(6, $result['value']);
        $this->assertEquals('AAAAAA', $result['key']);
        $this->assertTrue($result['sensitive']);
    }

    public function testMathGenerator(): void
    {
        $generator = new MathGenerator();
        $result = $generator->generate([]);
        
        $this->assertIsArray($result['value']);
        $this->assertStringContainsString('+', $result['value'][0]);
        $this->assertIsString($result['key']);
        $this->assertFalse($result['sensitive']);
    }
}
