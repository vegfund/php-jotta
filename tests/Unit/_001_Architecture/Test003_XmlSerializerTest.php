<?php

namespace Vegfund\Jotta\Tests\Unit\_001_Architecture;

use Vegfund\Jotta\Client\Responses\Namespaces\Attributes;
use Vegfund\Jotta\Client\Responses\Namespaces\MountPoint;
use Vegfund\Jotta\Client\Responses\XmlResponseSerializer;
use Vegfund\Jotta\Tests\JottaTestCase;
use Vegfund\Jotta\Tests\Mock\ResponseBodyMock;

class Test003_XmlSerializerTest extends JottaTestCase
{
    /**
     * @covers \Vegfund\Jotta\Client\Responses\XmlResponseSerializer::getRootNamespace
     *
     * @throws \ReflectionException
     */
    public function test001_detect_root()
    {
        $responseBodyMock = new ResponseBodyMock();

        $method = new \ReflectionMethod(XmlResponseSerializer::class, 'getRootNamespace');
        $method->setAccessible(true);
        $mock = \Mockery::mock(XmlResponseSerializer::class);
        $mock->makePartial();

        $newHeaders = [
            'user'       => $responseBodyMock->user(),
            'device'     => $responseBodyMock->device(),
            'mountPoint' => $responseBodyMock->mountPoint(),
            'folder'     => $responseBodyMock->folder(),
            'file'       => $responseBodyMock->file(),
        ];

        foreach ($newHeaders as $namespace => $body) {
            $rootNms = $method->invoke($mock, $body);
            $this->assertSame($namespace, $rootNms);
        }
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\XmlResponseSerializer::getRaw
     *
     * @throws \Sabre\Xml\ParseException
     */
    public function test003_raw_body()
    {
        $body = '<?xml version="1.0" encoding="UTF-8"?>
                    <mountPoint time="2020-03-16-T09:47:15Z" host="**obfuscated**">
                        <name xml:space="preserve">Archive</name>
                        <path xml:space="preserve">/**obfuscated**/Jotta</path>
                        <abspath xml:space="preserve">/**obfuscated**/Jotta</abspath>
                        <size>383654</size>
                        <modified>2020-03-12-T22:57:05Z</modified>
                        <device>Jotta</device>
                        <user>**obfuscated**</user>
                    </mountPoint>';

        $serialized = new XmlResponseSerializer($body, 'auto');
        $this->assertSame($body, $serialized->getRaw());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\ResponseNamespace::getAttribute
     * @covers \Vegfund\Jotta\Client\Responses\ResponseNamespace::setAttributes
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\Attributes::get
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\Attributes::all
     *
     * @throws \Sabre\Xml\ParseException
     */
    public function test005_attributes()
    {
        $body = '<?xml version="1.0" encoding="UTF-8"?>
                    <mountPoint time="2020-03-16-T09:47:15Z" host="**obfuscated**">
                        <name xml:space="preserve">Archive</name>
                        <path xml:space="preserve">/**obfuscated**/Jotta</path>
                        <abspath xml:space="preserve">/**obfuscated**/Jotta</abspath>
                        <size>383654</size>
                        <modified>2020-03-12-T22:57:05Z</modified>
                        <device>Jotta</device>
                        <user>**obfuscated**</user>
                    </mountPoint>';

        $serialized = XmlResponseSerializer::parse($body, 'auto');

        $this->assertInstanceOf(MountPoint::class, $serialized);
        $this->assertInstanceOf(Attributes::class, $serialized->attributes);
        $this->assertTrue(is_array($serialized->attributes->all()));
        $this->assertSame('**obfuscated**', $serialized->getAttribute('host'));
        $this->assertSame('**obfuscated**', $serialized->attributes->all()['host']);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\ResponseNamespace::getAttribute
     *
     * @throws \Sabre\Xml\ParseException
     */
    public function test007_no_attributes()
    {
        $body = '<?xml version="1.0" encoding="UTF-8"?>
                    <mountPoint time="2020-03-16-T09:47:15Z" host="**obfuscated**">
                        <name xml:space="preserve">Archive</name>
                        <path xml:space="preserve">/**obfuscated**/Jotta</path>
                        <abspath xml:space="preserve">/**obfuscated**/Jotta</abspath>
                        <size>383654</size>
                        <modified>2020-03-12-T22:57:05Z</modified>
                        <device>Jotta</device>
                        <user>**obfuscated**</user>
                    </mountPoint>';

        $serialized = XmlResponseSerializer::parse($body, 'auto');

        $this->assertNull($serialized->getAttribute('nonexisting'));

        $body = '<?xml version="1.0" encoding="UTF-8"?>
                    <mountPoint>
                        <name xml:space="preserve">Archive</name>
                        <path xml:space="preserve">/**obfuscated**/Jotta</path>
                        <abspath xml:space="preserve">/**obfuscated**/Jotta</abspath>
                        <size>383654</size>
                        <modified>2020-03-12-T22:57:05Z</modified>
                        <device>Jotta</device>
                        <user>**obfuscated**</user>
                    </mountPoint>';

        $serialized = XmlResponseSerializer::parse($body, 'auto');

        $this->assertNull($serialized->getAttribute('nonexisting'));
        $this->assertObjectNotHasAttribute('nonexisting', $serialized);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\Attributes::__get
     */
    public function test007a_no_attributes()
    {
        $mock = \Mockery::mock(Attributes::class);
        $mock->makePartial();

        $this->assertNull($mock->get('nonexistant'));
    }
}
