<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Support;

use Vegfund\Jotta\Jotta;

/**
 * Class UploadReport.
 */
class UploadReport
{
    /**
     * @var array
     */
    protected $report = [
        'folders' => [
            'created'     => [],
            'restored'    => [],
            'existing'    => [],
            'troublesome' => [],
        ],
        'files' => [
            'uploaded_fresh'              => [],
            'uploaded_forcibly'           => [],
            'ignored'                     => [],
            'uploaded_newer'              => [],
            'uploaded_different'          => [],
            'uploaded_newer_or_different' => [],
            'no_folder'                   => [],
            'troublesome'                 => [],
        ],
        'sizes' => [
            'uploaded_fresh'              => 0,
            'uploaded_forcibly'           => 0,
            'ignored'                     => 0,
            'uploaded_newer'              => 0,
            'uploaded_different'          => 0,
            'uploaded_newer_or_different' => 0,
            'no_folder'                   => 0,
            'troublesome'                 => 0,
        ],
        'duration' => [],
    ];

    /**
     * @var float
     */
    protected $start;

    /**
     * @var float
     */
    protected $end;

    /**
     * UploadReport constructor.
     */
    public function __construct()
    {
        $this->start = microtime(true);
    }

    /**
     * @param $path
     */
    public function folderExisting($path)
    {
        $this->report['folders']['existing'][] = $relativePath;
    }

    /**
     * @param $path
     */
    public function folderCreated($path)
    {
        $this->report['folders']['created'][] = $relativePath;
    }

    /**
     * @param $path
     * @param array $files
     */
    public function folderTroublesome($path, $files = [])
    {
        $this->report['folders']['troublesome'][] = $relativePath;
        $this->fileNoFolder($files);
    }

    /**
     * @param $files
     */
    public function fileNoFolder($files)
    {
        $files = is_array($files) ? $files : [$files];
        $this->report['files']['no_folder'] = array_merge($this->report['files']['no_folder'], $files);
    }

    /**
     * @param $file
     */
    public function fileFresh($file)
    {
        $this->report['files']['uploaded_fresh'][] = $file;
    }

    /**
     * @param $existed
     * @param $file
     * @param $overwriteMode
     */
    public function file($existed, $file, $overwriteMode)
    {
        $mapping = [
            Jotta::FILE_OVERWRITE_ALWAYS                => 'uploaded_forcibly',
            Jotta::FILE_OVERWRITE_NEVER                 => 'ignored',
            Jotta::FILE_OVERWRITE_IF_DIFFERENT          => 'uploaded_different',
            Jotta::FILE_OVERWRITE_IF_NEWER              => 'uploaded_newer',
            Jotta::FILE_OVERWRITE_IF_NEWER_OR_DIFFERENT => 'uploaded_newer_or_different',
        ];

        if (!$existed) {
            $this->report['files']['uploaded_fresh'][] = $file;
        } else {
            $this->report['files'][$mapping[$overwriteMode]][] = $file;
        }
    }

    /**
     * @param $file
     */
    public function fileTroublesome($file)
    {
        $this->report['files']['troublesome'][] = $file;
    }

    public function stop()
    {
        $this->end = microtime(true);
        $this->report['duration'] = $this->end - $this->start;
    }

    /**
     * @return array
     */
    public function getReport()
    {
        $report = array_map(function ($item) {
            if (is_string($item)) {
                return new JFileInfo($item);
            }

            if ($item instanceof \SplFileInfo) {
                return new JFileInfo($item->getRealPath());
            }

            return $item;
        }, $this->report);

        foreach ($report['files'] as $scope => $scopeFiles) {
            foreach ($scopeFiles as $key => $scopeFile) {
                $report['sizes'][$scope][$key] += $scopeFiles->getSize();
            }
        }

        return $report;
    }
}
