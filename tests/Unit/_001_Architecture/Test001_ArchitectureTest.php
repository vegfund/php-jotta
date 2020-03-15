<?php

namespace Vegfund\Jotta\Tests\Unit\_001_Architecture;

use Vegfund\Jotta\Client\Scopes\AccountScope;
use Vegfund\Jotta\Client\Scopes\DeviceScope;
use Vegfund\Jotta\Client\Scopes\FileScope;
use Vegfund\Jotta\Client\Scopes\FolderScope;
use Vegfund\Jotta\Client\Scopes\MountPointScope;
use Vegfund\Jotta\Client\Scopes\Scope;
use Vegfund\Jotta\Jotta;
use Vegfund\Jotta\JottaClient;

class Test001_ArchitectureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Vegfund\Jotta\Jotta::client
     * @covers \Vegfund\Jotta\Jotta::getClient
     */
    public function test001_init()
    {
        $client = Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'));
        $this->assertInstanceOf(JottaClient::class, $client);

        $client = new JottaClient(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'));
        $this->assertInstanceOf(JottaClient::class, $client);
    }

    /**
     * @covers \Vegfund\Jotta\JottaClient::getScope
     * @covers \Vegfund\Jotta\JottaClient::account
     * @covers \Vegfund\Jotta\JottaClient::device
     * @covers \Vegfund\Jotta\JottaClient::file
     * @covers \Vegfund\Jotta\JottaClient::folder
     * @covers \Vegfund\Jotta\JottaClient::mountPoint
     */
    public function test003_scopes()
    {
        $scopes = [
            'account'    => AccountScope::class,
            'device'     => DeviceScope::class,
            'file'       => FileScope::class,
            'folder'     => FolderScope::class,
            'mountPoint' => MountPointScope::class,
        ];

        $client = new JottaClient(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'));

        foreach ($scopes as $method => $className) {
            $scope = $client->{$method}();

            $this->assertInstanceOf($className, $scope);
        }
    }

    /**
     * @covers \Vegfund\Jotta\JottaClient::getScope
     */
    public function test003a_scopes_with_options()
    {
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\Scope::normalizePathSegment
     *
     * @throws \ReflectionException
     */
    public function test005_normalize_path()
    {
        $method = new \ReflectionMethod(Scope::class, 'normalizePathSegment');
        $method->setAccessible(true);
        $mock = \Mockery::mock(Scope::class);

        $output = $method->invoke($mock, '/////path-segment//');

        $this->assertSame('path-segment', $output);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\Scope::getPath
     *
     * @throws \ReflectionException
     */
    public function test007_get_path()
    {
        $method = new \ReflectionMethod(Scope::class, 'getPath');
        $method->setAccessible(true);
        $mock = \Mockery::mock(Scope::class);
        $mock->makePartial();
        $mock->setUsername(getenv('JOTTA_USERNAME'));

        $path = $method->invoke($mock, Jotta::API_BASE_URL, Jotta::DEVICE_JOTTA, Jotta::MOUNT_POINT_ARCHIVE, 'somefolder/gone/missing/', ['umode' => 'nomultipart']);
        $this->assertSame('https://jottacloud.com/jfs/'.getenv('JOTTA_USERNAME').'/Jotta/Archive/somefolder/gone/missing?umode=nomultipart', $path);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\Scope::getRelativePath
     */
    public function test009_get_relative_path()
    {
        $method = new \ReflectionMethod(Scope::class, 'getRelativePath');
        $method->setAccessible(true);
        $mock = \Mockery::mock(Scope::class);
        $mock->makePartial();

        $path = getcwd().'/some/relative/path/';
        $relativePath = $method->invoke($mock, $path);
        $this->assertSame('some/relative/path', $relativePath);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\Scope::getRelativePath
     */
    public function test011_get_root_path()
    {
        $method = new \ReflectionMethod(Scope::class, 'getRootPath');
        $method->setAccessible(true);
        $mock = \Mockery::mock(Scope::class);
        $mock->makePartial();

        $path = '/some/relative/path/';
        $rootPath = $method->invoke($mock, $path);
        $this->assertSame('some/relative', $rootPath);
    }

    /**
     * @covers Scope::withoutExceptions
     */
    public function test013_disable_exceptions()
    {
    }

    /**
     * @covers Scope::setAsync
     */
    public function test015_force_async_requests()
    {
    }

    /**
     * @covers Scope::setSync
     */
    public function test017_force_sync_requests()
    {
    }

    public function test019_force_auto_requests()
    {
    }
}
