<?php

namespace Vegfund\Jotta\Tests;

use PHPUnit\Framework\TestCase;
use Vegfund\Jotta\Tests\Support\AssertExceptions;
use Vegfund\Jotta\Tests\Support\JottaTestTrait;

/**
 * Class JottaTestCase
 * @package Vegfund\Jotta\Tests
 */
class JottaTestCase extends TestCase
{
    use AssertExceptions;
    use JottaTestTrait;

    /**
     * @param string $filename
     * @return string
     */
    public function tempPath($filename = '')
    {
        return __DIR__.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$filename;
    }
}