<?php

namespace Vegfund\Jotta\Tests\Unit\_003_Scopes;

use Vegfund\Jotta\Client\Exceptions\CliDevicesNotSupportedException;
use Vegfund\Jotta\Client\Responses\Namespaces\Device;
use Vegfund\Jotta\Client\Scopes\AccountScope;
use Vegfund\Jotta\Client\Scopes\DeviceScope;
use Vegfund\Jotta\Client\Scopes\FileScope;
use Vegfund\Jotta\Client\Scopes\FolderScope;
use Vegfund\Jotta\Client\Scopes\MountPointScope;
use Vegfund\Jotta\Client\Scopes\Scope;
use Vegfund\Jotta\Jotta;
use Vegfund\Jotta\JottaClient;

class Test003_DeviceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DeviceScope::all
     * @throws \Sabre\Xml\ParseException
     */
    public function test000_list_all_devices()
    {
        $devices = Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'))->device()->all();

        $this->assertIsArray($devices);
        $this->assertCount(2, $devices);

        $names = array_map(function (Device $device) {
            return $device->getName();
        }, $devices);

        $deviceJotta = Jotta::DEVICE_JOTTA;

        $this->assertTrue(isset($deviceJotta, $names));
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DeviceScope::get
     * @throws \Sabre\Xml\ParseException
     */
    public function test003_get_device_jotta()
    {
        $device = Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'))->device()->get(Jotta::DEVICE_JOTTA);

        $this->assertInstanceOf(Device::class, $device);

        $this->assertSame(Jotta::DEVICE_JOTTA, $device->getName());
        $this->assertSame(Jotta::DEVICE_JOTTA, $device->getDisplayName());
        $this->assertSame('JOTTA', $device->getType());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DeviceScope::get
     * @throws \Sabre\Xml\ParseException
     */
    public function test005_get_other_device()
    {
        try {
            $device = Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'))->device()->get('other');
        } catch (\Exception $e) {
            $this->assertInstanceOf(CliDevicesNotSupportedException::class, $e);
        }
    }
}