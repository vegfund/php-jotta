<?php

namespace Vegfund\Jotta\Tests\Unit\_001_Architecture;

use Vegfund\Jotta\Client\Responses\Namespaces\CurrentRevision;
use Vegfund\Jotta\Client\Responses\Namespaces\Device;
use Vegfund\Jotta\Client\Responses\Namespaces\File;
use Vegfund\Jotta\Client\Responses\Namespaces\Folder;
use Vegfund\Jotta\Client\Responses\Namespaces\MountPoint;
use Vegfund\Jotta\Client\Responses\Namespaces\User;
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

    public function test007_file()
    {
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

    public function test011_metadata()
    {
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
}
