<?php

namespace Vegfund\Jotta\Tests\Unit\_001_Architecture;

use Vegfund\Jotta\Support\JFileInfo;
use Vegfund\Jotta\Support\UploadReport;

class Test006_UploadReportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Vegfund\Jotta\Support\UploadReport::stop
     * @covers \Vegfund\Jotta\Support\UploadReport::getReport
     */
    public function test001_upload_report_time()
    {
        $report = new UploadReport();
        sleep(2);
        $report->stop();

        $report = $report->getReport();

        $this->assertIsArray($report);
        $this->assertIsFloat($report['duration']);
        $this->assertTrue($report['duration'] > 1 && $report['duration'] < 3);
    }

    /**
     * @covers \Vegfund\Jotta\Support\UploadReport::folderTroublesome
     * @covers \Vegfund\Jotta\Support\UploadReport::fileNoFolder
     * @covers  \Vegfund\Jotta\Support\UploadReport::stop
     */
    public function test003_upload_report_folders_troublesome()
    {
        $folders = [
            'folder1' => [
                'file1' => [
                    'path' => 'path/folder1/file1',
                    'size' => 2000
                ],
                'file2' => [
                    'path' => 'path/folder1/file2',
                    'size' => 3000
                ]
            ]
        ];

        $uploadReport = new UploadReport();

        foreach($folders as $path => $folder) {
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

    public function test005_upload_report_files_by_type()
    {

    }
}