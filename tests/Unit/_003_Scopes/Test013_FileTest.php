<?php

namespace Vegfund\Jotta\Tests\Unit\_003_Scopes;

use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Vegfund\Jotta\Client\Exceptions\JottaException;
use Vegfund\Jotta\Client\Responses\Namespaces\File;
use Vegfund\Jotta\Jotta;
use Vegfund\Jotta\Tests\Mock\ResponseBodyMock;
use Vegfund\Jotta\Tests\Support\AssertExceptions;
use Vegfund\Jotta\Tests\Support\JottaTestTrait;

/**
 * Class Test013_FileTest.
 */
class Test013_FileTest extends TestCase
{
    use AssertExceptions;
    use JottaTestTrait;

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::get
     *
     * @throws JottaException
     */
    public function test001_get()
    {
        $filename = Str::random(12).'.txt';
        $uuid = Uuid::uuid4()->toString();
        $body = (new ResponseBodyMock())->file([
            'name'       => $filename,
            'uuid'       => $uuid,
            'mountPoint' => Jotta::MOUNT_POINT_ARCHIVE,
        ]);

        $mock = $this->jottaMock($body);
        $result = $mock->file()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->get($filename);
        $this->assertInstanceOf(File::class, $result);
        $this->assertSame($filename, $result->getName());
        $this->assertSame($uuid, $result->getUuid());
    }
}
