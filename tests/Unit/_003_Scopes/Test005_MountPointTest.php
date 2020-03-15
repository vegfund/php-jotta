<?php

namespace Vegfund\Jotta\Tests\Unit\_003_Scopes;

use Vegfund\Jotta\Client\Responses\Namespaces\MountPoint;
use Vegfund\Jotta\Client\Scopes\MountPointScope;
use Vegfund\Jotta\Jotta;

class Test005_MountPointTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Vegfund\Jotta\Client\Scopes\MountPointScope::all
     *
     * @throws \Sabre\Xml\ParseException
     */
    public function test000_list_all_mount_points()
    {
        $mountPoints = Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'))->mountPoint()->all();

        $this->assertIsArray($mountPoints);
        $this->assertCount(3, $mountPoints);

        $names = array_map(function (MountPoint $mountPoint) {
            return $mountPoint->getName();
        }, $mountPoints);

        $builtInMountPoints = [
            Jotta::MOUNT_POINT_SYNC,
            Jotta::MOUNT_POINT_SHARED,
            Jotta::MOUNT_POINT_ARCHIVE,
        ];

        foreach ($builtInMountPoints as $builtInMountPoint) {
            $this->assertTrue(isset($builtInMountPoint, $names));
        }
    }

    /**
     * @covers MountPointScope::create()
     * @covers MountPointScope::delete()
     *
     * @throws \Sabre\Xml\ParseException
     */
    public function test003_create_and_delete_vegfund_mount_point()
    {
        $mountPoint = Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'))->mountPoint()->create('vegfund');

        $this->assertInstanceOf(MountPoint::class, $mountPoint);

        $this->assertSame('vegfund', $mountPoint->getName());

        $mountPoints = Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'))->mountPoint()->all();
        $this->assertCount(4, $mountPoints);

        $mountPoint = Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'))->mountPoint()->setMountPoint('vegfund')->delete();

        $mountPoints = Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'))->mountPoint()->all();
        $this->assertCount(3, $mountPoints);
    }

    /**
     * @covers MountPointScope::delete()
     */
    public function test005_delete_built_in_mount_point()
    {
        $builtInMountPoints = [
            Jotta::MOUNT_POINT_SYNC,
            Jotta::MOUNT_POINT_SHARED,
            Jotta::MOUNT_POINT_ARCHIVE,
        ];

        foreach ($builtInMountPoints as $builtInMountPoint) {
            try {
                Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'))->mountPoint()->setMountPoint($builtInMountPoint)->delete();
                $this->assertTrue(false);
            } catch (\Exception $e) {
                $this->assertInstanceOf(\Exception::class, $e);
            }
        }
    }

    /**
     * @covers MountPointScope::get()
     */
    public function test007_get_mount_point_data_should_return()
    {
        $archive = Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'))->mountPoint()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->get();

        $this->assertInstanceOf(MountPoint::class, $archive);

        $this->assertSame(Jotta::MOUNT_POINT_ARCHIVE, $archive->getName());
        $this->assertInstanceOf(\DateTime::class, $archive->getModified());
    }

    /**
     * @covers MountPointScope::get()
     */
    public function test009_get_mount_point_data_should_throw_exception()
    {
        try {
            Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'))->mountPoint()->setMountPoint('thisMountPointDoesNotExist')->get();
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    /**
     * @covers MountPointScope::get()
     */
    public function test011_get_mount_point_data_should_return_null()
    {
        $this->assertNull(Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'))->mountPoint()->withoutExceptions()->setMountPoint('thisMountPointDoesNotExist')->get());
    }
}
