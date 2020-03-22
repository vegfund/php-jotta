<?php

namespace Vegfund\Jotta\Tests\Unit\_003_Scopes;

use Exception;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Vegfund\Jotta\Client\Exceptions\JottaException;
use Vegfund\Jotta\Client\Responses\Namespaces\Device;
use Vegfund\Jotta\Client\Responses\Namespaces\Folder;
use Vegfund\Jotta\Client\Responses\Namespaces\Metadata;
use Vegfund\Jotta\Client\Responses\Namespaces\MountPoint;
use Vegfund\Jotta\Client\Scopes\DirectoryScope;
use Vegfund\Jotta\Jotta;
use Vegfund\Jotta\JottaClient;
use Vegfund\Jotta\Tests\Mock\JottaApiV1Mock;
use Vegfund\Jotta\Tests\Mock\ResponseBodyMock;
use Vegfund\Jotta\Tests\Support\AssertExceptions;
use Vegfund\Jotta\Tests\Support\JottaTestTrait;

/**
 * Class Test011_DirectoryTest.
 */
class Test011_DirectoryTest extends TestCase
{
    use AssertExceptions, JottaTestTrait;

    /**
     * @covers \Vegfund\Jotta\Jotta::directory
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::getMode
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::setMode
     *
     * @throws JottaException
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

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::uuid
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::getUuid
     *
     * @throws JottaException
     */
    public function test007_uuid()
    {
        $directory = Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'))->directory();
        $this->shouldThrowException(Exception::class, function () use ($directory) {
            $directory->uuid('not-an-uuid');
        });
        $this->shouldNotThrowException(function () use ($directory) {
            $uuid = Uuid::uuid4()->toString();
            $directory->uuid($uuid);
            $this->assertSame($uuid, $directory->getUuid());
        });
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::regex
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::getRegex
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::deleted
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::withDeleted
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::corrupt
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::withCorrupt
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::completed
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::withCompleted
     *
     * @throws JottaException
     */
    public function test009_other_configs()
    {
        $directory = Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'))->directory();

        $this->assertNull($directory->getUuid());
        $this->assertNull($directory->getRegex());

        $this->assertFalse($directory->withDeleted());
        $this->assertFalse($directory->withCorrupt());
        $this->assertTrue($directory->withCompleted());
        $this->assertTrue($directory->deleted(true)->withDeleted());
        $this->assertTrue($directory->corrupt(true)->withCorrupt());
        $this->assertFalse($directory->completed(false)->withCompleted());

        $regex = Str::random(32);
        $this->assertSame($regex, $directory->regex($regex)->getRegex());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::get
     * @covers \Vegfund\Jotta\Client\Responses\ResponseNamespace::except
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\Device::xmlDeserialize
     *
     * @throws JottaException
     * @throws Exception
     */
    public function test011_get()
    {
        $body = (new ResponseBodyMock())->mountPoint([
            'name'    => Jotta::MOUNT_POINT_SHARED,
            'folders' => [
                [
                    'name'    => 'somefolder',
                    'deleted' => time(),
                ],
            ],
            'files' => [
                [
                    'name' => 'one.txt',
                ],
            ],
        ]);

        $mock = new JottaApiV1Mock($body);
        $jotta = new JottaClient('a', 'b', $mock->getMock());
        $result = $jotta->mountPoint()->setMountPoint(Jotta::MOUNT_POINT_SHARED)->get();
        $this->assertInstanceOf(MountPoint::class, $result);
        $this->assertSame(getenv('JOTTA_USERNAME'), $result->getUser());
        $this->assertSame($result->getUser(), $result->getUsername());

        $this->assertInstanceOf(Metadata::class, $result->getMetadata());

        $this->assertFalse(isset($result->files));
        $this->assertFalse(isset($result->folders));
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::list
     * @covers \Vegfund\Jotta\Client\Responses\ResponseNamespace::except
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\Device::xmlDeserialize
     *
     * @throws JottaException
     * @throws Exception
     */
    public function test013_list_simple()
    {
        $body = (new ResponseBodyMock())->mountPoint([
            'name'    => Jotta::MOUNT_POINT_SHARED,
            'folders' => [
                [
                    'name'    => 'somefolder',
                ],
            ],
            'files' => [
                [
                    'name'    => 'one.txt',
                    'deleted' => time(),
                ],
            ],
        ]);

        $mock = new JottaApiV1Mock($body);
        $jotta = new JottaClient('a', 'b', $mock->getMock());
        $result = $jotta->mountPoint()->setMountPoint(Jotta::MOUNT_POINT_SHARED)->list();

        $this->assertSame(['somefolder' => []], $result);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::list
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::withDeleted
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::applyFilters
     * @throws JottaException
     */
    public function test015_list_with_deleted()
    {
        $body = (new ResponseBodyMock())->mountPoint([
            'name'    => Jotta::MOUNT_POINT_SHARED,
            'files' => [
                [
                    'name'    => 'one.txt',
                ],
                [
                    'name' => 'two.txt',
                    'deleted' => time()
                ]
            ],
        ]);

        $mock = new JottaApiV1Mock($body);
        $jotta = new JottaClient('a', 'b', $mock->getMock());
        $result = $jotta->mountPoint()->setMountPoint(Jotta::MOUNT_POINT_SHARED)->list();

        $this->assertSame(['one.txt'], $result);


        $body = (new ResponseBodyMock())->mountPoint([
            'name'    => Jotta::MOUNT_POINT_SHARED,
            'files' => [
                [
                    'name'    => 'one.txt',
                ],
                [
                    'name' => 'two.txt',
                    'deleted' => time()
                ]
            ],
        ]);

        $mock = new JottaApiV1Mock($body);
        $jotta = new JottaClient('a', 'b', $mock->getMock());
        $result = $jotta->mountPoint()->setMountPoint(Jotta::MOUNT_POINT_SHARED)->deleted(true)->list();

        $this->assertSame(['one.txt', 'two.txt'], $result);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::list
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::regex
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::applyFilters
     * @throws JottaException
     */
    public function test015_list_with_regex()
    {
        $body = (new ResponseBodyMock())->mountPoint([
            'name'    => Jotta::MOUNT_POINT_SHARED,
            'files' => [
                [
                    'name'    => 'one.txt',
                ],
                [
                    'name' => 'two.php',
                ],
                [
                    'name' => 'three.php',
                ],
                [
                    'name' => 'four.txt',
                ]
            ],
        ]);

        $mock = new JottaApiV1Mock($body);
        $jotta = new JottaClient('a', 'b', $mock->getMock());
        $result = $jotta->mountPoint()->setMountPoint(Jotta::MOUNT_POINT_SHARED)->regex('/.*\.php$/')->list();

        $this->assertSame(['two.php', 'three.php'], $result);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::list
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::uuid
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::applyFilters
     * @throws JottaException
     */
    public function test015_list_with_uuid()
    {
        $uuids = [];
        for($i = 0; $i < 4; $i++) {
            $uuids[] = Uuid::uuid4()->toString();
        }

        $body = (new ResponseBodyMock())->mountPoint([
            'name'    => Jotta::MOUNT_POINT_SHARED,
            'files' => [
                [
                    'name'    => 'one.txt',
                    'uuid' => $uuids[0]
                ],
                [
                    'name'    => 'two.txt',
                    'uuid' => $uuids[1]
                ],
                [
                    'name'    => 'three.txt',
                    'uuid' => $uuids[2]
                ],
                [
                    'name'    => 'one.txt',
                    'uuid' => $uuids[3]
                ],
            ],
        ]);

        $mock = new JottaApiV1Mock($body);
        $jotta = new JottaClient('a', 'b', $mock->getMock());
        $result = $jotta->mountPoint()->setMountPoint(Jotta::MOUNT_POINT_SHARED)->uuid($uuids[2])->list();

        $this->assertSame(['three.txt'], $result);
    }


    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::list
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::regex
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::withDeleted
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::applyFilters
     * @throws JottaException
     */
    public function test015_list_regex_and_deleted()
    {
        $body = (new ResponseBodyMock())->mountPoint([
            'name'    => Jotta::MOUNT_POINT_SHARED,
            'files' => [
                [
                    'name'    => 'one.txt',
                ],
                [
                    'name' => 'two.php',
                    'deleted' => time()
                ],
                [
                    'name' => 'three.php',
                ],
                [
                    'name' => 'four.txt',
                ]
            ],
        ]);

        $mock = new JottaApiV1Mock($body);
        $jotta = new JottaClient('a', 'b', $mock->getMock());
        $result = $jotta->mountPoint()->setMountPoint(Jotta::MOUNT_POINT_SHARED)->deleted(true)->regex('/.*\.php$/')->list();

        $this->assertSame(['two.php', 'three.php'], $result);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::create
     * @throws JottaException
     */
    public function test017_create_folder()
    {
        $folderName = md5(file_get_contents(__FILE__)) . '_017';
        $response = Jotta::client(getenv('JOTTA_USERNAME'), getenv('JOTTA_PASSWORD'))
            ->folder()
            ->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)
            ->create($folderName);

        $this->assertInstanceOf(Folder::class, $response);
        $this->assertSame($folderName, $response->getName());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::create
     * @throws JottaException
     */
    public function test019_create_subfolder()
    {
        $folderName = md5(file_get_contents(__FILE__)) . '_019';
        $subfolderName = md5(file_get_contents(__FILE__)) . '_sub019';
        $response = $this->jotta()
            ->folder()
            ->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)
            ->create($folderName . '/' . $subfolderName);

        $this->assertInstanceOf(Folder::class, $response);
        $this->assertSame($subfolderName, $response->getName());

        $this->assertSame('/'.getenv('JOTTA_USERNAME').'/'.Jotta::DEVICE_JOTTA.'/'.Jotta::MOUNT_POINT_ARCHIVE.'/'.$folderName, $response->getPath());

        // Get the root folder

        $rootResponse = $this->jotta()
            ->folder()
            ->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)
            ->get($folderName);

        $this->assertSame('/'.getenv('JOTTA_USERNAME').'/'.Jotta::DEVICE_JOTTA.'/'.Jotta::MOUNT_POINT_ARCHIVE, $rootResponse->getPath());
        $this->assertSame(1, (int) $rootResponse->getMetadata()->getAttribute('num_folders'));
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::create
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::delete
     * @throws JottaException
     */
    public function test021_delete_folder()
    {
        // first, create
        $folderName = md5(file_get_contents(__FILE__)) . '_021';
        $created = $this->jotta()->folder()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->create($folderName);

        // check if created
        $this->assertSame($folderName, $created->getName());

        // delete
        $deleted = $this->jotta()->folder()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->delete($folderName);
        $this->assertSame($folderName, $deleted->getName());
        $this->assertTrue($deleted->isDeleted());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::move
     * @throws JottaException
     */
    public function test023_move_folder_same_mount_point()
    {
        $rootFrom = md5(file_get_contents(__FILE__)) . '_021_from';
        $rootTo = md5(file_get_contents(__FILE__)) . '_021_to';
        $moved = md5(file_get_contents(__FILE__)) . '_021_moved';

        // first, create
        $created = $this->jotta()->folder()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->create($rootFrom.'/'.$moved);

        // check if created
        $this->assertSame($moved, $created->getName());

        // move
        $movingResult = $this->jotta()->folder()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->move($rootFrom.'/'.$moved, $rootTo.'/'.$moved);
        $this->assertSame($moved, $movingResult->getName());
        $this->assertSame('/'.getenv('JOTTA_USERNAME').'/'.Jotta::DEVICE_JOTTA.'/'.Jotta::MOUNT_POINT_ARCHIVE.'/'.$rootTo, $movingResult->getPath());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::rename
     * @throws JottaException
     */
    public function test023_rename_folder_same_mount_point()
    {
        $rootFrom = md5(file_get_contents(__FILE__)) . '_023_from';
        $rootTo = md5(file_get_contents(__FILE__)) . '_023_to';
        $moved = md5(file_get_contents(__FILE__)) . '_023_moved';

        // first, create
        $created = $this->jotta()->folder()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->create($rootFrom.'/'.$moved);

        // check if created
        $this->assertSame($moved, $created->getName());

        // move
        $movingResult = $this->jotta()->folder()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->rename($rootFrom.'/'.$moved, $rootTo.'/'.$moved);
        $this->assertSame($moved, $movingResult->getName());
        $this->assertSame('/'.getenv('JOTTA_USERNAME').'/'.Jotta::DEVICE_JOTTA.'/'.Jotta::MOUNT_POINT_ARCHIVE.'/'.$rootTo, $movingResult->getPath());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::move
     * @throws JottaException
     */
    public function test025_move_folder_different_mount_point()
    {
        $rootFrom = md5(file_get_contents(__FILE__)) . '_021_from';
        $rootTo = md5(file_get_contents(__FILE__)) . '_021_to';
        $moved = md5(file_get_contents(__FILE__)) . '_021_moved';

        // first, create
        $created = $this->jotta()->folder()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->create($rootFrom.'/'.$moved);

        // check if created
        $this->assertSame($moved, $created->getName());

        // move
        $movingResult = $this->jotta()->folder()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->move($rootFrom.'/'.$moved, $rootTo.'/'.$moved, Jotta::MOUNT_POINT_SHARED);
        $this->assertSame($moved, $movingResult->getName());
        $this->assertSame('/'.getenv('JOTTA_USERNAME').'/'.Jotta::DEVICE_JOTTA.'/'.Jotta::MOUNT_POINT_SHARED.'/'.$rootTo, $movingResult->getPath());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::move
     */
    public function test026_move_should_throw_exception_no_folder()
    {
        $folderName = Str::random(32) . '/' . Str::random(32);
        $this->shouldThrowException(Exception::class, function () use ($folderName) {
            $this->jotta()->folder()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->move($folderName, Str::random(32));
        });
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::move
     */
    public function test026_move_should_throw_exception_mount_point_mode()
    {
        $folderName = Str::random(32) . '/' . Str::random(32);
        $this->shouldThrowException(Exception::class, function () use ($folderName) {
            $this->jotta()->mountPoint()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->move($folderName, Str::random(32));
        });
    }

    /**
     * This test checks the implementation of a case, when a source folder name is empty.
     * In such a case, all items should be moved (but involves also files moving).
     *
     * @todo files moving
     */
    public function test027_move_when_folder_name_empty()
    {

    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::all
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::create
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::createMountPoint
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::delete
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::deleteMountPoint
     * @throws JottaException
     */
    public function test029_create_and_delete_mount_point()
    {
        // Initial number of mountpoints
        $count = count($this->jotta()->mountPoint()->all());

        $mountPointName = md5(file_get_contents(__FILE__)) . '_029';
        $response = $this->jotta()->directory()->setMountPoint($mountPointName)->create();

        $this->assertInstanceOf(MountPoint::class, $response);
        $this->assertSame($mountPointName, $response->getName());

        // delete

        $response = $this->jotta()->directory()->setMountPoint($mountPointName)->delete();
        $this->assertInstanceOf(Device::class, $response);

        $this->assertSame($count, count($this->jotta()->mountPoint()->all()));
    }

}
