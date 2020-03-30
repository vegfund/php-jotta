<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Scopes;

use Exception;
use function in_array;
use Psr\Http\Message\ResponseInterface;
use Sabre\Xml\ParseException;
use Vegfund\Jotta\Client\Contracts\NamespaceContract;
use Vegfund\Jotta\Client\Exceptions\JottaException;
use Vegfund\Jotta\Client\Responses\Namespaces\Folder;
use Vegfund\Jotta\Client\Responses\Namespaces\MountPoint;
use Vegfund\Jotta\Jotta;
use Vegfund\Jotta\Support\JFileInfo;
use Vegfund\Jotta\Support\OperationReport;
use Vegfund\Jotta\Traits\DirectoryScopeConfig;

/**
 * Class DirectoryScope.
 */
class DirectoryScope extends Scope
{
    use DirectoryScopeConfig;

    const MODE_MOUNT_POINT = 1;

    const MODE_FOLDER = 2;

    /**
     * @var int
     */
    protected $mode;

    /**
     * @param $mode
     *
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
     * @throws JottaException
     *
     * @return mixed
     */
    public function all()
    {
        if ($this->mode !== self::MODE_MOUNT_POINT) {
            throw new JottaException('This is valid only for mount points.');
        }

        $device = $this->jottaClient->device()->get();

        return $device->getMountPoints();
    }

    /**
     * Get folder metadata.
     *
     * @param string $remotePath remote path
     * @param array  $except
     *
     * @throws Exception
     *
     * @return MountPoint|Folder
     */
    public function get($remotePath = '', $except = ['files', 'folders'])
    {
        // Prepare relative path.
        $normalizedPath = $this->normalizePathSegment($remotePath);

        $response = $this->request(
            $requestPath = $this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint, $normalizedPath)
        );

        $serialized = $this->serialize($response);

        if (null !== $serialized) {
            return $serialized->except($except);
        }

