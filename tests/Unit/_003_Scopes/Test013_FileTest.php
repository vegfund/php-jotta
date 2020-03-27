<?php

namespace Vegfund\Jotta\Tests\Unit\_003_Scopes;

use Exception;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Vegfund\Jotta\Client\Exceptions\JottaException;
use Vegfund\Jotta\Client\Responses\Namespaces\File;
use Vegfund\Jotta\Client\Responses\Namespaces\Folder;
use Vegfund\Jotta\Jotta;
use Vegfund\Jotta\Support\JFileInfo;
use Vegfund\Jotta\Tests\JottaTestCase;
use Vegfund\Jotta\Tests\Mock\ResponseBodyMock;

/**
 * Class Test013_FileTest.
 */
class Test013_FileTest extends JottaTestCase
{
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

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::get
     *
     * @throws Exception
     */
    public function test003_get_if_folder_should_throw_exception()
    {
        $filename = Str::random(12).'.txt';
        $body = (new ResponseBodyMock())->folder([
            'name'       => $filename,
            'mountPoint' => Jotta::MOUNT_POINT_ARCHIVE,
        ]);

        $mock = $this->jottaMock($body);
        $this->shouldThrowException(JottaException::class, function () use ($mock, $filename) {
            $mock->file()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->get($filename);
        });
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::upload
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::makeUpload
     *
     * @throws Exception
     */
    public function test005_upload_simple_small_file()
    {
        // generate random file, 256 KB
        $filename = Str::random(16).'_005.txt';
        $path = $this->tempPath($filename);

        $f = fopen($path, 'a');
        for ($i = 0; $i < 256 * 1024; $i += 512) {
            fwrite($f, Str::random(512));
        }
        fclose($f);

        $fileinfo = JFileInfo::make($path);

        //
        $response = $this->jotta()->file()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->upload($path);
        $this->assertInstanceOf(File::class, $response);
        $this->assertTrue($response->isCompleted());

        $this->assertSame($filename, $response->getName());
        $this->assertSame($fileinfo->getMd5(), $response->getMd5());
        $this->assertSame(256 * 1024, $response->getSize());

        $this->addToTempList($filename, 'file');

        @unlink($path);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::upload
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::makeUpload
     *
     * @throws Exception
     */
    public function test007_upload_simple_large_file_25mb()
    {
        if (true !== getenv('JOTTA_TEST_LARGE_FILES')) {
            $this->markTestSkipped('Testing large files upload skipped. Set JOTTA_TEST_LARGE_FILES env to true');
        }

        // generate random file, 25 MB
        $filename = Str::random(16).'_007.txt';
        $path = $this->tempPath($filename);

        $f = fopen($path, 'a');
        for ($i = 0; $i < 25 * 1024 * 1024; $i += 512) {
            fwrite($f, Str::random(512));
        }
        fclose($f);

        $fileinfo = JFileInfo::make($path);

        //
        $response = $this->jotta()->file()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->upload($path);
        $this->assertInstanceOf(File::class, $response);
        $this->assertTrue($response->isCompleted());

        $this->assertSame($filename, $response->getName());
        $this->assertSame($fileinfo->getMd5(), $response->getMd5());
        $this->assertSame(25 * 1024 * 1024, $response->getSize());

        $this->addToTempList($filename, 'file');

        @unlink($path);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::upload
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::makeUpload
     *
     * @throws Exception
     */
    public function test009_upload_simple_large_file_100mb()
    {
        if (true !== getenv('JOTTA_TEST_LARGE_FILES')) {
            $this->markTestSkipped('Testing large files upload skipped. Set JOTTA_TEST_LARGE_FILES env to true');
        }

        // generate random file, 100 MB
        $filename = Str::random(16).'_009.txt';
        $path = $this->tempPath($filename);

        $f = fopen($path, 'a');
        for ($i = 0; $i < 100 * 1024 * 1024; $i += 512) {
            fwrite($f, Str::random(512));
        }
        fclose($f);

        $fileinfo = JFileInfo::make($path);

        //
        $response = $this->jotta()->file()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->upload($path);
        $this->assertInstanceOf(File::class, $response);
        $this->assertTrue($response->isCompleted());

        $this->assertSame($filename, $response->getName());
        $this->assertSame($fileinfo->getMd5(), $response->getMd5());
        $this->assertSame(100 * 1024 * 1024, $response->getSize());

        $this->addToTempList($filename, 'file');

        @unlink($path);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::upload
     */
    public function test011_upload_no_file_should_throw_exception()
    {
        $path = $this->tempPath(Str::random(16).'.php');
        $this->shouldThrowException(JottaException::class, function () use ($path) {
            $this->jotta()->file()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->upload($path);
        });
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::upload
     */
    public function test011a_upload_no_file_should_throw_exception()
    {
        $path = $this->tempPath(Str::random(16).'.php');
        $this->shouldThrowException(JottaException::class, function () use ($path) {
            $this->jotta()->file()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->upload($path);
        });
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::move
     *
     * @throws JottaException
     */
    public function test013_move_file()
    {
        // generate random file, 256 KB
        $filename = Str::random(16).'_013.txt';
        $path = $this->tempPath($filename);

        $f = fopen($path, 'a');
        for ($i = 0; $i < 256 * 1024; $i += 512) {
            fwrite($f, Str::random(512));
        }
        fclose($f);

        $fileinfo = JFileInfo::make($path);

        //
        $response = $this->jotta()->file()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->upload($path);
        $this->assertInstanceOf(File::class, $response);
        $this->assertTrue($response->isCompleted());

        // test moving
        $destinationPath = Str::random(12).'_13_to';
        $response = $this->jotta()->file()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->move($filename, $destinationPath);
        $this->assertInstanceOf(File::class, $response);
        $this->assertTrue($response->isCompleted());
        $this->assertSame('/'.getenv('JOTTA_USERNAME').'/'.Jotta::DEVICE_JOTTA.'/'.Jotta::MOUNT_POINT_ARCHIVE.'/'.$destinationPath, $response->getPath());
        $this->assertSame($fileinfo->getMd5(), $response->getMd5());

        $this->shouldThrowException(Exception::class, function () use ($filename) {
            $this->jotta()->file()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->get($filename);
        });

        $this->addToTempList($destinationPath.'/'.$filename, 'file');
        $this->addToTempList($destinationPath, 'folder');
        $this->addToTempList($filename, 'file');

        @unlink($path);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::rename
     *
     * @throws JottaException
     */
    public function test013a_rename_file()
    {
        // generate random file, 256 KB
        $filename = Str::random(16).'_013a.txt';
        $path = $this->tempPath($filename);

        $f = fopen($path, 'a');
        for ($i = 0; $i < 256 * 1024; $i += 512) {
            fwrite($f, Str::random(512));
        }
        fclose($f);

        $fileinfo = JFileInfo::make($path);

        //
        $response = $this->jotta()->file()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->upload($path);
        $this->assertInstanceOf(File::class, $response);
        $this->assertTrue($response->isCompleted());

        // test moving
        $destinationPath = Str::random(12).'_013a_to';
        $response = $this->jotta()->file()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->rename($filename, $destinationPath);
        $this->assertInstanceOf(File::class, $response);
        $this->assertTrue($response->isCompleted());
        $this->assertSame('/'.getenv('JOTTA_USERNAME').'/'.Jotta::DEVICE_JOTTA.'/'.Jotta::MOUNT_POINT_ARCHIVE.'/'.$destinationPath, $response->getPath());
        $this->assertSame($fileinfo->getMd5(), $response->getMd5());

        $this->shouldThrowException(Exception::class, function () use ($filename) {
            $this->jotta()->file()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->get($filename);
        });

        $this->addToTempList($destinationPath.'/'.$filename, 'file');
        $this->addToTempList($destinationPath, 'folder');
        $this->addToTempList($filename, 'file');

        @unlink($path);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::move
     *
     * @throws JottaException
     */
    public function test013b_move_when_folder_should_throw_exception()
    {
        $folderName = Str::random(12).'_013b';
        $response = $this->jotta()->folder()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->create($folderName);
        $this->assertInstanceOf(Folder::class, $response);

        $this->shouldThrowException(Exception::class, function () use ($folderName) {
            $this->jotta()->directory()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->move($folderName, 'other/path');
        });

        $this->jotta()->directory()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->delete($folderName);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::delete
     *
     * @throws JottaException
     */
    public function test015_delete_file()
    {
        // generate random file, 256 KB
        $filename = Str::random(16).'_015.txt';
        $path = $this->tempPath($filename);

        $f = fopen($path, 'a');
        for ($i = 0; $i < 256 * 1024; $i += 512) {
            fwrite($f, Str::random(512));
        }
        fclose($f);

        //
        $response = $this->jotta()->file()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->upload($path);
        $this->assertInstanceOf(File::class, $response);
        $this->assertTrue($response->isCompleted());
        $this->assertSame($filename, $response->getName());

        // Delete tests
        $this->jotta()->file()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->delete($filename);
        $this->shouldThrowException(Exception::class, function () use ($filename) {
            $file = $this->jotta()->file()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->get($filename);
        });

        $this->addToTempList($filename, 'file');

        @unlink($path);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::delete
     *
     * @throws Exception
     */
    public function test017_delete_when_deleted_should_throw_exception()
    {
        $filename = Str::random(12).'_017.txt';
        $uuid = Uuid::uuid4()->toString();
        $body = (new ResponseBodyMock())->file([
            'name'       => $filename,
            'uuid'       => $uuid,
            'mountPoint' => Jotta::MOUNT_POINT_ARCHIVE,
            'deleted'    => time() - 60 * 60 * 20,
        ]);

        $mock = $this->jottaMock($body);
        $this->shouldThrowException(Exception::class, function () use ($filename) {
            $mock->file()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->delete($filename);
        });
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::verify
     * @throws JottaException
     */
    public function test019_verify_file()
    {
        // generate random file, 256 KB
        $filename = Str::random(16).'_013.txt';
        $path = $this->tempPath($filename);

        $f = fopen($path, 'a');
        for ($i = 0; $i < 256 * 1024; $i += 512) {
            fwrite($f, Str::random(512));
        }
        fclose($f);

        //
        $response = $this->jotta()->file()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->upload($path);
        $this->assertInstanceOf(File::class, $response);
        $this->assertTrue($response->isCompleted());

        $this->assertTrue($this->jotta()->file()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->verify($filename, $path));
        $this->assertTrue($this->jotta()->file()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->verify($filename));

        $f = fopen($path, 'a');
        for ($i = 0; $i < 256 * 1024; $i += 512) {
            fwrite($f, Str::random(512));
        }
        fclose($f);

        $this->assertFalse($this->jotta()->file()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->verify($filename, $path));
        $this->assertTrue($this->jotta()->file()->setMountPoint(Jotta::MOUNT_POINT_ARCHIVE)->verify($filename));

        $this->addToTempList($filename, 'file');
        @unlink($path);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::verify
     */
    public function test021_verify_file_local_missing_should_throw_exception()
    {
        $filename = Str::random(16).'_013.txt';
        $this->shouldThrowException(JottaException::class, function () use ($filename) {
            $this->jotta()->file()->verify($filename, $filename);
        });
    }
}
