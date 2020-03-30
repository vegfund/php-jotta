<?php

namespace Vegfund\Jotta\Tests;

use Illuminate\Support\Str;
use Vegfund\Jotta\Client\Exceptions\JottaException;
use Vegfund\Jotta\Client\Responses\Namespaces\Folder;
use Vegfund\Jotta\Support\JFileInfo;
use Vegfund\Jotta\Tests\Support\DirectoryScopeExtended;

class Test015b_FolderRecursiveUploadTest extends JottaTestCase
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
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::upload
     *
     * @throws JottaException
     */
    public function test007_upload_folder_recursive_src()
    {
        // 1. Create remote folder for storing data
        $localPath = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src');
        $folderName = Str::random(12).'_test007_upload_rec';

        $remoteFolder = $this->jotta()->folder()->create($folderName);
        $this->assertInstanceOf(Folder::class, $remoteFolder);

        // 2. Try uploading the src folder contents
        $result = $this->jotta()->folder()->upload($localPath, $folderName);

        // 3. Start asserting
        $expected = [];
        $this->getExpectedDirContents($localPath, $expected);
        foreach ($expected as $expectedPath => $expectedFolder) {
            $expectedPath = $folderName.'/'.basename($localPath).str_replace($localPath, '', $expectedPath);

            // 1. Assert folder exists
            $folder = $this->jotta()->folder()->get($expectedPath);
            $this->assertInstanceOf(Folder::class, $folder);

            $filesCount = 0;

            // 2. Assert folder has all files
            foreach ($expectedFolder as $item) {
                if ($item instanceof JFileInfo || $item instanceof \SplFileInfo) {
                    $expectedFilePath = $expectedPath.'/'.$item->getFilename();
                    $this->assertTrue($this->jotta()->file()->verify($expectedFilePath, $item->getRealPath()));

                    $filesCount++;
                }
            }

            $files = $this->jotta()->folder()->getWithContents($expectedPath)->getFiles();
            $this->assertSame($filesCount, count($files));
        }
    }

    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::listRecursive
     *
     * @throws JottaException
     * @throws \Sabre\Xml\ParseException
     */
    public function test009_list_recursive()
    {
        // 1. First, find folder from the previous test
        $folders = $this->jotta()->mountPoint()->regex('/_test007_upload_rec$/')->list();
        $this->assertTrue(count($folders) >= 1);

        $localPath = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src');
        $localTree = [basename($localPath) => $this->getExpectedDirTree($localPath)];

        $rootFolderName = array_keys($folders)[0];

        $listRecursive = $this->jotta()->folder()->listRecursive($rootFolderName);
        $this->assertSame($localTree, $listRecursive);

        // 2. Tear down
        $this->addToTempList($rootFolderName, 'folder');
    }

    /**
     * @param $localPath
     * @param array $tree
     *
     * @return array
     */
    protected function getExpectedDirTree($localPath, $tree = [])
    {
        $items = scandir($localPath);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            if (is_dir($localPath.DIRECTORY_SEPARATOR.$item)) {
                $tree[$item] = $this->getExpectedDirTree($localPath.DIRECTORY_SEPARATOR.$item);
            }

            if (is_file($localPath.DIRECTORY_SEPARATOR.$item)) {
                $tree[] = $item;
            }
        }

        asort($tree);

        return $tree;
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
