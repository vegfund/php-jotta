<?php

namespace Vegfund\Jotta\Tests\Unit\_001_Architecture;

use Illuminate\Support\Str;
use Vegfund\Jotta\Jotta;
use Vegfund\Jotta\Support\JFileInfo;
use Vegfund\Jotta\Support\OperationReport;
use Vegfund\Jotta\Tests\JottaTestCase;

class Test006_UploadReportTest extends JottaTestCase
{
    /**
     * @covers \Vegfund\Jotta\Support\OperationReport::__construct
     * @covers \Vegfund\Jotta\Support\OperationReport::stop
     * @covers \Vegfund\Jotta\Support\OperationReport::getReport
     */
    public function test001_upload_report_time()
    {
        $report = new OperationReport();

        $reflection = new \ReflectionClass($report);
        $property = $reflection->getProperty('start');
        $property->setAccessible(true);

        $this->assertTrue($property->getValue($report) < microtime(true));

        sleep(2);
        $report->stop();

        $report = $report->getReport();

        $this->assertIsArray($report);
        $this->assertIsFloat($report['duration']);
        $this->assertTrue($report['duration'] > 1 && $report['duration'] < 3);
    }

    /**
     * @covers \Vegfund\Jotta\Support\OperationReport::folderTroublesome
     * @covers \Vegfund\Jotta\Support\OperationReport::fileNoFolder
     * @covers  \Vegfund\Jotta\Support\OperationReport::stop
     */
    public function test003_upload_report_folders_troublesome()
    {
        $folders = [
            'folder1' => [
                'file1' => [
                    'path' => 'path/folder1/file1',
                    'size' => 2000,
                ],
                'file2' => [
                    'path' => 'path/folder1/file2',
                    'size' => 3000,
                ],
            ],
        ];

        $uploadReport = new OperationReport();

        foreach ($folders as $path => $folder) {
            $files = array_map(function ($item) {
                $mock = \Mockery::mock(JFileInfo::class);
                $mock->shouldReceive('getSize')->andReturn($item['size']);
                $mock->shouldReceive('getRealPath')->andReturn($item['path']);

                return $mock;
            }, $folder);

            $uploadReport->folderTroublesome($path, $files);
        }

        $uploadReport->stop();
        $report = $uploadReport->getReport();

        $this->assertIsArray($report['folders']['troublesome']);
        $this->assertCount(1, $report['folders']['troublesome']);

        $this->assertIsArray($report['files']['no_folder']);
        $this->assertCount(2, $report['files']['no_folder']);

        $this->assertSame(5000, $report['sizes']['no_folder']);
    }

    /**
     * @covers \Vegfund\Jotta\Support\OperationReport::folderCreated
     * @covers \Vegfund\Jotta\Support\OperationReport::folderExisting
     */
    public function test003_upload_report_folders_by_types()
    {
        $folders = [
            'path/folder1' => 'existing',
            'path/folder2' => 'existing',
            'path/folder3' => 'existing',
            'path/folder4' => 'created',
            'path/folder5' => 'created',
        ];

        $uploadReport = new OperationReport();

        foreach ($folders as $path => $type) {
            $funcName = 'folder'.ucfirst($type);
            $uploadReport->{$funcName}($path);
        }

        $uploadReport->stop();
        $report = $uploadReport->getReport();

        $this->assertCount(3, $report['folders']['existing']);
        $this->assertCount(2, $report['folders']['created']);
    }

