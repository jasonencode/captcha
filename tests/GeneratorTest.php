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
        
        // Test multiple times to cover both + and -
        for ($i = 0; $i < 20; $i++) {
            $result = $generator->generate([]);
            
            $this->assertIsArray($result['value']);
            $this->assertContains('=', $result['value']);
            
            $expression = implode('', $result['value']);
            if (str_contains($expression, '+')) {
                $this->assertStringContainsString('+', $expression);
            } else {
                $this->assertStringContainsString('-', $expression);
            }
            
            $this->assertIsString($result['key']);
            $this->assertGreaterThanOrEqual(0, (int)$result['key']);
            $this->assertFalse($result['sensitive']);
        }
    }
}
