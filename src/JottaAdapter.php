<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta;

use Exception;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Config;
use Sabre\Xml\ParseException;
use Vegfund\Jotta\Client\Exceptions\JottaException;
use Vegfund\Jotta\Traits\PathTrait;

/**
 * Class JottaAdapter.
 */
class JottaAdapter extends AbstractAdapter
{
    use PathTrait;

    /**
     * @var JottaClient
     */
    protected $client;

    /**
     * JottaAdapter constructor.
     * @param JottaClient $client
     */
    public function __construct(JottaClient $client)
    {
        $this->client = $client;
    }

    /**
     * Write a new file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config   Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function write($path, $contents, Config $config)
    {
        // TODO: Implement write() method.
    }

    /**
     * Write a new file using a stream.
     *
     * @param string   $path
     * @param resource $resource
     * @param Config   $config   Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function writeStream($path, $resource, Config $config)
    {
        // TODO: Implement writeStream() method.
    }

    /**
     * Update a file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config   Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function update($path, $contents, Config $config)
    {
        // TODO: Implement update() method.
    }

    /**
     * Update a file using a stream.
     *
     * @param string   $path
     * @param resource $resource
     * @param Config   $config   Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function updateStream($path, $resource, Config $config)
    {
        // TODO: Implement updateStream() method.
    }

    /**
     * Rename a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function rename($path, $newpath)
    {
        $mountPointFrom = $this->getMountPointFromPath($path);
        $mountPointTo = $this->getMountPointFromPath($newpath);
        $pathFrom = $this->stripMountPointFromPath($path);
        $pathTo = $this->stripMountPointFromPath($newpath);

        try {
            $this->client->file()->setMountPoint($mountPointFrom)->rename($pathFrom, $pathTo, $mountPointTo);
            return true;
        } catch (JottaException $e) {} catch (Exception $e) {}

        return false;
    }

    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function copy($path, $newpath)
    {
        // TODO: Implement copy() method.
    }

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete($path)
    {
        // TODO: Implement delete() method.
    }

    /**
     * Delete a directory.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function deleteDir($dirname)
    {
        // TODO: Implement deleteDir() method.
    }

    /**
     * Create a directory.
     *
     * @param string $dirname directory name
     *
     * @return array|false
     */
    public function createDir($dirname, Config $config)
    {
        $mountPoint = $this->getMountPointFromPath($dirname);
        $path = $this->stripMountPointFromPath($dirname);

        try {
            $folder = $this->client->directory()->setMountPoint($mountPoint)->create($path);
            return ['path' => $dirname, 'type' => 'dir'];
        } catch (JottaException $e) {} catch (Exception $e) {}

        return false;
    }

    /**
     * Set the visibility for a file.
     *
     * @param string $path
     * @param string $visibility
     *
     * @return array|false file meta data
     */
    public function setVisibility($path, $visibility)
    {
        // TODO: Implement setVisibility() method.
    }

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return bool
     */
    public function has($path)
    {
        $mountPoint = $this->getMountPointFromPath($path);
        $path = $this->stripMountPointFromPath($path);

        try {
            return null !== $this->client->file()->setMountPoint($mountPoint)->verify($path);
        } catch (JottaException $e) {} catch (Exception $e) {}

        return false;
    }

    /**
     * Read a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function read($path)
    {
        // TODO: Implement read() method.
    }

    /**
     * Read a file as a stream.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function readStream($path)
    {
        // TODO: Implement readStream() method.
    }

    /**
     * List contents of a directory.
     *
     * @param string $directory
     * @param bool $recursive
     *
     * @return array
     * @throws JottaException
     * @throws ParseException
     * @throws Exception
     */
    public function listContents($directory = '', $recursive = false)
    {
        $mountPoint = $this->getMountPointFromPath($directory);
        $path = $this->stripMountPointFromPath($directory);

        if($path === '') {
            $scope = $this->client->mountPoint()->setMountPoint($mountPoint);
        } else {
            $scope = $this->client->folder()->setMountPoint($mountPoint);
        }

        if ($recursive) {
            return $scope->listRecursive($path);
        } else {
            return $scope->list($path);
        }
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMetadata($path)
    {
        // TODO: Implement getMetadata() method.
    }

    /**
     * Get the size of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getSize($path)
    {
        $mountPoint = $this->getMountPointFromPath($directory);
        $path = $this->stripMountPointFromPath($directory);

        try {
            return ['size' => $this->client->file()->setMountPoint($mountPoint)->get($path)->getSize()];
        } catch (JottaException $e) {} catch (Exception $e) {}

        return false;
    }

    /**
     * Get the mimetype of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMimetype($path)
    {
        $mountPoint = $this->getMountPointFromPath($directory);
        $path = $this->stripMountPointFromPath($directory);

        try {
            return ['mimetype' => $this->client->file()->setMountPoint($mountPoint)->get($path)->getMime()];
        } catch (JottaException $e) {} catch (Exception $e) {}

        return false;
    }

    /**
     * Get the last modified time of a file as a timestamp.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getTimestamp($path)
    {
        $mountPoint = $this->getMountPointFromPath($directory);
        $path = $this->stripMountPointFromPath($directory);

        try {
            return ['timestamp' => $this->client->file()->setMountPoint($mountPoint)->get($path)->getModified()->toTimestamp()];
        } catch (JottaException $e) {} catch (Exception $e) {}

        return false;
    }

    /**
     * Get the visibility of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getVisibility($path)
    {
        // TODO: Implement getVisibility() method.
    }
}
