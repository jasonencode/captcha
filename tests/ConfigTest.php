<?php

namespace Jason\Captcha\Tests;

use Illuminate\Contracts\Config\Repository;
use Jason\Captcha\Support\Config;
use Mockery;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class ConfigTest extends PHPUnitTestCase
{
    protected $config;
    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(Repository::class);
        $this->config = new Config($this->repository);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testGetReturnsMergedConfig(): void
    {
        $defaultChars = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];
        $this->repository->shouldReceive('get')->with('captcha.characters', $defaultChars)->andReturn(['1', '2', '3']);
        $this->repository->shouldReceive('get')->with('captcha.default', [])->andReturn(['length' => 6, 'width' => 150]);

        $result = $this->config->get('default');

        $this->assertIsArray($result);
        $this->assertEquals(['1', '2', '3'], $result['characters']);
        $this->assertEquals(6, $result['length']);
        $this->assertEquals(150, $result['width']);
        $this->assertEquals(36, $result['height']);
        $this->assertEquals(3, $result['lines']);
    }

    public function testGetReturnsDefaultConfigWhenStyleNotExists(): void
    {
        $defaultChars = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];
        $this->repository->shouldReceive('get')->with('captcha.characters', $defaultChars)->andReturn(['1', '2', '3']);
        $this->repository->shouldReceive('get')->with('captcha.nonexistent', [])->andReturn([]);

        $result = $this->config->get('nonexistent');

        $this->assertIsArray($result);
        $this->assertEquals(['1', '2', '3'], $result['characters']);
        $this->assertEquals(4, $result['length']);
        $this->assertEquals(120, $result['width']);
        $this->assertEquals(36, $result['height']);
    }

    public function testGetUsesDefaultCharactersWhenNotSet(): void
    {
        $defaultChars = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];
        $this->repository->shouldReceive('get')->with('captcha.characters', $defaultChars)->andReturn($defaultChars);
        $this->repository->shouldReceive('get')->with('captcha.default', [])->andReturn([]);

        $result = $this->config->get('default');

        $this->assertIsArray($result);
        $this->assertEquals($defaultChars, $result['characters']);
    }

    public function testGetMergesAllConfigLayers(): void
    {
        $defaultChars = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];
        $this->repository->shouldReceive('get')->with('captcha.characters', $defaultChars)->andReturn(['A', 'B', 'C']);
        $this->repository->shouldReceive('get')->with('captcha.custom', [])->andReturn([
            'length' => 8,
            'width' => 200,
            'height' => 50,
            'math' => true,
            'expire' => 120,
        ]);

        $result = $this->config->get('custom');

        $this->assertIsArray($result);
        $this->assertEquals(['A', 'B', 'C'], $result['characters']);
        $this->assertEquals(8, $result['length']);
        $this->assertEquals(200, $result['width']);
        $this->assertEquals(50, $result['height']);
        $this->assertEquals(true, $result['math']);
        $this->assertEquals(120, $result['expire']);
        $this->assertEquals(90, $result['quality']);
        $this->assertEquals(false, $result['sensitive']);
    }

    public function testGetChineseStyleConfig(): void
    {
        $defaultChars = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];
        $chineseChars = ['天', '地', '人'];

        $this->repository->shouldReceive('get')->with('captcha.characters', $defaultChars)->andReturn($defaultChars);
        $this->repository->shouldReceive('get')->with('captcha.chinese', [])->andReturn([
            'length' => 4,
            'width' => 150,
            'height' => 50,
            'sensitive' => true,
            'bgImage' => false,
            'bgColor' => '#ffffff',
            'lineColor' => '#cccccc',
            'marginTop' => 10,
            'textLeftPadding' => 10,
            'characters' => $chineseChars,
            'fontColors' => ['#2c3e50', '#c0392b'],
        ]);

        $result = $this->config->get('chinese');

        $this->assertIsArray($result);
        $this->assertEquals(4, $result['length']);
        $this->assertEquals(150, $result['width']);
        $this->assertEquals(50, $result['height']);
        $this->assertTrue($result['sensitive']);
        $this->assertFalse($result['bgImage']);
        $this->assertEquals('#ffffff', $result['bgColor']);
        $this->assertEquals('#cccccc', $result['lineColor']);
        $this->assertEquals(10, $result['marginTop']);
        $this->assertEquals(10, $result['textLeftPadding']);
        $this->assertEquals($chineseChars, $result['characters']);
        $this->assertEquals(['#2c3e50', '#c0392b'], $result['fontColors']);
        $this->assertEquals(90, $result['quality']);
        $this->assertEquals(15, $result['angle']);
    }
}
