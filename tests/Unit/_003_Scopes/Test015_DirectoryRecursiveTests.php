<?php

namespace Vegfund\Jotta\Tests;

use Vegfund\Jotta\Support\JFileInfo;

class Test015_DirectoryRecursiveTests extends JottaTestCase
{
    /**
     * @covers \Vegfund\Jotta\Client\Scopes\DirectoryScope::getDirContents
     *
     * @throws \ReflectionException
     * @throws \Vegfund\Jotta\Client\Exceptions\JottaException
     */
    public function test001_get_dir_contents()
    {
        $dirPath = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src');

        $directory = $this->jotta()->directory();
        $method = new \ReflectionMethod($directory, 'getDirContents');
        $method->setAccessible(true);

        $results = [];
        $method->invokeArgs($directory, [$dirPath, &$results]);

        $expected = [];
        $this->getExpectedDirContents($dirPath, $expected);

        $this->assertEqualsCanonicalizing($expected, $results);
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
