<?php

namespace Vegfund\Jotta\Tests\Unit\_003_Scopes;

use Illuminate\Support\Str;
use Vegfund\Jotta\Support\JFileInfo;
use Vegfund\Jotta\Tests\JottaTestCase;

class Test013a_FileDownloadTest extends JottaTestCase
{
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
}