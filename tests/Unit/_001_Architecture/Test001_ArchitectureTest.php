<?php

namespace Vegfund\Jotta\Tests\Unit\_001_Architecture;

use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Mockery;
use Vegfund\Jotta\Client\Contracts\ScopeContract;
use Vegfund\Jotta\Client\Exceptions\JottaException;
use Vegfund\Jotta\Client\Responses\Namespaces\Attributes;
use Vegfund\Jotta\Client\Responses\ResponseNamespace;
use Vegfund\Jotta\Client\Scopes\AccountScope;
use Vegfund\Jotta\Client\Scopes\DeviceScope;
use Vegfund\Jotta\Client\Scopes\DirectoryScope;
use Vegfund\Jotta\Client\Scopes\FileScope;
use Vegfund\Jotta\Client\Scopes\Scope;
use Vegfund\Jotta\Jotta;
use Vegfund\Jotta\JottaClient;
use Vegfund\Jotta\Support\JFileInfo;
use Vegfund\Jotta\Tests\JottaTestCase;

class Test001_ArchitectureTest extends JottaTestCase
{
    /**
     * @covers \Vegfund\Jotta\JottaClient::__construct
     * @covers \Vegfund\Jotta\Jotta::client
     * @covers \Vegfund\Jotta\Jotta::getClient
     * @covers \Vegfund\Jotta\Jotta::__construct
     * @covers \Vegfund\Jotta\JottaClient::getUsername
     * @covers \Vegfund\Jotta\JottaClient::getClient
     */
    public function test001_init()
    {
        $client = Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'));
        $this->assertInstanceOf(JottaClient::class, $client);

        $client = new JottaClient(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'));
        $this->assertInstanceOf(JottaClient::class, $client);

        $this->assertSame(getenv('JOTTA_USERNAME'), $client->getUsername());

        $client = new Jotta(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'));
        $this->assertInstanceOf(JottaClient::class, $client->getClient());
    }

    /**
     * @covers \Vegfund\Jotta\JottaClient::getClient
     */
    public function test001a_get_client()
    {
        $client = Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'));
        $this->assertInstanceOf(Client::class, $client->getClient());

        $mock = Mockery::mock(Scope::class);
        $mock->makePartial();
        $mock->shouldAllowMockingProtectedMethods();

        $property = new \ReflectionProperty(Scope::class, 'jottaClient');
        $property->setAccessible(true);
        $property->setValue($mock, $client);
        $method = new \ReflectionMethod(Scope::class, 'getClient');
        $method->setAccessible(true);

        $this->assertInstanceOf(get_class($client), $method->invoke($mock));
    }

    /**
     * @covers \Vegfund\Jotta\JottaClient::getScope
     * @covers \Vegfund\Jotta\JottaClient::account
     * @covers \Vegfund\Jotta\JottaClient::device
     * @covers \Vegfund\Jotta\JottaClient::file
     * @covers \Vegfund\Jotta\JottaClient::directory
     */
    public function test003_scopes()
    {
        $scopes = [
            'account'    => AccountScope::class,
            'device'     => DeviceScope::class,
            'file'       => FileScope::class,
            'directory'  => DirectoryScope::class,
        ];

        $client = new JottaClient(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'));

        foreach ($scopes as $method => $className) {
            $scope = $client->{$method}();

            $this->assertInstanceOf($className, $scope);
            $this->assertInstanceOf(Scope::class, $scope);
            $this->assertInstanceOf(ScopeContract::class, $scope);
        }
    }

    /**
     * @covers \Vegfund\Jotta\JottaClient::folder
     * @covers \Vegfund\Jotta\JottaClient::mountPoint
     *
     * @throws JottaException
     */
    public function test003a_mount_point_folder_scopes()
    {
        $client = new JottaClient(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'));

        $folder = $client->folder();

        $this->assertInstanceOf(DirectoryScope::class, $folder);
        $this->assertSame(DirectoryScope::MODE_FOLDER, $folder->getMode());

        $folder = $client->mountPoint();

        $this->assertInstanceOf(DirectoryScope::class, $folder);
        $this->assertSame(DirectoryScope::MODE_MOUNT_POINT, $folder->getMode());
    }

    /**
     * @covers \Vegfund\Jotta\Jotta::account
     * @covers \Vegfund\Jotta\Jotta::device
     * @covers \Vegfund\Jotta\Jotta::file
     * @covers \Vegfund\Jotta\Jotta::directory
     * @covers \Vegfund\Jotta\Jotta::folder
     * @covers \Vegfund\Jotta\Jotta::mountPoint
     */
    public function test003a_scopes_static()
    {
        $scopes = [
            'account'    => AccountScope::class,
            'device'     => DeviceScope::class,
            'file'       => FileScope::class,
            'directory'  => DirectoryScope::class,
            'folder'     => DirectoryScope::class,
            'mountPoint' => DirectoryScope::class,
        ];

        foreach ($scopes as $method => $className) {
            $scope = Jotta::{$method}(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'));

            $this->assertInstanceOf($className, $scope);
            $this->assertInstanceOf(Scope::class, $scope);
            $this->assertInstanceOf(ScopeContract::class, $scope);

            if ($method === 'folder') {
                $this->assertSame(DirectoryScope::MODE_FOLDER, $scope->getMode());
            }
            if ($method === 'mountPoint') {
                $this->assertSame(DirectoryScope::MODE_MOUNT_POINT, $scope->getMode());
            }
        }
    }

    /**
     * @covers \Vegfund\Jotta\JottaClient::getScope
     */
    public function test003a_scope_does_not_exist()
    {
        $scopeName = str_replace('il', 'somethingelse', FileScope::class);

        try {
            Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'))->getScope($scopeName);
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertInstanceOf(JottaException::class, $e);
        }
    }

    /**
     * @covers \Vegfund\Jotta\JottaClient::getScope
     */
    public function test003c_scopes_with_options()
    {
        $options = [
            'device'      => Str::random(32),
            'mount_point' => Str::random(32),
            'base_path'   => Str::random(32),
        ];

        $scopes = [
            'account'    => AccountScope::class,
            'device'     => DeviceScope::class,
            'file'       => FileScope::class,
            'folder'     => DirectoryScope::class,
            'mountPoint' => DirectoryScope::class,
        ];

        foreach ($scopes as $method => $className) {
            $scope = Jotta::{$method}(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'), $options);

            $this->assertInstanceOf($className, $scope);
            $this->assertInstanceOf(Scope::class, $scope);
            $this->assertInstanceOf(ScopeContract::class, $scope);

            $this->assertSame(Jotta::DEVICE_JOTTA, $scope->getDevice());
            $this->assertSame($options['mount_point'], $scope->getMountPoint());
            $this->assertSame($options['base_path'], $scope->getBasePath());
        }
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\Scope::setApiUrl
     *
     * @throws \ReflectionException
     */
    public function test003d_scope_configs()
    {
        $mock = \Mockery::mock(Scope::class);
        $mock->makePartial();

        $mock->setApiUrl(Jotta::API_UPLOAD_URL);

        $reflection = new \ReflectionClass($mock);
        $property = $reflection->getProperty('apiUrl');
        $property->setAccessible(true);

        $this->assertSame(Jotta::API_UPLOAD_URL, $property->getValue($mock));
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

        $path = $method->invoke($mock, null, null, null, null);
        $this->assertSame(getenv('JOTTA_USERNAME'), $path);

        $path = $method->invoke($mock, '', '', '', '');
        $this->assertSame(getenv('JOTTA_USERNAME'), $path);
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
     * @covers \Vegfund\Jotta\Client\Scopes\Scope::getRootPath
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
     * @covers \Vegfund\Jotta\Client\Scopes\Scope::withoutExceptions
     * @covers \Vegfund\Jotta\Client\Scopes\Scope::withExceptions
     * @covers \Vegfund\Jotta\Client\Scopes\Scope::serialize
     */
    public function test013_disable_exceptions()
    {
        $mock = \Mockery::mock(Scope::class);
        $mock->makePartial();

        $this->shouldNotThrowException(function () use ($mock) {
            $mock->withoutExceptions()->serialize('not a XML body');
        });

        $this->shouldThrowException(\Exception::class, function () use ($mock) {
            $mock->withExceptions()->serialize('not a XML body');
        });
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\Scope::setAutoRequest
     * @covers \Vegfund\Jotta\Client\Scopes\Scope::setSyncRequest
     * @covers \Vegfund\Jotta\Client\Scopes\Scope::setAsyncRequest
     */
    public function test015_set_request_type()
    {
        foreach (['async', 'sync', 'auto'] as $requestType) {
            $mock = \Mockery::mock(Scope::class);
            $mock->makePartial();

            $reflection = new \ReflectionClass($mock);
            $property = $reflection->getProperty('requestType');
            $property->setAccessible(true);

            $funcName = 'set'.ucfirst($requestType).'Request';
            $asyncScope = $mock->{$funcName}();

            $this->assertSame($requestType, $property->getValue($asyncScope));
        }
    }

    /**
     * @covers \Vegfund\Jotta\JottaClient::getHeaders
     *
     * @throws \ReflectionException
     */
    public function test021_merge_headers()
    {
        $method = new \ReflectionMethod(JottaClient::class, 'getHeaders');
        $method->setAccessible(true);
        $mock = \Mockery::mock(JottaClient::class);
        $mock->makePartial();

        $newHeaders = [
            'header1' => 'value1',
            'header2' => 'value2',
        ];
        $mergedHeaders = $method->invoke($mock, $newHeaders);
        foreach ($newHeaders as $header => $value) {
            $this->assertSame($value, $mergedHeaders[$header]);
        }
    }

    /**
     * @covers \Vegfund\Jotta\Support\JFileInfo::getMd5
     * @covers \Vegfund\Jotta\Support\JFileInfo::getContents
     */
    public function test023_jfile_info()
    {
        $file = new JFileInfo(__FILE__);
        $this->assertInstanceOf(\SplFileInfo::class, $file);
        $this->assertSame(md5(file_get_contents(__FILE__)), $file->getMd5());

        $contents = file_get_contents(__FILE__);
        $this->assertSame($contents, $file->getContents());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\Scope::setUsername
     * @covers \Vegfund\Jotta\Client\Scopes\Scope::getUsername
     * @covers \Vegfund\Jotta\Client\Scopes\Scope::setDevice
     * @covers \Vegfund\Jotta\Client\Scopes\Scope::getDevice
     * @covers \Vegfund\Jotta\Client\Scopes\Scope::setMountPoint
     * @covers \Vegfund\Jotta\Client\Scopes\Scope::getMountPoint
     * @covers \Vegfund\Jotta\Client\Scopes\Scope::setBasePath
     * @covers \Vegfund\Jotta\Client\Scopes\Scope::getBasePath
     */
    public function test025_scope_settings()
    {
        $mock = \Mockery::mock(Scope::class);
        $mock->makePartial();

        $username = Str::random(32);
        $mock->setUsername($username);
        $this->assertSame($username, $mock->getUsername());

        $device = Str::random(32);
        $mock->setDevice($device);
        $this->assertSame(Jotta::DEVICE_JOTTA, $mock->getDevice());

        $mountPoint = Str::random(32);
        $mock->setMountPoint($mountPoint);
        $this->assertSame($mountPoint, $mock->getMountPoint());

        $basePath = Str::random(32);
        $mock->setBasePath($basePath);
        $this->assertSame($basePath, $mock->getBasePath());
    }

    /**
     * @covers \Vegfund\Jotta\Support\JFileInfo::__construct
     * @covers \Vegfund\Jotta\Support\JFileInfo::make
     */
    public function test027_cast_file_to_jfile()
    {
        $thisFile = __FILE__;
        $this->assertInstanceOf(JFileInfo::class, JFileInfo::make($thisFile));

        $thisFile = new \SplFileInfo($thisFile);
        $this->assertInstanceOf(JFileInfo::class, JFileInfo::make($thisFile));

        $thisFile = new JFileInfo($thisFile);
        $this->assertInstanceOf(JFileInfo::class, JFileInfo::make($thisFile));
    }

    /**
     * @covers ResponseNamespace::__get
     *
     * @throws \ReflectionException
     */
    public function test029_scope_getter()
    {
        $mock = \Mockery::mock(ResponseNamespace::class);
        $mock->makePartial();
        $mock->shouldAllowMockingProtectedMethods();

        $mock->testAttribute1 = 'abc';
        $this->assertSame('abc', $mock->testAttribute1);

        $attributes = new Attributes(['testAttribute2' => 'def']);
        $method = new \ReflectionMethod(ResponseNamespace::class, 'setAttributes');
        $method->setAccessible(true);

        $method->invoke($mock, $attributes);

        $this->assertSame('def', $mock->testAttribute2);

        $this->shouldThrowException(JottaException::class, function () use ($mock) {
            $nonExistant = $mock->nonExistant;
        });
    }
}