        return null;
    }

    /**
     * @param string $remotePath
     *
     * @throws Exception
     *
     * @return MountPoint|Folder
     */
    public function getWithContents($remotePath = '')
    {
        return $this->get($remotePath, []);
    }

    /**
     * Create a remote folder.
     *
     * @param string $remotePath remote path
     *
     * @throws Exception
     *
     * @return array|Folder|NamespaceContract|object|ResponseInterface|string
     */
    public function create($remotePath = null)
    {
        if ($this->mode === self::MODE_MOUNT_POINT || null === $remotePath) {
            $remotePath = $remotePath ?: $this->mountPoint;

            return $this->createMountPoint($remotePath);
        }
        // Prepare relative path.
        $normalizedPath = $this->normalizePathSegment($remotePath);

        // Prepare API path.
        $requestPath = $this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint, $normalizedPath, ['mkDir' => 'true']);

        $response = $this->request(
            $requestPath,
            'post'
        );

        return $this->serialize($response);
    }

    /**
     * @param $name
     *
     * @throws Exception
     *
     * @return array|object|ResponseInterface|string|NamespaceContract
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
     * @param string $remotePath
     *
     * @throws Exception
     *
     * @return array
     */
    public function list($remotePath = '')
    {
        $directory = $this->getWithContents($remotePath);

        $listing = [];

        foreach ($this->applyFilters($directory->getFolders()) as $folder) {
            $listing[$folder->getName()] = [];
        }

        foreach ($this->applyFilters($directory->getFiles()) as $file) {
            $listing[] = $file->getName();
        }

        return $listing;
    }

    /**
     * @param $localPath
     * @param string $remotePath
     * @param mixed  $overwriteMode
     *
     * @throws Exception
     * @throws JottaException
     *
     * @return OperationReport
     */
    public function upload($localPath, $remotePath = '', $overwriteMode = Jotta::FILE_OVERWRITE_NEVER)
    {
        if (!file_exists($localPath) || !is_dir($localPath)) {
            throw new JottaException('This is not a folder or it does not exist');
        }

        $report = new OperationReport();

        $contents = [];
        $this->getDirContents($localPath, $contents);

        foreach ($contents as $path => $files) {
            // Get path relative to script
            $relativePath = $this->normalizePathSegment($remotePath.'/'.$this->getRelativePath($path));

            $threwExceptions = $this->shouldThrowExceptions;
            $this->withoutExceptions();

            try {
                $folder = $this->get($relativePath);
            } catch (Exception $e) {
                $folder = null;
            }

            if (null !== $folder) {
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
                $fileRelativePath = $this->normalizePathSegment($remotePath.'/'.$this->getRelativePath($path));

                try {
                    $fileScope->upload($file->getRealPath(), $fileRelativePath, $overwriteMode);
                    $report->file($existed, $fileRelativePath, $overwriteMode);
                } catch (Exception $e) {
                    $report->fileTroublesome($fileRelativePath);
                }
            }
        }

        $report->stop();

        return $report;
    }

    /**
     * @param $remotePath
     * @param array $recursive
     * @param bool  $responseObjects
     *
     * @throws ParseException
     *
     * @return array
     */
    public function listRecursive($remotePath, $recursive = [], $responseObjects = false)
    {
        $folder = $this->getWithContents($remotePath);

        foreach ($folder->getFolders() as $childFolder) {
            if (is_array($childFolder)) {
                $childFolder = $childFolder['value'];
            }
            if (!$folder->isDeleted()) {
                if ([] !== ($subtree = $this->listRecursive($this->normalizePathSegment($remotePath).'/'.$this->normalizePathSegment($childFolder->name)))) {
                    $recursive[$childFolder->name] = $subtree;
                }
            }
        }

        foreach ($folder->getFiles() as $file) {
            if ($responseObjects) {
                $recursive[] = $file;
            } else {
                $recursive[] = $file->getName();
            }
        }

        asort($recursive);

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
     * @throws Exception
     *
     * @return array|NamespaceContract|object|ResponseInterface|string
     */
    public function move($pathFrom, $pathTo, $mountPointTo = null)
    {
        if ($this->mode !== self::MODE_FOLDER) {
            throw new JottaException('Not a folder mode.');
        }

        $folder = $this->get($pathFrom);
        if (!($folder instanceof Folder)) {
            throw new JottaException('This is not a remote folder.');
        }

        $mountPointTo = $mountPointTo ?: $this->mountPoint;

        $fullPathTo = $this->getPath(null, $this->device, $mountPointTo, $pathTo);
        if (0 !== strpos($fullPathTo, '/')) {
            $fullPathTo = '/'.$fullPathTo;
        }

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
     * @throws Exception
     *
     * @return array|NamespaceContract|object|ResponseInterface|string
     */
    public function rename($nameFrom, $nameTo)
    {
        return $this->move($nameFrom, $nameTo);
    }

    /**
     * @param $path
     *
     * @throws Exception
     * @throws JottaException
     *
     * @return array|NamespaceContract|object|ResponseInterface|string
     */
    public function delete($path = null)
    {
        if ($this->mode === self::MODE_MOUNT_POINT || null === $path) {
            return $this->deleteMountPoint();
        }

        $folder = $this->get($path);
        if ($folder->isDeleted()) {
            throw new JottaException('Deleting Trash items not supported.');
        }

        $requestPath = $this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint, $path, [
            'dlDir'  => 'true',
            'method' => 'post',
        ]);

        $response = $this->request($requestPath, 'post');

        return $this->serialize($response);
    }

    /**
     * @throws Exception
     *
     * @return array|object|ResponseInterface|string|NamespaceContract
     */
    public function deleteMountPoint()
    {
        $forbidden = [
            Jotta::MOUNT_POINT_ARCHIVE,
            Jotta::MOUNT_POINT_SHARED,
            Jotta::MOUNT_POINT_SYNC,
        ];

        if (in_array($this->mountPoint, $forbidden, true)) {
            throw new JottaException('The mount point '.$this->mountPoint.' cannot be deleted.');
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
            $path = realpath($localPath.DIRECTORY_SEPARATOR.$value);
            if (!is_dir($path)) {
                $results[$localPath][] = (new JFileInfo($localPath.'/'.$value));
            } elseif ('.' !== $value && '..' !== $value) {
                $this->getDirContents($path, $results);
            }
        }
    }
}