    /**
     * @covers \Vegfund\Jotta\Support\OperationReport::file
     */
    public function test005_upload_report_files_overwrite()
    {
        $overwriteTypes = [
            Jotta::FILE_OVERWRITE_IF_NEWER_OR_DIFFERENT => 'uploaded_newer_or_different',
            Jotta::FILE_OVERWRITE_IF_NEWER              => 'uploaded_newer',
            Jotta::FILE_OVERWRITE_IF_DIFFERENT          => 'uploaded_different',
            Jotta::FILE_OVERWRITE_NEVER                 => 'ignored',
            Jotta::FILE_OVERWRITE_ALWAYS                => 'uploaded_forcibly',
        ];

        $overwriteTypesKeys = array_keys($overwriteTypes);

        $generatedFiles = [];

        $uploadReport = new OperationReport();

        $expectedSizes = [
            'uploaded_newer_or_different' => 0,
            'uploaded_newer'              => 0,
            'uploaded_different'          => 0,
            'ignored'                     => 0,
            'uploaded_forcibly'           => 0,
        ];

        for ($i = 0; $i < 80; $i++) {
            $mock = \Mockery::mock(JFileInfo::class);
            $size = rand(1, 999999);
            $path = 'path/to/'.Str::random(12).'txt';
            $mock->shouldReceive('getSize')->andReturn($size);
            $mock->shouldReceive('getRealPath')->andReturn($path);

            $overwriteType = $overwriteTypesKeys[array_rand($overwriteTypesKeys)];

            $generatedFiles[] = [
                'mock'           => $mock,
                'size'           => $size,
                'path'           => $path,
                'overwrite_type' => $overwriteTypes[$overwriteType],
            ];

            $expectedSizes[$overwriteTypes[$overwriteType]] += $size;

            $uploadReport->file(true, $mock, $overwriteType);
        }

        $uploadReport->stop();
        $report = $uploadReport->getReport();

        foreach ($expectedSizes as $scope => $size) {
            $this->assertSame($size, $report['sizes'][$scope]);
        }
    }

    /**
     * @covers \Vegfund\Jotta\Support\OperationReport::fileFresh
     */
    public function test007_upload_files_fresh()
    {
        $expectedSize = 0;
        $expectedCount = rand(50, 100);

        $uploadReport = new OperationReport();

        for ($i = 0; $i < $expectedCount; $i++) {
            $mock = \Mockery::mock(JFileInfo::class);
            $size = rand(1, 999999);
            $path = 'path/to/'.Str::random(12).'txt';
            $mock->shouldReceive('getSize')->andReturn($size);
            $mock->shouldReceive('getRealPath')->andReturn($path);

            $expectedSize += $size;

            $uploadReport->fileFresh($mock);
        }

        $uploadReport->stop();
        $report = $uploadReport->getReport();

        $this->assertSame($expectedCount, count($report['files']['uploaded_fresh']));
        $this->assertSame($expectedSize, $report['sizes']['uploaded_fresh']);
    }

    /**
     * @covers \Vegfund\Jotta\Support\OperationReport::file
     */
    public function test007a_upload_files_fresh_variant()
    {
        $expectedSize = 0;
        $expectedCount = rand(50, 100);

        $uploadReport = new OperationReport();

        for ($i = 0; $i < $expectedCount; $i++) {
            $mock = \Mockery::mock(JFileInfo::class);
            $size = rand(1, 999999);
            $path = 'path/to/'.Str::random(12).'txt';
            $mock->shouldReceive('getSize')->andReturn($size);
            $mock->shouldReceive('getRealPath')->andReturn($path);

            $expectedSize += $size;

            $uploadReport->file(false, $mock, Jotta::FILE_OVERWRITE_ALWAYS);
        }

        $uploadReport->stop();
        $report = $uploadReport->getReport();

        $this->assertSame($expectedCount, count($report['files']['uploaded_fresh']));
        $this->assertSame($expectedSize, $report['sizes']['uploaded_fresh']);
    }

    /**
     * @covers \Vegfund\Jotta\Support\OperationReport::fileTroublesome
     */
    public function test007_upload_files_troublesome()
    {
        $expectedSize = 0;
        $expectedCount = rand(50, 100);

        $uploadReport = new OperationReport();

        for ($i = 0; $i < $expectedCount; $i++) {
            $mock = \Mockery::mock(JFileInfo::class);
            $size = rand(1, 999999);
            $path = 'path/to/'.Str::random(12).'txt';
            $mock->shouldReceive('getSize')->andReturn($size);
            $mock->shouldReceive('getRealPath')->andReturn($path);

            $expectedSize += $size;

            $uploadReport->fileTroublesome($mock);
        }

        $uploadReport->stop();
        $report = $uploadReport->getReport();

        $this->assertSame($expectedCount, count($report['files']['troublesome']));
        $this->assertSame($expectedSize, $report['sizes']['troublesome']);
    }
}
