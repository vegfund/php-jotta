<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Scopes;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Sabre\Xml\ParseException;
use Vegfund\Jotta\Client\Contracts\NamespaceContract;
use Vegfund\Jotta\Client\Exceptions\JottaException;
use Vegfund\Jotta\Client\Resources\FileResource;
use Vegfund\Jotta\Client\Resources\FolderListingResource;
use Vegfund\Jotta\Client\Responses\Namespaces\Folder;
use Vegfund\Jotta\Jotta;
use Vegfund\Jotta\Support\JFileInfo;
use Vegfund\Jotta\Support\UploadReport;
use function in_array;

/**
 * Class DirectoryScope
 * @package Vegfund\Jotta\Client\Scopes
 */
class DirectoryScope extends Scope
{
    const MODE_MOUNT_POINT = 1;

    const MODE_FOLDER = 2;

    /**
     * @var int
     */
    protected $mode;

    /**
     * @param $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return mixed
     * @throws JottaException
     * @throws ParseException
     */
    public function all()
    {
        if($this->mode !== self::MODE_MOUNT_POINT) {
            throw new JottaException('This is valid only for mount points.');
        }
        /**
         * @var Device
         */
        $device = $this->jottaClient->device()->get();

        return $device->getMountPoints();
    }

    /**
     * Get folder metadata.
     *
     * @param string $remotePath remote path
     * @param null|string $remoteName remote name (if name is not specified, then only remote path will be used)
     *
     * @return array|Folder|NamespaceContract|object|ResponseInterface|string
     * @throws Exception
     */
    public function get($remotePath = '', $remoteName = null)
    {
        // Prepare relative path.
        $normalizedPath = $this->normalizePathSegment($remotePath);
        if (null !== $remoteName) {
            $normalizedPath .= DIRECTORY_SEPARATOR.$this->normalizePathSegment($remoteName);
        }

        $response = $this->request(
            $requestPath = $this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint, $normalizedPath)
        );

