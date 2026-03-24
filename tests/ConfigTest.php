<?php

namespace Jason\Captcha\Tests;

use Jason\Captcha\Support\Config;
use Illuminate\Contracts\Config\Repository;
use Mockery;

class ConfigTest extends TestCase
{
    public function testGetStyleConfig(): void
    {
        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('get')->with('captcha.characters', Mockery::any())->andReturn(['1', '2']);
        $repository->shouldReceive('get')->with('captcha.default', [])->andReturn(['length' => 4]);

        $config = new Config($repository);
        $styleConfig = $config->get('default');

        $this->assertEquals(4, $styleConfig['length']);
        $this->assertEquals(['1', '2'], $styleConfig['characters']);
        $this->assertEquals(120, $styleConfig['width']); // default value
    }
}
