<?php

namespace Vegfund\Jotta\Tests\Support;

use PHPUnit\Framework\TestCase;

/**
 * Trait AssertExceptions.
 *
 * @mixin TestCase
 */
trait AssertExceptions
{
    /**
     * @param $exceptionClass
     * @param $callback
     */
    public function shouldThrowException($exceptionClass, $callback)
    {
        try {
            $callback();
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertInstanceOf($exceptionClass, $e);
        }
    }

    /**
     * @param $callback
     */
    public function shouldNotThrowException($callback)
    {
        try {
            $callback();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false, get_class($e) . ' ' . $e->getMessage());
        }
    }
}
