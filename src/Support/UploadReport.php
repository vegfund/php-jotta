<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Support;

class UploadReport
{
    protected $report;
    protected $start;
    protected $end;

    public function __construct()
    {
        $this->start = microtime(true);

        $this->report = [
            'folders' => [
                'created'     => [],
                'restored'    => [],
                'existing'    => [],
                'troublesome' => [],
            ],
            'files' => [
                'uploaded_fresh'     => [],
                'uploaded_forcibly'  => [],
                'ignored'            => [],
                'uploaded_newer'     => [],
                'uploaded_different' => [],
                'no_folder'          => [],
                'troublesome'        => [],
            ],
            'sizes' => [
                'uploaded_fresh'     => 0,
                'uploaded_forcibly'  => 0,
                'ignored'            => 0,
                'uploaded_newer'     => 0,
                'uploaded_different' => 0,
                'no_folder'          => 0,
                'troublesome'        => 0,
            ],
            'metadata' => [],
        ];
    }

    public function folderExisting($path)
    {
        $this->report['folders']['existing'][] = $relativePath;
    }

    public function folderCreated($path)
    {
        $this->report['folders']['created'][] = $relativePath;
    }

    public function folderTroublesome($path, $files = [])
    {
        $this->report['folders']['troublesome'][] = $relativePath;
        $this->fileNoFolder($files);
    }

    public function fileNoFolder($files)
    {
        $files = is_array($files) ? $files : [$files];
        $this->report['files']['no_folder'] = array_merge($this->report['files']['no_folder'], $files);
    }

    public function fileFresh($file)
    {
        $this->report['files']['uploaded_fresh'][] = $file;
    }

    public function file($existed, $file, $overwriteMode)
    {
    }

    public function fileTroublesome($file)
    {
    }

    public function stop()
    {
        $this->end = microtime(true);
    }
}
