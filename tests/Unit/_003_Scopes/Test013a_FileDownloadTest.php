<?php

namespace Vegfund\Jotta\Tests\Unit\_003_Scopes;

use Illuminate\Support\Str;
use Spatie\Image\Image;
use Vegfund\Jotta\Jotta;
use Vegfund\Jotta\Support\JFileInfo;
use Vegfund\Jotta\Tests\JottaTestCase;

class Test013a_FileDownloadTest extends JottaTestCase
{
    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::download
     *
     * @throws \Vegfund\Jotta\Client\Exceptions\JottaException
     */
    public function test001_download()
    {
        // 1. Create local source folder and file
        $localSourceFolder = $this->tempPath(Str::random(24).'_001_from');
        $localSourceFile = Str::random(12).'.txt';
        $localSourcePath = $localSourceFolder.DIRECTORY_SEPARATOR.$localSourceFile;

        @mkdir($localSourceFolder);
        $f = fopen($localSourcePath, 'w');
        for ($i = 0; $i < 256 * 1024; $i += 512) {
            fwrite($f, Str::random(512));
        }
        fclose($f);

        // 2. Create local destination folder
        $localDestFolder = $this->tempPath(Str::random(24).'_001_to');
        $localDestPath = $localDestFolder.DIRECTORY_SEPARATOR.$localSourceFile;

        // 3. Upload
        $this->jotta()->file()->upload($localSourcePath);
        $this->assertTrue($this->jotta()->file()->verify($localSourceFile, $localSourcePath));

        // 4. Try downloading
        $this->jotta()->file()->download($localSourceFile, $localDestPath);
        $this->assertTrue(file_exists($localDestPath));
        $this->assertSame(JFileInfo::make($localSourcePath)->getMd5(), JFileInfo::make($localDestPath)->getMd5());

        // 99. Tear down
        $this->addToTempList($localSourceFile, 'file');

        @unlink($localSourcePath);
        @unlink($localDestPath);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::download
     *
     * @throws \Vegfund\Jotta\Client\Exceptions\JottaException
     */
    public function test003_download_overwrite_never()
    {
        // 1. Create local source folder and file
        $localSourceFolder = $this->tempPath(Str::random(24).'_003_from');
        $localSourceFile = Str::random(12).'.txt';
        $localSourcePath = $localSourceFolder.DIRECTORY_SEPARATOR.$localSourceFile;

        @mkdir($localSourceFolder);
        $f = fopen($localSourcePath, 'w');
        for ($i = 0; $i < 256 * 1024; $i += 512) {
            fwrite($f, Str::random(512));
        }
        fclose($f);

        // 2. Create local destination folder
        $localDestFolder = $this->tempPath(Str::random(24).'_003_to');
        $localDestPath = $localDestFolder.DIRECTORY_SEPARATOR.$localSourceFile;

        // 3. Upload
        $this->jotta()->file()->upload($localSourcePath);
        $this->assertTrue($this->jotta()->file()->verify($localSourceFile, $localSourcePath));

        // 3a. In the meantime, create the local file
        @mkdir($localDestFolder);
        $f = fopen($localDestPath, 'w');
        for ($i = 0; $i < 256 * 1024; $i += 512) {
            fwrite($f, Str::random(512));
        }
        fclose($f);

        // 4. Try downloading
        $this->assertNull($this->jotta()->file()->download($localSourceFile, $localDestPath, Jotta::FILE_OVERWRITE_NEVER));

        // 99. Tear down
        $this->addToTempList($localSourceFile, 'file');

        @unlink($localSourcePath);
        @unlink($localDestPath);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::download
     *
     * @throws \Vegfund\Jotta\Client\Exceptions\JottaException
     */
    public function test005_download_overwrite_always()
    {
        // 1. Create local source folder and file
        $localSourceFolder = $this->tempPath(Str::random(24).'_005_from');
        $localSourceFile = Str::random(12).'.txt';
        $localSourcePath = $localSourceFolder.DIRECTORY_SEPARATOR.$localSourceFile;

        @mkdir($localSourceFolder);
        $f = fopen($localSourcePath, 'w');
        for ($i = 0; $i < 256 * 1024; $i += 512) {
            fwrite($f, Str::random(512));
        }
        fclose($f);

        // 2. Create local destination folder
        $localDestFolder = $this->tempPath(Str::random(24).'_005_to');
        $localDestPath = $localDestFolder.DIRECTORY_SEPARATOR.$localSourceFile;

        // 3. Upload
        $this->jotta()->file()->upload($localSourcePath);
        $this->assertTrue($this->jotta()->file()->verify($localSourceFile, $localSourcePath));

        // 3a. In the meantime, create the local file
        @mkdir($localDestFolder);
        $f = fopen($localDestPath, 'w');
        for ($i = 0; $i < 256 * 1024; $i += 512) {
            fwrite($f, Str::random(512));
        }
        fclose($f);

        // 4. Try downloading
        $this->jotta()->file()->download($localSourceFile, $localDestPath, Jotta::FILE_OVERWRITE_ALWAYS);
        $this->assertTrue(file_exists($localDestPath));
        $this->assertSame(JFileInfo::make($localSourcePath)->getMd5(), JFileInfo::make($localDestPath)->getMd5());

        // 99. Tear down
        $this->addToTempList($localSourceFile, 'file');

        @unlink($localSourcePath);
        @unlink($localDestPath);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::download
     *
     * @throws \Vegfund\Jotta\Client\Exceptions\JottaException
     */
    public function test007_download_overwrite_if_newer_success()
    {
        // 0. Create local destination folder
        $localSourceFile = Str::random(12).'.txt';
        $localDestFolder = $this->tempPath(Str::random(24).'_007_to');
        $localDestPath = $localDestFolder.DIRECTORY_SEPARATOR.$localSourceFile;

        // 0. First, create the local destination file
        @mkdir($localDestFolder);
        $f = fopen($localDestPath, 'w');
        for ($i = 0; $i < 256 * 1024; $i += 512) {
            fwrite($f, Str::random(512));
        }
        fclose($f);

        sleep(3);

        // 1. Create local source folder and file
        $localSourceFolder = $this->tempPath(Str::random(24).'_007_from');
        $localSourcePath = $localSourceFolder.DIRECTORY_SEPARATOR.$localSourceFile;

        @mkdir($localSourceFolder);
        $f = fopen($localSourcePath, 'w');
        for ($i = 0; $i < 256 * 1024; $i += 512) {
            fwrite($f, Str::random(512));
        }
        fclose($f);

        // 3. Upload
        $this->jotta()->file()->upload($localSourcePath);
        $this->assertTrue($this->jotta()->file()->verify($localSourceFile, $localSourcePath));

        // 4. Try downloading
        $this->jotta()->file()->download($localSourceFile, $localDestPath, Jotta::FILE_OVERWRITE_IF_NEWER);
        $this->assertTrue(file_exists($localDestPath));
        $this->assertSame(JFileInfo::make($localSourcePath)->getMd5(), JFileInfo::make($localDestPath)->getMd5());

        // 99. Tear down
        $this->addToTempList($localSourceFile, 'file');

        @unlink($localSourcePath);
        @unlink($localDestPath);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::download
     *
     * @throws \Vegfund\Jotta\Client\Exceptions\JottaException
     */
    public function test007a_download_overwrite_if_newer_fail()
    {
        // 0. Create local destination folder
        $localSourceFile = Str::random(12).'.txt';
        $localDestFolder = $this->tempPath(Str::random(24).'_007a_to');
        $localDestPath = $localDestFolder.DIRECTORY_SEPARATOR.$localSourceFile;

        // 1. Create local source folder and file
        $localSourceFolder = $this->tempPath(Str::random(24).'_007a_from');
        $localSourcePath = $localSourceFolder.DIRECTORY_SEPARATOR.$localSourceFile;

        @mkdir($localSourceFolder);
        $f = fopen($localSourcePath, 'w');
        for ($i = 0; $i < 256 * 1024; $i += 512) {
            fwrite($f, Str::random(512));
        }
        fclose($f);

        // 3. Upload
        $this->jotta()->file()->upload($localSourcePath);
        $this->assertTrue($this->jotta()->file()->verify($localSourceFile, $localSourcePath));

        // 0. Then, create the local destination file
        @mkdir($localDestFolder);
        $f = fopen($localDestPath, 'w');
        for ($i = 0; $i < 256 * 1024; $i += 512) {
            fwrite($f, Str::random(512));
        }
        fclose($f);

        sleep(3);

        // 4. Try downloading
        $this->assertNull($this->jotta()->file()->download($localSourceFile, $localDestPath, Jotta::FILE_OVERWRITE_IF_NEWER));
        $this->assertTrue(file_exists($localDestPath));
        $this->assertNotSame(JFileInfo::make($localSourcePath)->getMd5(), JFileInfo::make($localDestPath)->getMd5());

        $this->assertNotNull($this->jotta()->file()->download($localSourceFile, $localDestPath, Jotta::FILE_OVERWRITE_IF_NEWER_OR_DIFFERENT));

        // 99. Tear down
        $this->addToTempList($localSourceFile, 'file');

        @unlink($localSourcePath);
        @unlink($localDestPath);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::download
     *
     * @throws \Vegfund\Jotta\Client\Exceptions\JottaException
     */
    public function test009_download_overwrite_if_different_success()
    {
        // 0. Create local destination folder
        $localSourceFile = Str::random(12).'.txt';
        $localDestFolder = $this->tempPath(Str::random(24).'_009_to');
        $localDestPath = $localDestFolder.DIRECTORY_SEPARATOR.$localSourceFile;

        // 0. First, create the local destination file
        @mkdir($localDestFolder);
        $f = fopen($localDestPath, 'w');
        for ($i = 0; $i < 256 * 1024; $i += 512) {
            fwrite($f, Str::random(512));
        }
        fclose($f);

        sleep(3);

        // 1. Create local source folder and file
        $localSourceFolder = $this->tempPath(Str::random(24).'_009_from');
        $localSourcePath = $localSourceFolder.DIRECTORY_SEPARATOR.$localSourceFile;

        @mkdir($localSourceFolder);
        $f = fopen($localSourcePath, 'w');
        for ($i = 0; $i < 256 * 1024; $i += 512) {
            fwrite($f, Str::random(512));
        }
        fclose($f);

        // 3. Upload
        $this->jotta()->file()->upload($localSourcePath);
        $this->assertTrue($this->jotta()->file()->verify($localSourceFile, $localSourcePath));

        // 4. Try downloading
        $this->jotta()->file()->download($localSourceFile, $localDestPath, Jotta::FILE_OVERWRITE_IF_DIFFERENT);
        $this->assertTrue(file_exists($localDestPath));
        $this->assertSame(JFileInfo::make($localSourcePath)->getMd5(), JFileInfo::make($localDestPath)->getMd5());

        // 99. Tear down
        $this->addToTempList($localSourceFile, 'file');

        @unlink($localSourcePath);
        @unlink($localDestPath);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::download
     *
     * @throws \Vegfund\Jotta\Client\Exceptions\JottaException
     */
    public function test009a_download_overwrite_if_different_fail()
    {
        // 0. Create local destination folder
        $localSourceFile = Str::random(12).'.txt';
        $localDestFolder = $this->tempPath(Str::random(24).'_009a_to');
        $localDestPath = $localDestFolder.DIRECTORY_SEPARATOR.$localSourceFile;

        // 1. Create local source folder and file
        $localSourceFolder = $this->tempPath(Str::random(24).'_009a_from');
        $localSourcePath = $localSourceFolder.DIRECTORY_SEPARATOR.$localSourceFile;

        @mkdir($localSourceFolder);
        $f = fopen($localSourcePath, 'w');
        for ($i = 0; $i < 256 * 1024; $i += 512) {
            fwrite($f, Str::random(512));
        }
        fclose($f);

        @mkdir($localDestFolder);
        copy($localSourcePath, $localDestPath);

        // 3. Upload
        $this->jotta()->file()->upload($localSourcePath);
        $this->assertTrue($this->jotta()->file()->verify($localSourceFile, $localSourcePath));

        // 4. Try downloading
        $this->assertNull($this->jotta()->file()->download($localSourceFile, $localDestPath, Jotta::FILE_OVERWRITE_IF_DIFFERENT));
        $this->assertNotNull($this->jotta()->file()->download($localSourceFile, $localDestPath, Jotta::FILE_OVERWRITE_IF_NEWER_OR_DIFFERENT));

        // 99. Tear down
        $this->addToTempList($localSourceFile, 'file');

        @unlink($localSourcePath);
        @unlink($localDestPath);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::thumbnail
     *
     * @throws \Vegfund\Jotta\Client\Exceptions\JottaException
     */
    public function test017_download_thumbnail()
    {
        // 1. Create local image
        $localFilename = Str::random(12).'.jpg';
        $localPath = $this->tempPath($localFilename);

        copy('https://picsum.photos/800/600', $localPath);
        $fileinfo = JFileInfo::make($localPath);
        $this->assertTrue($fileinfo->getSize() > 1);

        // 2. Upload
        $this->jotta()->file()->upload($localPath);
        $this->assertTrue($this->jotta()->file()->verify($localFilename, $localPath));

        // 3. Thumbnail size small (WS)
        $localThumbnailWs = str_replace('.jpg', '_ws.jpg', $localPath);
        $this->jotta()->file()->thumbnail($localFilename, $localThumbnailWs, Jotta::THUMBNAIL_SIZE_SMALL);
        $this->assertTrue(file_exists($localThumbnailWs));
        $image = Image::load($localThumbnailWs);
        $this->assertSame(30, $image->getWidth());
        $this->assertSame(30, $image->getHeight());
        $this->assertSame('image/jpeg', mime_content_type($localThumbnailWs));

        // 3. Thumbnail size medium (WM)
        $localThumbnailWm = str_replace('.jpg', '_wm.jpg', $localPath);
        $this->jotta()->file()->thumbnail($localFilename, $localThumbnailWm, Jotta::THUMBNAIL_SIZE_MEDIUM);
        $this->assertTrue(file_exists($localThumbnailWm));
        $image = Image::load($localThumbnailWm);
        $this->assertSame(240, $image->getWidth());
        $this->assertSame(240 * 6 / 8, $image->getHeight());
        $this->assertSame('image/jpeg', mime_content_type($localThumbnailWm));

        // 3. Thumbnail size large (WL)
        $localThumbnailWl = str_replace('.jpg', '_wl.jpg', $localPath);
        $this->jotta()->file()->thumbnail($localFilename, $localThumbnailWl, Jotta::THUMBNAIL_SIZE_LARGE);
        $this->assertTrue(file_exists($localThumbnailWl));
        $image = Image::load($localThumbnailWl);
        $this->assertSame(560, $image->getWidth());
        $this->assertSame(560 * 6 / 8, $image->getHeight());
        $this->assertSame('image/jpeg', mime_content_type($localThumbnailWl));

        // 3. Thumbnail size extra large (WXL)
        $localThumbnailWxl = str_replace('.jpg', '_wxl.jpg', $localPath);
        $this->jotta()->file()->thumbnail($localFilename, $localThumbnailWxl, Jotta::THUMBNAIL_SIZE_EXTRA_LARGE);
        $this->assertTrue(file_exists($localThumbnailWxl));
        $image = Image::load($localThumbnailWxl);
        $this->assertSame(1024, $image->getWidth());
        $this->assertSame(1024 * 6 / 8, $image->getHeight());
        $this->assertSame('image/jpeg', mime_content_type($localThumbnailWxl));

        // 99. Tear down
        $this->addToTempList($localFilename, 'file');
        @unlink($localThumbnailWs);
        @unlink($localThumbnailWm);
        @unlink($localThumbnailWl);
        @unlink($localThumbnailWxl);
        @unlink($this->tempPath($localFilename));
    }
}