        return $this->serialize($response);
    }

    /**
     * Create a remote folder.
     *
     * @param string $remotePath remote path
     * @param null|string $remoteName remote name (if name is not specified, then only remote path will be used)
     *
     * @return array|Folder|NamespaceContract|object|ResponseInterface|string
     * @throws Exception
     */
    public function create($remotePath, $remoteName = null)
    {
        if($this->mode === self::MODE_MOUNT_POINT) {
            return $this->createMountPoint($remotePath);
        }
        // Prepare relative path.
        $normalizedPath = $this->normalizePathSegment($remotePath);
        if (null !== $remoteName) {
            $normalizedPath .= DIRECTORY_SEPARATOR.$this->normalizePathSegment($remoteName);
        }

        // Prepare API path.
        $requestPath = $this->getPath(Jotta::API_UPLOAD_URL, $this->device, $this->mountPoint, $normalizedPath, ['mkdir' => true]);

        $response = $this->getClient()->request(
            $requestPath,
            'post',
            [
                'JMd5'  => md5(''),
                'JSize' => 0,
            ]
        );

        return $this->serialize($response);
    }

    /**
     * @param $name
     * @return array|object|ResponseInterface|string|NamespaceContract
     * @throws Exception
     */
    protected function createMountPoint($name)
    {
        // Prepare API path.
        $requestPath = $this->getPath(Jotta::API_UPLOAD_URL, $this->device, $name);

        $response = $this->getClient()->request(
            $requestPath,
            'post',
            [
                'JMd5'  => md5(''),
                'JSize' => 0,
            ]
        );

        return $this->serialize($response);
    }

    /**
     * @param $remotePath
     * @param null $remoteName
     * @param array $options
     *
     * @return array
     * @throws Exception
     */
    public function list($remotePath, $remoteName = null, $options = [])
    {
        $metadata = $this->get($remotePath, $remoteName);

        return [implode('/', [$metadata->abspath, $metadata->name]) => (new FolderListingResource($metadata))->toArray()];
    }

    /**
     * @param $localPath
     * @param string $remotePath
     * @param mixed  $overwriteMode
     *
     * @throws ParseException
     * @throws JottaException
     *
     * @return UploadReport
     */
    public function upload($localPath, $remotePath, $overwriteMode = Jotta::FILE_OVERWRITE_NEVER)
    {
        if (!file_exists($localPath) || !is_dir($localPath)) {
            throw new JottaException('This is not a folder or it does not exist');
        }

        $report = new UploadReport();

        $contents = [];
        $this->getDirContents($localPath, $contents);

        foreach ($contents as $path => $files) {
            // Get path relative to script
            $relativePath = $this->getRelativePath($path);

            $threwExceptions = $this->shouldThrowExceptions;
            $this->withoutExceptions();

            if (null !== ($folder = $this->get($relativePath))) {
                $report->folderExisting($relativePath);
            } else {
                $this->create($relativePath);
                if (null !== ($folder = $this->get($relativePath))) {
                    $report->folderCreated($relativePath);
                } else {
                    $report->folderTroublesome($relativePath, $files);
                    continue;
                }
            }

            if ($threwExceptions) {
                $this->withExceptions();
            }

            $fileScope = $this->getClient()->file([
                'device'      => $this->device,
                'mount_point' => $this->mountPoint,
                'base_path'   => $remotePath,
            ])->withoutExceptions();

            /*
             * ADD FILES.
             *
             * @var \SplFileInfo
             */
            foreach ($files as $file) {
                $fileRelativePath = $this->getRelativePath($file->getRealPath());
                $existed = null !== $fileScope->get($fileRelativePath);

                $remoteFile = $fileScope->upload($file->getRealPath(), $fileRelativePath, $overwriteMode);

                if (null !== $remoteFile && $remoteFile->isSameAs($file->getRealPath())) {
                    $report->file($existed, $fileRelativePath, $overwriteMode);
                } else {
                    $report->fileTroublesome($fileRelativePath);
                }
            }
        }

        $report->stop();

        return $report;
    }

    /**
     * @param $remotePath
     * @param array $options
     * @param array $recursive
     *
     * @throws ParseException
     *
     * @return array
     */
    public function listRecursive($remotePath, $options = [], $recursive = [])
    {
        $folder = $this->get($remotePath);

        if (is_array($folder->getFolders()) && count($folder->getFolders()) > 0) {
            foreach ($folder->getFolders() as $childFolder) {
                if (is_array($childFolder)) {
                    $childFolder = $childFolder['value'];
                }
                if (!isset($childFolder->getAttributes()->deleted)) {
                    if ([] !== ($subtree = $this->listRecursive($this->normalizePathSegment($remotePath).'/'.$this->normalizePathSegment($childFolder->name), $options, $recursive))) {
                        $recursive[$childFolder->name] = $subtree;
                    }
                }
            }
        }

        if (is_array($folder->getFiles()) && count($folder->getFiles()) > 0) {
            foreach ($folder->getFiles() as $file) {
                if (isset($options['uuid']) && $file->uuid !== $options['uuid']) {
                    continue;
                }

                if (isset($options['with_deleted']) && true === $options['with_deleted'] && $file->isDeleted()) {
                    continue;
                }

                if (isset($options['with_completed']) && false === $options['with_completed'] && $file->isCompleted()) {
                    continue;
                }

                if (isset($options['regex']) && 0 === preg_match($options['regex'], $file->name)) {
                    continue;
                }

                $recursive[] = (new FileResource($file))->toArray();
            }
        }

        return $recursive;
    }

    /**
     * @param $pathFrom
     * @param $pathTo
     */
    public function copy($pathFrom, $pathTo)
    {
    }

    /**
     * @param $pathFrom
     * @param $pathTo
     * @param null $mountPointTo
     *
     * @return array|NamespaceContract|object|ResponseInterface|string
     * @throws Exception
     */
    public function move($pathFrom, $pathTo, $mountPointTo = null)
    {
        $mountPointTo = $mountPointTo ?: $this->mountPoint;

        $fullPathTo = $this->getPath(null, $this->device, $mountPointTo, $pathTo);
        $requestPath = $this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint, $pathFrom, [
            'mvDir' => $fullPathTo,
        ]);

        $response = $this->request($requestPath, 'post');

        return $this->serialize($response);
    }

    /**
     * @param $nameFrom
     * @param $nameTo
     *
     * @return array|NamespaceContract|object|ResponseInterface|string
     * @throws Exception
     */
    public function rename($nameFrom, $nameTo)
    {
        return $this->move($nameFrom, $nameTo);
    }

    /**
     * @param $path
     *
     * @return array|NamespaceContract|object|ResponseInterface|string
     * @throws JottaException
     */
    public function delete($path = null)
    {
        if($this->mode === self::MODE_MOUNT_POINT) {
            return $this->deleteMountPoint();
        }

        $folder = $this->get($path);
        if ($folder->isDeleted()) {
            throw new JottaException('Deleting Trash items not supported.');
        }

        $requestPath = $this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint, $path, [
            'dlDir' => true,
        ]);

        $response = $this->request($requestPath, 'post');

        return $this->serialize($response);
    }

    /**
     * @return array|object|ResponseInterface|string|NamespaceContract
     * @throws Exception
     */
    public function deleteMountPoint()
    {
        $forbidden = [
            Jotta::MOUNT_POINT_ARCHIVE,
            Jotta::MOUNT_POINT_SHARED,
            Jotta::MOUNT_POINT_SYNC,
        ];

        if (in_array($this->mountPoint, $forbidden, true)) {
            throw new Exception('The mount point '.$this->mountPoint.' cannot be deleted.');
        }

        $response = $this->request(
            $this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint, null, ['rm' => 'true']),
            'post'
        );

        return $this->serialize($response);
    }

    /**
     * Get the local folder contents.
     *
     * @param string $localPath path for the local directory
     * @param array  $results   results array
     */
    protected function getDirContents($localPath, &$results = [])
    {
        $files = scandir($localPath);

        foreach ($files as $key => $value) {
            if (!isset($results[$localPath])) {
                $results[$localPath] = [];
            }

            $path = realpath($localPath.DIRECTORY_SEPARATOR.$value);
            if (!is_dir($path)) {
                $results[$localPath][] = (new JFileInfo($localPath.'/'.$value));
            } elseif ('.' !== $value && '..' !== $value) {
                $this->getDirContents($path, $results);
            }
        }
    }
}