<?php

namespace Vegfund\Jotta\Tests\Unit\_003_Scopes;

use Illuminate\Support\Str;
use Vegfund\Jotta\Client\Responses\Namespaces\File;
use Vegfund\Jotta\Client\Responses\Namespaces\Folder;
use Vegfund\Jotta\Tests\JottaTestCase;

class Test013b_FileUploadTest extends JottaTestCase
{
    /**
     * @covers \Vegfund\Jotta\Client\Scopes\FileScope::upload
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

        $this->addToTempList($remoteFolder.'/'.$localFile, 'file');
        $this->addToTempList($remoteFolder, 'folder');
        @unlink($localPath);
    }
}