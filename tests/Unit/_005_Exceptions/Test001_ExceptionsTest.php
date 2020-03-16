<?php

namespace Vegfund\Jotta\Tests\Unit\_005_Exceptions;

use Vegfund\Jotta\Client\Responses\XmlResponseSerializer;

class Test001_ExceptionsTest extends \PHPUnit\Framework\TestCase
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
}
