<?php

namespace Vegfund\Jotta\Tests\Support;

use PHPUnit\Framework\TestCase;
use Vegfund\Jotta\Jotta;
use Vegfund\Jotta\Tests\Mock\JottaApiV1Mock;

/**
 * Trait JottaClient
 * @package Vegfund\Jotta\Tests\Support
 * @mixin TestCase
 */
trait JottaTestTrait
{
    /**
     * @return \Vegfund\Jotta\JottaClient
     */
    public function jotta()
    {
        return Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'));
    }

    /**
     * @return \Vegfund\Jotta\JottaClient
     */
    public function jottaMock()
    {
        $mock = new JottaApiV1Mock($body);
        $jotta = new \Vegfund\Jotta\JottaClient('a', 'b', $mock->getMock());

        return $jotta;
    }
}