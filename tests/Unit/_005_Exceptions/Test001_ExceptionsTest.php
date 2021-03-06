<?php

namespace Vegfund\Jotta\Tests\Unit\_005_Exceptions;

use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Str;
use Vegfund\Jotta\Client\Exceptions\JottaException;
use Vegfund\Jotta\Client\Responses\XmlResponseSerializer;
use Vegfund\Jotta\Client\Scopes\Scope;
use Vegfund\Jotta\JottaClient;
use Vegfund\Jotta\Tests\JottaTestCase;
use Vegfund\Jotta\Tests\Mock\ResponseBodyMock;

class Test001_ExceptionsTest extends JottaTestCase
{
    /**
     * @covers \Vegfund\Jotta\Client\Responses\XmlResponseSerializer::getRootNamespace
     */
    public function test001_exception_parse()
    {
        $body = '<?xml version="1.0" encoding="UTF-8"?>
                    <error>
                        <code>404</code>
                        <message>no.jotta.backup.errors.NoSuchMountPointException</message>
                        <reason>Not Found</reason>
                        <cause></cause>
                        <hostname></hostname>
                        <x-id>156394042147</x-id>
                    </error>';

        try {
            XmlResponseSerializer::parse($body, 'auto');
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertSame(404, $e->getCode());
            $this->assertSame('no.jotta.backup.errors.NoSuchMountPointException', $e->getMessage());
        }
    }

    /**
     * @covers \Vegfund\Jotta\Client\Exceptions\JottaException::__construct
     */
    public function test003_jotta_exception()
    {
        $string = Str::random(32);
        $code = rand(400, 599);

        try {
            throw new JottaException($string, $code);
        } catch (\Exception $e) {
            $this->assertInstanceOf(JottaException::class, $e);
            $this->assertSame($string, $e->getMessage());
            $this->assertSame($code, $e->getCode());
        }
    }

    /**
     * @covers \Vegfund\Jotta\JottaClient::handleException
     *
     * @throws \ReflectionException
     */
    public function test005_jotta_client_handle_exception_plain()
    {
        $method = new \ReflectionMethod(JottaClient::class, 'handleException');
        $method->setAccessible(true);
        $mock = \Mockery::mock(JottaClient::class);
        $mock->makePartial();

        $exception = new \Exception('message', 111);

        try {
            $method->invoke($mock, $exception);
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
            $this->assertSame('message', $e->getMessage());
            $this->assertSame(111, $e->getCode());
        }

        $request = \Mockery::mock(Request::class);
        $request->makePartial();

        $exception = new ServerException('message', $request);

        try {
            $method->invoke($mock, $exception);
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertInstanceOf(ServerException::class, $e);
            $this->assertSame('message', $e->getMessage());
        }
    }

    /**
     * @covers \Vegfund\Jotta\JottaClient::handleException
     */
    public function test006a_jotta_client_test_xml_error_exception()
    {
        $body = (new ResponseBodyMock())->error();
        $mock = $this->jottaMock($body);
        $this->shouldThrowException(\Exception::class, function () use ($mock) {
            $mock->account()->data();
        });
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\Scope::serialize
     *
     * @throws \ReflectionException
     */
    public function test007_scope_handle_other_exception()
    {
        $method = new \ReflectionMethod(Scope::class, 'serialize');
        $method->setAccessible(true);
        $mock = \Mockery::mock(Scope::class);
        $mock->makePartial();

        try {
            $method->invoke($mock, ['a' => 'b', 'c' => 'd']);
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }
}
