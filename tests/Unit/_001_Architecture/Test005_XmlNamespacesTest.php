<?php

namespace Vegfund\Jotta\Tests\Unit\_001_Architecture;

use Illuminate\Support\Str;
use Vegfund\Jotta\Client\Exceptions\JottaException;
use Vegfund\Jotta\Client\Responses\Namespaces\Device;
use Vegfund\Jotta\Client\Responses\Namespaces\File;
use Vegfund\Jotta\Client\Responses\Namespaces\Folder;
use Vegfund\Jotta\Client\Responses\Namespaces\Metadata;
use Vegfund\Jotta\Client\Responses\Namespaces\MountPoint;
use Vegfund\Jotta\Client\Responses\Namespaces\Revision;
use Vegfund\Jotta\Client\Responses\Namespaces\User;
use Vegfund\Jotta\Client\Responses\ResponseNamespace;
use Vegfund\Jotta\Client\Responses\XmlResponseSerializer;
use Vegfund\Jotta\Support\JFileInfo;
use Vegfund\Jotta\Tests\JottaTestCase;
use Vegfund\Jotta\Tests\Mock\ResponseBodyMock;

class Test005_XmlNamespacesTest extends JottaTestCase
{
    /**
     * @covers \Vegfund\Jotta\Client\Responses\ElementMapper::file
     * @covers \Vegfund\Jotta\Client\Responses\ElementMapper::currentRevision
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\Revision::xmlDeserialize
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\Revision::__call
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\Revision::__get
     *
     * @throws \Sabre\Xml\ParseException
     */
    public function test001_current_revision()
    {
        $responseBodyMock = new ResponseBodyMock();

        $body = $responseBodyMock->file();
        $serialized = XmlResponseSerializer::parse($body, 'auto');

        $this->assertInstanceOf(File::class, $serialized);
        $this->assertInstanceOf(Revision::class, $serialized->getCurrentRevision());

        $currentRevision = $serialized->getCurrentRevision();

        $this->assertIsInt($currentRevision->getNumber());
        $this->assertIsInt($currentRevision->getSize());
        $this->assertIsString($currentRevision->getState());
        $this->assertIsString($currentRevision->getMd5());
        $this->assertSame(32, strlen($currentRevision->getMd5()));
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\ElementMapper::device
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\Device::xmlDeserialize
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\Device::__call
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\Device::__get
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
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::xmlDeserialize
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::__call
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::__get
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::getMd5
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::getSize
     *
     * @throws \Sabre\Xml\ParseException
     */
    public function test007a_file()
    {
        $responseBodyMock = new ResponseBodyMock();

        $md5 = md5(Str::random(32));
        $size = rand(100, 1000000);

        $body = $responseBodyMock->file([
            'md5'  => $md5,
            'size' => $size,
        ]);
        $serialized = XmlResponseSerializer::parse($body, 'auto');

        $this->assertInstanceOf(File::class, $serialized);
        $this->assertIsString($serialized->getPath());
        $this->assertSame($serialized->getPath(), $serialized->path);
        $this->assertInstanceOf(Revision::class, $serialized->getCurrentRevision());
        $this->assertNotNull($serialized->getAttribute('uuid'));
        $this->assertSame($md5, $serialized->getMd5());
        $this->assertSame($size, $serialized->getSize());
        $this->assertFalse($serialized->isDeleted());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::getAttribute
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::xmlDeserialize
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::__call
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::__get
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::getMd5
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::getSize
     *
     * @throws \Sabre\Xml\ParseException
     */
    public function test007b_file_deleted()
    {
        $responseBodyMock = new ResponseBodyMock();

        $md5 = md5(Str::random(32));
        $size = rand(100, 1000000);

        $body = $responseBodyMock->file([
            'md5'     => $md5,
            'size'    => $size,
            'deleted' => time() - 120,
        ]);
        $serialized = XmlResponseSerializer::parse($body, 'auto');

        $this->assertInstanceOf(File::class, $serialized);
        $this->assertIsString($serialized->getPath());
        $this->assertSame($serialized->getPath(), $serialized->path);
        $this->assertInstanceOf(Revision::class, $serialized->getCurrentRevision());
        $this->assertNotNull($serialized->getAttribute('uuid'));
        $this->assertSame($md5, $serialized->getMd5());
        $this->assertSame($size, $serialized->getSize());
        $this->assertTrue($serialized->isDeleted());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\ElementMapper::folder
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\Folder::xmlDeserialize
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\Folder::__call
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\Folder::__get
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\Folder::getPath
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\Folder::isDeleted
     *
     * @throws \Sabre\Xml\ParseException
     */
    public function test009_folder()
    {
        $responseBodyMock = new ResponseBodyMock();

        $body = $responseBodyMock->folder(['deleted' => time()]);
        $serialized = XmlResponseSerializer::parse($body, 'auto');

        $this->assertInstanceOf(Folder::class, $serialized);
        $this->assertIsArray($serialized->getFolders());
        $this->assertIsString($serialized->getPath());
        $this->assertTrue($serialized->isDeleted());
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
        $body = (new ResponseBodyMock())->mountPoint([
            'folders' => [
                ['name' => 'folder1'],
                ['name' => 'folder2'],
            ],
            'files' => [
                ['name' => 'file1.txt'],
                ['name' => 'file2.txt'],
            ],
        ]);
        $serialized = XmlResponseSerializer::parse($body, 'auto');

        $this->assertInstanceOf(MountPoint::class, $serialized);
        $this->assertInstanceOf(\DateTime::class, $serialized->getModified());
        $this->assertIsInt($serialized->getSize());
        $this->assertIsString($serialized->getPath());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\ElementMapper::user
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\User::__get
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\User::__call
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\User::xmlDeserialize
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

    /**
     * @covers \Vegfund\Jotta\Client\Responses\ResponseNamespace::castPrimitives()
     *
     * @throws \ReflectionException
     */
    public function test019_cast_primitives()
    {
        $method = new \ReflectionMethod(ResponseNamespace::class, 'castPrimitives');
        $method->setAccessible(true);
        $mock = \Mockery::mock(ResponseNamespace::class);
        $mock->makePartial();

        $datetime = new \DateTime();

        $casted = $method->invoke($mock, $datetime->format('Y-m-d-\TH:i:sO'), 'datetime');
        $this->assertInstanceOf(\DateTime::class, $casted);
        $this->assertSame($datetime->getTimestamp(), $casted->getTimestamp());

        $primitives = [
            [
                'value'    => 1,
                'cast'     => 'string',
                'expected' => '1',
            ],
            [
                'value'    => 1,
                'cast'     => 'float',
                'expected' => (float) 1,
            ],
            [
                'value'    => 'true',
                'cast'     => 'bool',
                'expected' => true,
            ],
            [
                'value'    => 1,
                'cast'     => 'bool',
                'expected' => true,
            ],
            [
                'value'    => 'false',
                'cast'     => 'bool',
                'expected' => false,
            ],
            [
                'value'    => 0,
                'cast'     => 'bool',
                'expected' => false,
            ],
        ];

        foreach ($primitives as $primitive) {
            $casted = $method->invoke($mock, $primitive['value'], $primitive['cast']);
            $this->assertSame($primitive['expected'], $casted);
        }
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isDeleted
     *
     * @throws JottaException
     */
    public function test021_file_is_deleted()
    {
        $responseBodyMock = new ResponseBodyMock();

        // NOT DELETED

        $body = $responseBodyMock->file(['name' => Str::random(12).'.txt']);
        $mock = $this->jottaMock($body);
        $serialized = $mock->file()->get('somepath');

        $this->assertInstanceOf(File::class, $serialized);
        $this->assertFalse($serialized->isDeleted());

        // DELETED

        $body = $responseBodyMock->file(['name' => Str::random(12).'.txt', 'deleted' => time()]);
        $mock = $this->jottaMock($body);
        $serialized = $mock->file()->get('somepath');

        $this->assertInstanceOf(File::class, $serialized);
        $this->assertTrue($serialized->isDeleted());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isCorrupt
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isCompleted
     *
     * @throws JottaException
     */
    public function test023_file_is_corrupt()
    {
        $responseBodyMock = new ResponseBodyMock();

        // NOT CORRUPT

        $body = $responseBodyMock->file(['name' => Str::random(12).'.txt', 'state' => File::STATE_COMPLETED]);
        $mock = $this->jottaMock($body);
        $serialized = $mock->file()->get('somepath');

        $this->assertInstanceOf(File::class, $serialized);
        $this->assertFalse($serialized->isCorrupt());
        $this->assertTrue($serialized->isCompleted());

        // CORRUPT

        $body = $responseBodyMock->file(['name' => Str::random(12).'.txt', 'state' => File::STATE_CORRUPT]);
        $mock = $this->jottaMock($body);
        $serialized = $mock->file()->get('somepath');

        $this->assertInstanceOf(File::class, $serialized);
        $this->assertTrue($serialized->isCorrupt());
        $this->assertFalse($serialized->isCompleted());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isIncomplete
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isCompleted
     *
     * @throws JottaException
     */
    public function test024_file_is_incomplete()
    {
        $responseBodyMock = new ResponseBodyMock();

        // NOT CORRUPT

        $body = $responseBodyMock->file(['name' => Str::random(12).'.txt', 'state' => File::STATE_COMPLETED]);
        $mock = $this->jottaMock($body);
        $serialized = $mock->file()->get('somepath');

        $this->assertInstanceOf(File::class, $serialized);
        $this->assertFalse($serialized->isIncomplete());
        $this->assertTrue($serialized->isCompleted());

        // CORRUPT

        $body = $responseBodyMock->file(['name' => Str::random(12).'.txt', 'state' => File::STATE_INCOMPLETE]);
        $mock = $this->jottaMock($body);
        $serialized = $mock->file()->get('somepath');

        $this->assertInstanceOf(File::class, $serialized);
        $this->assertTrue($serialized->isIncomplete());
        $this->assertFalse($serialized->isCompleted());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isValid
     *
     * @throws JottaException
     */
    public function test025_file_is_valid()
    {
        $responseBodyMock = new ResponseBodyMock();

        // VALID

        $body = $responseBodyMock->file(['name' => Str::random(12).'.txt', 'state' => File::STATE_COMPLETED]);
        $mock = $this->jottaMock($body);
        $serialized = $mock->file()->get('somepath');

        $this->assertInstanceOf(File::class, $serialized);
        $this->assertTrue($serialized->isValid());

        // COMPLETED BUT DELETED

        $body = $responseBodyMock->file(['name' => Str::random(12).'.txt', 'deleted' => time(), 'state' => File::STATE_COMPLETED]);
        $mock = $this->jottaMock($body);
        $serialized = $mock->file()->get('somepath');

        $this->assertInstanceOf(File::class, $serialized);
        $this->assertFalse($serialized->isValid());

        // CORRUPT

        $body = $responseBodyMock->file(['name' => Str::random(12).'.txt', 'state' => File::STATE_CORRUPT]);
        $mock = $this->jottaMock($body);
        $serialized = $mock->file()->get('somepath');

        $this->assertInstanceOf(File::class, $serialized);
        $this->assertFalse($serialized->isValid());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isNewerThan
     *
     * @throws JottaException
     */
    public function test027_file_is_newer_than()
    {
        $responseBodyMock = new ResponseBodyMock();

        // NEWER THAN LOCAL

        $body = $responseBodyMock->file(['name' => Str::random(12).'.txt', 'modified' => time()]);
        $mock = $this->jottaMock($body);
        $serialized = $mock->file()->get('somepath');

        $fileMock = \Mockery::mock(JFileInfo::class);
        $fileMock->makePartial();
        $fileMock->shouldAllowMockingProtectedMethods();
        $fileMock->shouldReceive('getMTime')->andReturn(time() - 1000);

        $this->assertInstanceOf(File::class, $serialized);
        $this->assertTrue($serialized->isNewerThan($fileMock));

        // SAME AS LOCAL

        $timestamp = time() - 60;

        $body = $responseBodyMock->file(['name' => Str::random(12).'.txt', 'modified' => $timestamp]);
        $mock = $this->jottaMock($body);
        $serialized = $mock->file()->get('somepath');

        $fileMock = \Mockery::mock(JFileInfo::class);
        $fileMock->makePartial();
        $fileMock->shouldAllowMockingProtectedMethods();
        $fileMock->shouldReceive('getMTime')->andReturn($timestamp);

        $this->assertInstanceOf(File::class, $serialized);
        $this->assertFalse($serialized->isNewerThan($fileMock));

        // OLDER AS LOCAL

        $timestamp = time() - 60;

        $body = $responseBodyMock->file(['name' => Str::random(12).'.txt', 'modified' => $timestamp - 10000]);
        $mock = $this->jottaMock($body);
        $serialized = $mock->file()->get('somepath');

        $fileMock = \Mockery::mock(JFileInfo::class);
        $fileMock->makePartial();
        $fileMock->shouldAllowMockingProtectedMethods();
        $fileMock->shouldReceive('getMTime')->andReturn($timestamp);

        $this->assertInstanceOf(File::class, $serialized);
        $this->assertFalse($serialized->isNewerThan($fileMock));
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isDifferentThan
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isSameAs
     *
     * @throws JottaException
     */
    public function test029_file_is_different()
    {
        $responseBodyMock = new ResponseBodyMock();

        // SAME SIZE AND MD5

        $size = rand(100, 100000);
        $md5 = md5(Str::random(10000));

        $body = $responseBodyMock->file(['name' => Str::random(12).'.txt', 'size' => $size, 'md5' => $md5]);
        $mock = $this->jottaMock($body);
        $serialized = $mock->file()->get('somepath');

        $fileMock = \Mockery::mock(JFileInfo::class);
        $fileMock->makePartial();
        $fileMock->shouldAllowMockingProtectedMethods();
        $fileMock->shouldReceive('getSize')->andReturn($size);
        $fileMock->shouldReceive('getMd5')->andReturn($md5);

        $this->assertInstanceOf(File::class, $serialized);
        $this->assertFalse($serialized->isDifferentThan($fileMock));
        $this->assertTrue($serialized->isSameAs($fileMock));

        // SAME SIZE, DIFFERENT MD5

        $size = rand(100, 100000);
        $md5 = md5(Str::random(10000));

        $body = $responseBodyMock->file(['name' => Str::random(12).'.txt', 'size' => $size, 'md5' => $md5]);
        $mock = $this->jottaMock($body);
        $serialized = $mock->file()->get('somepath');

        $fileMock = \Mockery::mock(JFileInfo::class);
        $fileMock->makePartial();
        $fileMock->shouldAllowMockingProtectedMethods();
        $fileMock->shouldReceive('getSize')->andReturn($size);
        $fileMock->shouldReceive('getMd5')->andReturn(md5($md5));

        $this->assertInstanceOf(File::class, $serialized);
        $this->assertTrue($serialized->isDifferentThan($fileMock));
        $this->assertFalse($serialized->isSameAs($fileMock));

        // SAME MD5, DIFFERENT SIZE

        $size = rand(100, 100000);
        $md5 = md5(Str::random(10000));

        $body = $responseBodyMock->file(['name' => Str::random(12).'.txt', 'size' => $size, 'md5' => $md5]);
        $mock = $this->jottaMock($body);
        $serialized = $mock->file()->get('somepath');

        $fileMock = \Mockery::mock(JFileInfo::class);
        $fileMock->makePartial();
        $fileMock->shouldAllowMockingProtectedMethods();
        $fileMock->shouldReceive('getSize')->andReturn($size * rand(2, 10));
        $fileMock->shouldReceive('getMd5')->andReturn($md5);

        $this->assertInstanceOf(File::class, $serialized);
        $this->assertTrue($serialized->isDifferentThan($fileMock));

        // BOTH DIFFERENT

        $size = rand(100, 100000);
        $md5 = md5(Str::random(10000));

        $body = $responseBodyMock->file(['name' => Str::random(12).'.txt', 'size' => $size, 'md5' => $md5]);
        $mock = $this->jottaMock($body);
        $serialized = $mock->file()->get('somepath');

        $fileMock = \Mockery::mock(JFileInfo::class);
        $fileMock->makePartial();
        $fileMock->shouldAllowMockingProtectedMethods();
        $fileMock->shouldReceive('getSize')->andReturn($size * rand(2, 10));
        $fileMock->shouldReceive('getMd5')->andReturn(md5($md5));

        $this->assertInstanceOf(File::class, $serialized);
        $this->assertTrue($serialized->isDifferentThan($fileMock));
        $this->assertFalse($serialized->isSameAs($fileMock));
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\MountPoint::getUser
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\MountPoint::getUsername
     *
     * @throws JottaException
     */
    public function test031_mount_point_get_username()
    {
        $responseBodyMock = new ResponseBodyMock();
        $body = $responseBodyMock->mountPoint();
        $mock = $this->jottaMock($body);
        $serialized = $mock->mountPoint()->get('somepath');

        $this->assertSame($serialized->getUser(), $serialized->getUsername());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\MountPoint::xmlDeserialize
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::getWithContents
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\MountPoint::getUser
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\MountPoint::getUsername
     *
     * @throws JottaException
     */
    public function test033_mount_point_get_folders()
    {
        $responseBodyMock = new ResponseBodyMock();

        // NO FOLDERS, NO FILES

        $body = $responseBodyMock->mountPoint(['files' => [], 'folders' => []]);
        $mock = $this->jottaMock($body);
        $serialized = $mock->mountPoint()->get('somepath');

        $this->assertSame([], $serialized->getFolders());
        $this->assertSame([], $serialized->getFiles());

        // TWO FOLDERS, NO FILES

        $body = $responseBodyMock->mountPoint(['files' => [], 'folders' => [['name' => '1'], ['name' => '2']]]);
        $mock = $this->jottaMock($body);
        $serialized = $mock->mountPoint()->getWithContents('somepath');

        $this->assertSame([], $serialized->getFiles());
        $this->assertIsArray($serialized->getFolders());
        $this->assertCount(2, $serialized->getFolders());

        // TWO FOLDERS, TWO FILES

        $body = $responseBodyMock->mountPoint(['files' => [['name' => '1.txt'], ['name' => '2.txt']], 'folders' => [['name' => '1'], ['name' => '2']]]);
        $mock = $this->jottaMock($body);
        $serialized = $mock->mountPoint()->getWithContents('somepath');

        $this->assertIsArray($serialized->getFolders());
        $this->assertCount(2, $serialized->getFolders());
        $this->assertIsArray($serialized->getFiles());
        $this->assertCount(2, $serialized->getFiles());
    }
}
