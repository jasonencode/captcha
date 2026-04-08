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
        $this->repository->shouldReceive('get')->with('captcha.characters', ['1', '2', '3', '4', '6', '7', '8', '9'])->andReturn(['1', '2', '3']);
        $this->repository->shouldReceive('get')->with('captcha.default', [])->andReturn(['length' => 6, 'width' => 150]);

        $result = $this->config->get('default');

        $this->assertIsArray($result);
        $this->assertEquals(['1', '2', '3'], $result['characters']);
        $this->assertEquals(6, $result['length']);
        $this->assertEquals(150, $result['width']);
        $this->assertEquals(36, $result['height']); // Default value
        $this->assertEquals(3, $result['lines']); // Default value
    }

    public function testGetReturnsDefaultConfigWhenStyleNotExists(): void
    {
        $this->repository->shouldReceive('get')->with('captcha.characters', ['1', '2', '3', '4', '6', '7', '8', '9'])->andReturn(['1', '2', '3']);
        $this->repository->shouldReceive('get')->with('captcha.nonexistent', [])->andReturn([]);

        $result = $this->config->get('nonexistent');

        $this->assertIsArray($result);
        $this->assertEquals(['1', '2', '3'], $result['characters']);
        $this->assertEquals(4, $result['length']); // Default value
        $this->assertEquals(120, $result['width']); // Default value
        $this->assertEquals(36, $result['height']); // Default value
    }

    public function testGetUsesDefaultCharactersWhenNotSet(): void
    {
        $this->repository->shouldReceive('get')->with('captcha.characters', ['1', '2', '3', '4', '6', '7', '8', '9'])->andReturn(['1', '2', '3', '4', '6', '7', '8', '9']);
        $this->repository->shouldReceive('get')->with('captcha.default', [])->andReturn([]);

        $result = $this->config->get('default');

        $this->assertIsArray($result);
        $this->assertEquals(['1', '2', '3', '4', '6', '7', '8', '9'], $result['characters']);
    }

    public function testGetMergesAllConfigLayers(): void
    {
        $this->repository->shouldReceive('get')->with('captcha.characters', ['1', '2', '3', '4', '6', '7', '8', '9'])->andReturn(['A', 'B', 'C']);
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
        $this->assertEquals(90, $result['quality']); // Default value
        $this->assertEquals(false, $result['sensitive']); // Default value
    }
}
