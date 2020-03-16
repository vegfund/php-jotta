<?php

namespace Vegfund\Jotta\Tests\Unit\_001_Architecture;

use Vegfund\Jotta\Client\Responses\XmlResponseSerializer;
use Vegfund\Jotta\Tests\Mock\ResponseBodyMock;

class Test003_XmlSerializerTest extends \PHPUnit\Framework\TestCase
{
    public function test001_detect_root()
    {
        $responseBodyMock = new ResponseBodyMock();

        $method = new \ReflectionMethod(XmlResponseSerializer::class, 'getRootNamespace');
        $method->setAccessible(true);
        $mock = \Mockery::mock(XmlResponseSerializer::class);
        $mock->makePartial();

        $newHeaders = [
            'user' => $responseBodyMock->user(),
            'device' => $responseBodyMock->device(),
            'mountPoint' => $responseBodyMock->mountPoint(),
            'folder' => $responseBodyMock->folder(),
            'file' => $responseBodyMock->file()
        ];

        foreach($newHeaders as $namespace => $body) {
            $rootNms = $method->invoke($mock, $body);
            $this->assertSame($namespace, $rootNms);
        }
    }

    public function test003_parse_body()
    {
    }

    public function test005_attributes()
    {
    }
}
