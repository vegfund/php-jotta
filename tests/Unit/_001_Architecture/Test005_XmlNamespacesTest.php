<?php

namespace Vegfund\Jotta\Tests\Unit\_001_Architecture;

use Vegfund\Jotta\Client\Exceptions\JottaException;
use Vegfund\Jotta\Client\Responses\Namespaces\CurrentRevision;
use Vegfund\Jotta\Client\Responses\Namespaces\Device;
use Vegfund\Jotta\Client\Responses\Namespaces\File;
use Vegfund\Jotta\Client\Responses\Namespaces\Folder;
use Vegfund\Jotta\Client\Responses\Namespaces\Metadata;
use Vegfund\Jotta\Client\Responses\Namespaces\MountPoint;
use Vegfund\Jotta\Client\Responses\Namespaces\User;
use Vegfund\Jotta\Client\Responses\ResponseNamespace;
use Vegfund\Jotta\Client\Responses\XmlResponseSerializer;
use Vegfund\Jotta\Tests\Mock\ResponseBodyMock;

class Test005_XmlNamespacesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Vegfund\Jotta\Client\Responses\ElementMapper::file
     * @covers \Vegfund\Jotta\Client\Responses\ElementMapper::currentRevision
     *
     * @throws \Sabre\Xml\ParseException
     */
    public function test001_current_revision()
    {
        $responseBodyMock = new ResponseBodyMock();

        $body = $responseBodyMock->file();
        $serialized = XmlResponseSerializer::parse($body, 'auto');

        $this->assertInstanceOf(File::class, $serialized);
        $this->assertInstanceOf(CurrentRevision::class, $serialized->getCurrentRevision());

        $currentRevision = $serialized->getCurrentRevision();

        $this->assertIsInt($currentRevision->getNumber());
        $this->assertIsInt($currentRevision->getSize());
        $this->assertIsString($currentRevision->getState());
        $this->assertIsString($currentRevision->getMd5());
        $this->assertSame(32, strlen($currentRevision->getMd5()));
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\ElementMapper::device
     *
     * @throws \Sabre\Xml\ParseException
     */
    public function test003_device()
    {
        $responseBodyMock = new ResponseBodyMock();

        $body = $responseBodyMock->device();
        $serialized = XmlResponseSerializer::parse($body, 'auto');

        $this->assertInstanceOf(Device::class, $serialized);
        $this->assertIsArray($serialized->getMountPoints());
        $this->assertInstanceOf(\DateTime::class, $serialized->getModified());
        $this->assertIsInt($serialized->getSize());
        $this->assertIsString($serialized->getName());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::getAttribute
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::__call
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::__get
     *
     * @throws \Sabre\Xml\ParseException
     */
    public function test007_file()
    {
        $responseBodyMock = new ResponseBodyMock();

        $body = $responseBodyMock->file();
        $serialized = XmlResponseSerializer::parse($body, 'auto');

        $this->assertInstanceOf(File::class, $serialized);
        $this->assertIsString($serialized->getPath());
        $this->assertSame($serialized->getPath(), $serialized->path);
        $this->assertInstanceOf(CurrentRevision::class, $serialized->getCurrentRevision());
        $this->assertNotNull($serialized->getAttribute('uuid'));
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\ElementMapper::folder
     *
     * @throws \Sabre\Xml\ParseException
     */
    public function test009_folder()
    {
        $responseBodyMock = new ResponseBodyMock();

        $body = $responseBodyMock->folder();
        $serialized = XmlResponseSerializer::parse($body, 'auto');

        $this->assertInstanceOf(Folder::class, $serialized);
        $this->assertIsArray($serialized->getFolders());
        $this->assertIsString($serialized->getPath());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\ElementMapper::metadata()
     *
     * @throws \Sabre\Xml\ParseException
     */
    public function test011_metadata()
    {
        $body = '<?xml version="1.0" encoding="UTF-8"?>
                <folder name="Somefolder" time="2020-03-16-T13:59:17Z" host="**obfuscated**">
                    <path xml:space="preserve">/**obfuscated**/Jotta/Sync</path>
                    <abspath xml:space="preserve">/**obfuscated**/Jotta/Sync</abspath>
                    <folders>
                        <folder name="Ideas">
                            <abspath xml:space="preserve">/**obfuscated**/Jotta/Sync/Somefolder</abspath>
                        </folder>
                    </folders>
                    <files>
                    </files>
                    <metadata first="" max="" total="8" num_folders="1" num_files="7"/>
                </folder>';

        $folder = XmlResponseSerializer::parse($body, 'auto');
        $this->assertInstanceOf(Metadata::class, $folder->getMetadata());
        $metadata = $folder->getMetadata();

        $this->assertSame(1, (int) $metadata->getAttribute('num_folders'));
        $this->assertSame(7, (int) $metadata->getAttribute('num_files'));
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\ElementMapper::mountPoint
     *
     * @throws \Sabre\Xml\ParseException
     */
    public function test013_mount_point()
    {
        $responseBodyMock = new ResponseBodyMock();

        $body = $responseBodyMock->mountPoint();
        $serialized = XmlResponseSerializer::parse($body, 'auto');

        $this->assertInstanceOf(MountPoint::class, $serialized);
        $this->assertInstanceOf(\DateTime::class, $serialized->getModified());
        $this->assertIsInt($serialized->getSize());
        $this->assertIsString($serialized->getPath());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\ElementMapper::user
     *
     * @throws \Sabre\Xml\ParseException
     */
    public function test015_user()
    {
        $responseBodyMock = new ResponseBodyMock();

        $body = $responseBodyMock->user();
        $serialized = XmlResponseSerializer::parse($body, 'auto');

        $this->assertInstanceOf(User::class, $serialized);
        $this->assertIsInt($serialized->getUsage());
        $this->assertIsInt($serialized->getMaxDevices());
        $this->assertIsBool($serialized->getEnableFoldershare());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\ResponseNamespace::__call
     */
    public function test017_method_not_exists()
    {
        $mock = \Mockery::mock(ResponseNamespace::class);
        $mock->makePartial();

        try {
            $mock->nonexisting();
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertInstanceOf(JottaException::class, $e);
        }
    }
}
