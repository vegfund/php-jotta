<?php

namespace Vegfund\Jotta\Tests\Unit\_003_Scopes;

use Illuminate\Support\Str;
use Vegfund\Jotta\Client\Responses\Namespaces\User;
use Vegfund\Jotta\Jotta;
use Vegfund\Jotta\Tests\JottaTestCase;
use Vegfund\Jotta\Tests\Support\AssertExceptions;

class Test001_AccountTest extends JottaTestCase
{

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\AccountScope::data
     * @covers \Vegfund\Jotta\Client\Scopes\AccountScope::getUsername
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
     * @covers \Vegfund\Jotta\Client\Scopes\AccountScope::getUsername
     */
    public function test001a_should_return_account_data2()
    {
        $data = Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'))->account()->index();

        $this->assertInstanceOf(User::class, $data);

        $this->assertSame(getenv('JOTTA_USERNAME'), $data->getUsername());
        $this->assertSame(getenv('JOTTA_USERNAME'), $data->username);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\AccountScope::index
     */
    public function test003_should_throw_exception()
    {
        $this->shouldThrowException(\Exception::class, function () {
            Jotta::client(Str::random(32), getenv('JOTTA_PASSWORD'))->account()->data();
        });
        $this->shouldThrowException(\Exception::class, function () {
            Jotta::client(getenv('JOTTA_USERNAME'), Str::random(32))->account()->data();
        });
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
