<?php

namespace Vegfund\Jotta\Tests\Unit\_003_Scopes;

use Vegfund\Jotta\Client\Exceptions\JottaException;
use Vegfund\Jotta\Client\Responses\Namespaces\MountPoint;
use Vegfund\Jotta\Client\Scopes\DirectoryScope;
use Vegfund\Jotta\Jotta;
use Vegfund\Jotta\Tests\Support\AssertExceptions;

/**
 * Class Test011_DirectoryTest.
 */
class Test011_DirectoryTest extends \PHPUnit\Framework\TestCase
{
    use AssertExceptions;

    /**
     * @covers \Vegfund\Jotta\Jotta::directory
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::getMode
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::setMode
     *
     * @throws \Vegfund\Jotta\Client\Exceptions\JottaException
     */
    public function test001_modes_simple()
    {
        $client = Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'));
        $scope = $client->directory();
        $this->assertInstanceOf(DirectoryScope::class, $scope);
        $this->assertNull($scope->getMode());
        $this->assertSame(DirectoryScope::MODE_FOLDER, $scope->setMode(DirectoryScope::MODE_FOLDER)->getMode());
        $this->assertSame(DirectoryScope::MODE_MOUNT_POINT, $scope->setMode(DirectoryScope::MODE_MOUNT_POINT)->getMode());

        $mountPoint = $client->mountPoint();
        $this->assertSame(DirectoryScope::MODE_MOUNT_POINT, $mountPoint->getMode());
        $folder = $client->folder();
        $this->assertSame(DirectoryScope::MODE_FOLDER, $folder->getMode());
    }

    /**
     * @covers \Vegfund\Jotta\Jotta::client
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::all
     *
     * @throws JottaException
     */
    public function test003_list_mount_points()
    {
        $client = Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'));

        $this->shouldThrowException(JottaException::class, function () use ($client) {
            $client->folder()->all();
        });

        $this->shouldThrowException(JottaException::class, function () use ($client) {
            $client->mountPoint()->setMode(DirectoryScope::MODE_FOLDER)->all();
        });

        $this->shouldNotThrowException(function () use ($client) {
            $client->mountPoint()->all();
            $client->directory()->setMode(DirectoryScope::MODE_MOUNT_POINT)->all();
            $client->folder()->setMode(DirectoryScope::MODE_MOUNT_POINT)->all();
        });

        $all = $client->mountPoint()->all();

        $this->assertIsArray($all);

        array_map(function ($item) {
            $this->assertInstanceOf(MountPoint::class, $item);
        }, $all);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::delete
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::deleteMountPoint
     */
    public function test005_delete_built_in_mount_point()
    {
        $client = Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'));

        $this->shouldThrowException(JottaException::class, function () use ($client) {
            $client->mountPoint()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->delete();
        });
        $this->shouldThrowException(JottaException::class, function () use ($client) {
            $client->mountPoint()->setMountPoint(Jotta::MOUNT_POINT_SHARED)->delete();
        });
        $this->shouldThrowException(JottaException::class, function () use ($client) {
            $client->mountPoint()->setMountPoint(Jotta::MOUNT_POINT_SYNC)->delete();
        });
    }
}
