<?php

namespace Vegfund\Jotta\Tests\Unit\_003_Scopes;

use Illuminate\Support\Str;
use Vegfund\Jotta\Client\Responses\Namespaces\User;
use Vegfund\Jotta\Jotta;

class Test001_AccountTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Vegfund\Jotta\Client\Scopes\AccountScope::index
     */
    public function test001_should_return_account_data()
    {
        $data = Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'))->account()->data();

        $this->assertInstanceOf(User::class, $data);

        $this->assertSame(getenv('JOTTA_USERNAME'), $data->getUsername());
        $this->assertSame(getenv('JOTTA_USERNAME'), $data->username);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\AccountScope::index
     */
    public function test003_should_throw_exception()
    {
        $exception = null;

        try {
            $data = Jotta::client(Str::random(32), getenv('JOTTA_PASSWORD'))->account()->data();
        } catch (\Exception $e) {
            $exception = $e;
        }
        $this->assertInstanceOf(\Exception::class, $exception);

        $exception = null;

        try {
            $data = Jotta::client(getenv('JOTTA_USERNAME'), Str::random(32))->account()->data();
        } catch (\Exception $e) {
            $exception = $e;
        }
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\AccountScope::index
     */
    public function test005_should_return_null()
    {
        $this->assertNull(Jotta::client(Str::random(32), getenv('JOTTA_PASSWORD'))->account()->withoutExceptions()->data());
        $this->assertNull(Jotta::client(getenv('JOTTA_USERNAME'), Str::random(32))->account()->withoutExceptions()->data());
    }
}