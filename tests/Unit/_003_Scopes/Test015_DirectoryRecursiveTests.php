<?php

namespace Vegfund\Jotta\Tests;

use Illuminate\Support\Str;
use Vegfund\Jotta\Client\Exceptions\JottaException;
use Vegfund\Jotta\Support\JFileInfo;
use Vegfund\Jotta\Tests\Support\DirectoryScopeExtended;

class Test015_DirectoryRecursiveTests extends JottaTestCase
{
    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::getDirContents
     */
    public function test001_get_dir_contents()
    {
        $dirPath = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src');

        $directory = new DirectoryScopeExtended();

        $results = [];
        $directory->getDirContents($dirPath, $results);

        $expected = [];
        $this->getExpectedDirContents($dirPath, $expected);

        $this->assertEqualsCanonicalizing($expected, $results);
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::upload
     */
    public function test003_upload_no_path_should_throw_exception()
    {
        $path = realpath(__DIR__.DIRECTORY_SEPARATOR.Str::random(24));
        $this->shouldThrowException(JottaException::class, function () use ($path) {
            $this->jotta()->folder()->upload($path);
        });
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::upload
     */
    public function test005_upload_no_folder_should_throw_exception()
    {
        $path = realpath(__FILE__);
        $this->shouldThrowException(JottaException::class, function () use ($path) {
            $this->jotta()->folder()->upload($path);
        });
    }

    /**
     * @param $localPath
     * @param array $expected
     */
    protected function getExpectedDirContents($localPath, &$expected = [])
    {
        $files = scandir($localPath);

        foreach ($files as $key => $value) {
            $path = realpath($localPath.DIRECTORY_SEPARATOR.$value);
            if (!is_dir($path)) {
                $expected[$localPath][] = (new JFileInfo($localPath.'/'.$value));
            } elseif ('.' !== $value && '..' !== $value) {
                $this->getExpectedDirContents($path, $expected);
            }
        }
    }
}
