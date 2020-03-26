<?php

namespace Vegfund\Jotta\Tests;

use PHPUnit\Framework\TestCase;
use Vegfund\Jotta\Client\Exceptions\JottaException;
use Vegfund\Jotta\Jotta;
use Vegfund\Jotta\Tests\Support\AssertExceptions;
use Vegfund\Jotta\Tests\Support\JottaTestTrait;

/**
 * Class JottaTestCase.
 */
class JottaTestCase extends TestCase
{
    use AssertExceptions;
    use JottaTestTrait;

    public function setUp(): void
    {
        $f = fopen(__DIR__.DIRECTORY_SEPARATOR.'temp.json', 'w');
        fwrite($f, json_encode([]));
        fclose($f);
        parent::setUp(); // TODO: Change the autogenerated stub
    }

    public function tearDown(): void
    {
        $jsonList = json_decode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'temp.json'), true);
        $client = $this->jotta();

        try {
            foreach ($jsonList as $item) {
                if ($item['type'] === 'folder') {
                    $client->folder()->setMountPoint($item['mount_point'])->delete($item['path']);
                }

                if ($item['type'] === 'file') {
                    $client->file()->setMountPoint($item['mount_point'])->delete($item['path']);
                }
            }
        } catch (JottaException $e) {}
        catch (\Exception $e) {}

        @unlink(__DIR__.DIRECTORY_SEPARATOR.'temp.json');

        parent::tearDown(); // TODO: Change the autogenerated stub
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    protected function tempPath($filename = '')
    {
        return __DIR__.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$filename;
    }

    /**
     * @param $path
     * @param $mountPoint
     */
    protected function addToTempList($path, $type, $mountPoint = Jotta::MOUNT_POINT_ARCHIVE)
    {
        $jsonList = json_decode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'temp.json'), true);
        $jsonList[] = [
            'path' => $path,
            'type' => $type,
            'mount_point' => $mountPoint
        ];

        $f = fopen(__DIR__.DIRECTORY_SEPARATOR.'temp.json', 'w');
        fwrite($f, json_encode($jsonList, JSON_PRETTY_PRINT));
        fclose($f);
    }
}
