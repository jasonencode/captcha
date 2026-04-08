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

    public function testStringGeneratorWithDefaultConfig(): void
    {
        $generator = new StringGenerator();
        $config = [];
        
        $result = $generator->generate($config);
        
        $this->assertIsArray($result['value']);
        $this->assertCount(4, $result['value']); // Default length
        $this->assertIsString($result['key']);
        $this->assertEquals(4, strlen($result['key']));
        $this->assertFalse($result['sensitive']); // Default sensitive
    }

    public function testStringGeneratorWithNonSensitiveMode(): void
    {
        $generator = new StringGenerator();
        $config = ['length' => 4, 'characters' => ['A', 'B', 'C'], 'sensitive' => false];
        
        $result = $generator->generate($config);
        
        $this->assertIsArray($result['value']);
        $this->assertCount(4, $result['value']);
        $this->assertIsString($result['key']);
        $this->assertEquals(strtolower($result['key']), $result['key']);
        $this->assertFalse($result['sensitive']);
    }

    public function testStringGeneratorWithStringCharacters(): void
    {
        $generator = new StringGenerator();
        $config = ['length' => 3, 'characters' => '123', 'sensitive' => true];
        
        $result = $generator->generate($config);
        
        $this->assertIsArray($result['value']);
        $this->assertCount(3, $result['value']);
        $this->assertIsString($result['key']);
        $this->assertEquals(3, strlen($result['key']));
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

    public function testMathGeneratorSubtractionAlwaysNonNegative(): void
    {
        $generator = new MathGenerator();
        
        // Test multiple times to ensure subtraction results are always non-negative
        for ($i = 0; $i < 50; $i++) {
            $result = $generator->generate([]);
            
            $expression = implode('', $result['value']);
            if (str_contains($expression, '-')) {
                $this->assertStringContainsString('-', $expression);
                $this->assertGreaterThanOrEqual(0, (int)$result['key']);
            }
        }
    }
}

