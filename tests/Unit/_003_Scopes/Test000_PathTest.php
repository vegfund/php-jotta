<?php

namespace Vegfund\Jotta\Tests\Unit\_003_Scopes;

use Vegfund\Jotta\Client\Exceptions\JottaException;
use Vegfund\Jotta\Client\Responses\Namespaces\Device;
use Vegfund\Jotta\Client\Responses\Namespaces\File;
use Vegfund\Jotta\Client\Responses\Namespaces\Folder;
use Vegfund\Jotta\Client\Responses\Namespaces\MountPoint;
use Vegfund\Jotta\Client\Responses\Namespaces\User;
use Vegfund\Jotta\Client\Scopes\AccountScope;
use Vegfund\Jotta\Client\Scopes\DeviceScope;
use Vegfund\Jotta\Client\Scopes\DirectoryScope;
use Vegfund\Jotta\Client\Scopes\FileScope;
use Vegfund\Jotta\Tests\JottaTestCase;
use Vegfund\Jotta\Tests\Mock\ResponseBodyMock;

class Test000_PathTest extends JottaTestCase
{
    /**
     * @covers \Vegfund\Jotta\JottaClient::detect
     * @covers \Vegfund\Jotta\Client\Scopes\PathScope::detect
     * @throws \Exception
     */
    public function test001_path()
    {
        $responseBodyMock = new ResponseBodyMock();

        $roots = [
            'account' => [
                'body' => $responseBodyMock->user(),
                'scope' => AccountScope::class,
            ],
            'device' => [
                'body' => $responseBodyMock->device(),
                'scope' => DeviceScope::class,
            ],
            'mount_point' => [
                'body' => $responseBodyMock->mountPoint(),
                'scope' => DirectoryScope::class,
            ],
            'folder' => [
                'body' => $responseBodyMock->folder(),
                'scope' => DirectoryScope::class,
            ],
            'file' => [
                'body' => $responseBodyMock->file(),
                'scope' => FileScope::class,
            ],
        ];

        foreach($roots as $root) {
            $serialized = $this->jottaMock($root['body'])->detect('somepath');
            $this->assertInstanceOf($root['scope'], $serialized);
        }

        $this->shouldThrowException(\Exception::class, function () {
            $error = $this->jottaMock($responseBodyMock->error())->detect('somepath');
        });
    }
}