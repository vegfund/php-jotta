<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Tests\Mock;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

/**
 * Class JottaApiV1Mock.
 */
class JottaApiV1Mock
{
    /**
     * @var Client|LegacyMockInterface|MockInterface
     */
    protected $mock;

    /**
     * @var ResponseBodyMock
     */
    protected $responseBodyMock;

    /**
     * @var string
     */
    protected $baseUri = 'https://jottacloud.com/jfs';

    /**
     * JottaApiV1Mock constructor.
     */
    public function __construct()
    {
        $this->responseBodyMock = new ResponseBodyMock();

        $this->mock = Mockery::mock(Client::class);
        $this->mock->makePartial();
        $this->mock->shouldAllowMockingProtectedMethods();

        $this->mock->shouldReceive('request')
            ->andReturnUsing(function (...$args) {
                return $this->mockRequest(...$args);
            })
        ;
    }

    /**
     * Guzzle client mock getter.
     *
     * @return Client|LegacyMockInterface|MockInterface
     */
    public function getMock()
    {
        return $this->mock;
    }

    /**
     * Mock request for given arguments.
     *
     * @param $uri
     * @param $method
     * @param $options
     *
     * @return LegacyMockInterface|MockInterface|Response
     */
    protected function mockRequest($uri, $method, $options)
    {
        $mockResponse = Mockery::mock(Response::class);
        $mockResponse->makePartial();
        $mockResponse->shouldAllowMockingProtectedMethods();

        $mockResponse->shouldReceive('getBody')
            ->andReturn($this->prepareResponse($uri, $method, $options)['body'])
        ;

        return $mockResponse;
    }

    /**
     * @param $uri
     * @param $method
     * @param $options
     *
     * @return array
     */
    protected function prepareResponse($method, $uri, $options)
    {
        if ($uri === $this->implodePath([getenv('JOTTA_USERNAME')])) {
            return [
                'body' => $this->prepareResponseBody($this->getXmlBody('user')),
            ];
        }

        return [
            'body' => $this->prepareResponseBody('body'),
        ];
    }

    /**
     * @param $namespace
     *
     * @return string
     */
    protected function getXmlBody($namespace)
    {
        return $this->responseBodyMock->{$namespace}();
    }

    /**
     * Mock stream.
     *
     * @return LegacyMockInterface|MockInterface|Stream
     */
    protected function mockStream(string $body)
    {
        $mockStream = Mockery::mock(Stream::class);
        $mockStream->makePartial();
        $mockStream->shouldAllowMockingProtectedMethods();

        $mockStream->shouldReceive('__toString')
            ->andReturn($body)
        ;

        return $mockStream;
    }

    /**
     * Prepare response body by mocking Stream object.
     *
     * @param $uri
     * @param $method
     * @param $options
     * @param mixed $body
     *
     * @return LegacyMockInterface|MockInterface|Stream
     */
    protected function prepareResponseBody($body)
    {
        return $this->mockStream($body);
    }

    /**
     * Mock ClientException object.
     *
     * @return ClientException|LegacyMockInterface|MockInterface
     */
    protected function mockClientException(int $code, string $message)
    {
        $mockException = Mockery::mock(ClientException::class);
        $mockException->makePartial();
        $mockException->shouldAllowMockingProtectedMethods();

        return $mockException;
    }

    /**
     * @param array $segments
     *
     * @return string
     */
    protected function implodePath($segments = [])
    {
        return implode('/', array_merge([$this->baseUri], $segments));
    }
}
