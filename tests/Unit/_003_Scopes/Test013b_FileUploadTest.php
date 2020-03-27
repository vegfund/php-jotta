<?php

namespace Vegfund\Jotta\Tests\Unit\_003_Scopes;

use Illuminate\Support\Str;
use Vegfund\Jotta\Client\Responses\Namespaces\File;
use Vegfund\Jotta\Client\Responses\Namespaces\Folder;
use Vegfund\Jotta\Jotta;
use Vegfund\Jotta\Support\JFileInfo;
use Vegfund\Jotta\Tests\JottaTestCase;

class Test013b_FileUploadTest extends JottaTestCase
{
    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::upload
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::makeUpload
     * @throws \Vegfund\Jotta\Client\Exceptions\JottaException
     */
    public function test001_upload_when_path_is_a_folder()
    {
        // 1. Create a remote folder
        $remoteFolder = Str::random(24).'_001';

        $this->jotta()->folder()->create($remoteFolder);
        $this->assertInstanceOf(Folder::class, $this->jotta()->folder()->get($remoteFolder));

        // 2. Create a local file
        $localFile = Str::random(24).'_001.txt';
        $localPath = $this->tempPath($localFile);

        file_put_contents($localPath, Str::random(1024));

        // 3. Try uploading
        $uploaded = $this->jotta()->file()->upload($localPath, $remoteFolder);
        $this->assertInstanceOf(File::class, $uploaded);
        $this->assertTrue($uploaded->isValid());
        $this->assertTrue($this->jotta()->file()->verify($remoteFolder, $localPath));
        $this->assertTrue(0 !== (int) preg_match('/.+'.$remoteFolder.'$/', $uploaded->getPath()));

        // 4. Tear down

        $this->addToTempList($remoteFolder.'/'.$localFile, 'file');
        $this->addToTempList($remoteFolder, 'folder');
        @unlink($localPath);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::upload
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::makeUpload
     * @throws \Vegfund\Jotta\Client\Exceptions\JottaException
     */
    public function test003_upload_overwrite_always_success()
    {
        // 1. Create remote file
        $localFile = Str::random(24).'_003.txt';
        $localPath = $this->tempPath($localFile);

        file_put_contents($localPath, Str::random(1024));

        // 2. Try uploading
        $uploaded = $this->jotta()->file()->upload($localPath, $localFile);
        $this->assertInstanceOf(File::class, $uploaded);
        $this->assertTrue($uploaded->isValid());
        $this->assertTrue($this->jotta()->file()->verify($localFile, $localPath));

        // 3. In the meantime, change the local file

        file_put_contents($localPath, Str::random(1024));

        // 4. Try uploading
        $uploaded = $this->jotta()->file()->upload($localPath, $localFile, Jotta::FILE_OVERWRITE_ALWAYS);
        $this->assertInstanceOf(File::class, $uploaded);
        $this->assertTrue($uploaded->isValid());
        $this->assertTrue($this->jotta()->file()->verify($localFile, $localPath));

        $this->assertNull($this->jotta()->file()->upload($localPath, $localFile, Jotta::FILE_OVERWRITE_IF_NEWER_OR_DIFFERENT));

        // 5. Tear down
        $this->addToTempList($localFile, 'file');
        @unlink($localPath);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::upload
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::makeUpload
     * @throws \Vegfund\Jotta\Client\Exceptions\JottaException
     */
    public function test005_upload_overwrite_never()
    {
        // 1. Create remote file
        $localFile = Str::random(24).'_005.txt';
        $localPath = $this->tempPath($localFile);

        file_put_contents($localPath, Str::random(1024));

        // 2. Try uploading
        $uploaded = $this->jotta()->file()->upload($localPath, $localFile);
        $this->assertInstanceOf(File::class, $uploaded);
        $this->assertTrue($uploaded->isValid());
        $this->assertTrue($this->jotta()->file()->verify($localFile, $localPath));

        // 3. In the meantime, change the local file

        file_put_contents($localPath, Str::random(1024));

        // 4. Try uploading
        $this->assertNull($this->jotta()->file()->upload($localPath, $localFile, Jotta::FILE_OVERWRITE_NEVER));

        // 5. Tear down
        $this->addToTempList($localFile, 'file');
        @unlink($localPath);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::upload
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::makeUpload
     * @throws \Vegfund\Jotta\Client\Exceptions\JottaException
     */
    public function test007_upload_overwrite_if_different_success()
    {
        // 1. Create local (older) file
        $localOlderFile = Str::random(24).'_007_older.txt';
        $localOlderPath = $this->tempPath($localOlderFile);

        file_put_contents($localOlderPath, Str::random(1024));

        // 2. Wait 3 seconds
        sleep(3);

        // 3. Create local (newer) file
        $localNewerFile = Str::random(24).'_007_newer.txt';
        $localNewerPath = $this->tempPath($localOlderFile);

        file_put_contents($localOlderPath, Str::random(1024));

        // 4. Upload newer file
        $this->shouldNotThrowException(function () use ($localNewerFile, $localNewerPath) {
            $this->jotta()->file()->upload($localNewerPath, $localNewerFile);
        });

        // 5. Upload local (older) file
        $this->shouldNotThrowException(function () use ($localNewerFile, $localOlderPath) {
            $this->jotta()->file()->upload($localOlderPath, $localNewerFile, Jotta::FILE_OVERWRITE_IF_DIFFERENT);
        });

        $uploaded = $this->jotta()->file()->get($localNewerFile);
        $this->assertSame(JFileInfo::make($localNewerPath)->getMd5(), $uploaded->getMd5());

        // 6. Tear down
        $this->addToTempList($localOlderFile, 'file');
        $this->addToTempList($localNewerFile, 'file');
        @unlink($localNewerPath);
        @unlink($localOlderPath);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::upload
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::makeUpload
     * @throws \Vegfund\Jotta\Client\Exceptions\JottaException
     */
    public function test009_upload_overwrite_if_different_fail()
    {
        // 1. Create local file
        $localOlderFile = Str::random(24).'_009_older.txt';
        $localOlderPath = $this->tempPath($localOlderFile);

        file_put_contents($localOlderPath, Str::random(1024));

        // 2. Wait 3 seconds
        sleep(3);

        // 3. Create local (newer) file
        $localNewerFile = $localOlderFile;
        $localNewerPath = $localOlderPath;

        // 4. Upload newer file
        $this->shouldNotThrowException(function () use ($localNewerFile, $localNewerPath) {
            $this->jotta()->file()->upload($localNewerPath, $localNewerFile);
        });

        // 5. Upload local (older) file
        $this->shouldNotThrowException(function () use ($localNewerFile, $localOlderPath) {
            $this->assertNull($this->jotta()->file()->upload($localOlderPath, $localNewerFile, Jotta::FILE_OVERWRITE_IF_DIFFERENT));
        });

        $uploaded = $this->jotta()->file()->get($localNewerFile);
        $this->assertSame(JFileInfo::make($localNewerPath)->getMd5(), $uploaded->getMd5());

        // 6. Tear down
        $this->addToTempList($localOlderFile, 'file');
        $this->addToTempList($localNewerFile, 'file');
        @unlink($localNewerPath);
        @unlink($localOlderPath);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::upload
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::makeUpload
     * @throws \Vegfund\Jotta\Client\Exceptions\JottaException
     */
    public function test011_upload_overwrite_if_newer_success()
    {
        // 1. Create local (older) file
        $localOlderFile = Str::random(24).'_011_older.txt';
        $localOlderPath = $this->tempPath($localOlderFile);

        file_put_contents($localOlderPath, Str::random(1024));

        // 2. Wait 3 seconds
        sleep(3);

        // 3. Create local (newer) file
        $localNewerFile = Str::random(24).'_011_newer.txt';
        $localNewerPath = $this->tempPath($localOlderFile);

        file_put_contents($localOlderPath, Str::random(1024));

        // 4. Upload older file
        $this->shouldNotThrowException(function () use ($localOlderFile, $localOlderPath) {
            $this->jotta()->file()->upload($localOlderPath, $localOlderFile);
        });

        // 5. Upload local (newer) file
        $this->shouldNotThrowException(function () use ($localOlderFile, $localNewerPath) {
            $this->jotta()->file()->upload($localNewerPath, $localOlderFile, Jotta::FILE_OVERWRITE_IF_NEWER);
        });

        $uploaded = $this->jotta()->file()->get($localOlderFile);
        $this->assertSame(JFileInfo::make($localOlderPath)->getMd5(), $uploaded->getMd5());

        // 6. Tear down
        $this->addToTempList($localOlderFile, 'file');
        $this->addToTempList($localNewerFile, 'file');
        @unlink($localNewerPath);
        @unlink($localOlderPath);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::upload
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::makeUpload
     * @throws \Vegfund\Jotta\Client\Exceptions\JottaException
     */
    public function test013_upload_overwrite_if_newer_fail()
    {
        // 1. Create local file
        $localOlderFile = Str::random(24).'_013_older.txt';
        $localOlderPath = $this->tempPath($localOlderFile);

        file_put_contents($localOlderPath, Str::random(1024));

        // 2. Wait 3 seconds
        sleep(3);

        // 3. Create local (newer) file
        $localNewerFile = $localOlderFile;
        $localNewerPath = $localOlderPath;

        // 4. Upload newer file
        $this->shouldNotThrowException(function () use ($localNewerFile, $localNewerPath) {
            $this->jotta()->file()->upload($localNewerPath, $localNewerFile);
        });

        // 5. Upload local (older) file
        $this->shouldNotThrowException(function () use ($localNewerFile, $localOlderPath) {
            $this->assertNull($this->jotta()->file()->upload($localOlderPath, $localNewerFile, Jotta::FILE_OVERWRITE_IF_NEWER));
        });

        $uploaded = $this->jotta()->file()->get($localNewerFile);
        $this->assertSame(JFileInfo::make($localNewerPath)->getMd5(), $uploaded->getMd5());

        // 6. Tear down
        $this->addToTempList($localOlderFile, 'file');
        $this->addToTempList($localNewerFile, 'file');
        @unlink($localNewerPath);
        @unlink($localOlderPath);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::upload
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::makeUpload
     * @throws \Vegfund\Jotta\Client\Exceptions\JottaException
     */
    public function test015_upload_overwrite_if_different_or_newer_success()
    {
        // DIFFERENT

        // 1. Create local (older) file
        $localOlderFile = Str::random(24).'_015_older.txt';
        $localOlderPath = $this->tempPath($localOlderFile);

        file_put_contents($localOlderPath, Str::random(1024));

        // 2. Wait 3 seconds
        sleep(3);

        // 3. Create local (newer) file
        $localNewerFile = Str::random(24).'_015_newer.txt';
        $localNewerPath = $this->tempPath($localOlderFile);

        file_put_contents($localOlderPath, Str::random(1024));

        // 4. Upload newer file
        $this->shouldNotThrowException(function () use ($localNewerFile, $localNewerPath) {
            $this->jotta()->file()->upload($localNewerPath, $localNewerFile);
        });

        // 5. Upload local (older) file
        $this->shouldNotThrowException(function () use ($localNewerFile, $localOlderPath) {
            $this->jotta()->file()->upload($localOlderPath, $localNewerFile, Jotta::FILE_OVERWRITE_IF_NEWER_OR_DIFFERENT);
        });

        $uploaded = $this->jotta()->file()->get($localNewerFile);
        $this->assertSame(JFileInfo::make($localNewerPath)->getMd5(), $uploaded->getMd5());

        // 6. Tear down
        $this->addToTempList($localOlderFile, 'file');
        $this->addToTempList($localNewerFile, 'file');
        @unlink($localNewerPath);
        @unlink($localOlderPath);

        // NEWER


        // 1. Create local (older) file
        $localOlderFile = Str::random(24).'_011_older.txt';
        $localOlderPath = $this->tempPath($localOlderFile);

        file_put_contents($localOlderPath, Str::random(1024));

        // 2. Wait 3 seconds
        sleep(3);

        // 3. Create local (newer) file
        $localNewerFile = Str::random(24).'_011_newer.txt';
        $localNewerPath = $this->tempPath($localOlderFile);

        file_put_contents($localOlderPath, Str::random(1024));

        // 4. Upload older file
        $this->shouldNotThrowException(function () use ($localOlderFile, $localOlderPath) {
            $this->jotta()->file()->upload($localOlderPath, $localOlderFile);
        });

        // 5. Upload local (newer) file
        $this->shouldNotThrowException(function () use ($localOlderFile, $localNewerPath) {
            $this->jotta()->file()->upload($localNewerPath, $localOlderFile, Jotta::FILE_OVERWRITE_IF_NEWER_OR_DIFFERENT);
        });

        $uploaded = $this->jotta()->file()->get($localOlderFile);
        $this->assertSame(JFileInfo::make($localOlderPath)->getMd5(), $uploaded->getMd5());

        // 6. Tear down
        $this->addToTempList($localOlderFile, 'file');
        $this->addToTempList($localNewerFile, 'file');
        @unlink($localNewerPath);
        @unlink($localOlderPath);
    }
}