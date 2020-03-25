<?php

namespace Vegfund\Jotta\Tests\Unit\_003_Scopes;

use Vegfund\Jotta\Client\Exceptions\JottaException;
use Vegfund\Jotta\Client\Responses\Namespaces\Device;
use Vegfund\Jotta\Jotta;
use Vegfund\Jotta\Tests\JottaTestCase;

class Test003_DeviceTest extends JottaTestCase
{
    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DeviceScope::all
     */
    public function test000_list_all_devices()
    {
        $devices = Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'))->device()->all();

        $this->assertIsArray($devices);
        $this->assertCount(2, $devices);

        array_map(function ($item) {
            $this->assertInstanceOf(Device::class, $item);
        }, $devices);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DeviceScope::get
     *
     * @throws JottaException
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
     */
    public function test005_get_other_device()
    {
        $this->shouldThrowException(JottaException::class, function () {
            Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'))->device()->get('other');
        });
    }
}
