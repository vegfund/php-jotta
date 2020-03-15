<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Scopes;

use const DIRECTORY_SEPARATOR;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Sabre\Xml\ParseException;
use Vegfund\Jotta\Client\Contracts\NamespaceContract;
use Vegfund\Jotta\Client\Exceptions\FileNotUploadedException;
use Vegfund\Jotta\Client\Responses\Namespaces\File;
use Vegfund\Jotta\Jotta;

class FileScope extends Scope
{
    /**
     * @param $remotePath
     * @param null $remoteName
     *
     * @throws ParseException
     *
     * @return array|NamespaceContract|object|ResponseInterface|string
     */
    public function get($remotePath, $remoteName = null)
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
     * @param $remotePath
     * @param null $localPath
     *
     * @throws ParseException
     *
     * @return bool
     */
    public function verify($remotePath, $localPath = null)
    {
        $file = $this->get($remotePath);

        return null !== $file && null !== $file->currentRevision && $file->isValid() && (null === $localPath || null !== $localPath && md5(file_get_contents($localPath)) === $file->currentRevision->md5);
    }

    /**
     * @param $remotePath
     * @param $localPath
     * @param int $overwriteMode
     *
     * @throws Exception
     *
     * @return array|object|ResponseInterface|string|NamespaceContract|bool||null|File
     */
    public function download($remotePath, $localPath, $overwriteMode = Jotta::FILE_OVERWRITE_NEVER)
    {
        // Prepare API path.
        $requestPath = $this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint, $remotePath, ['mode' => 'bin']);

        $f = fopen($localPath, 'w');

        return $this->request($requestPath, 'get', [], ['save_to' => $f]);
    }

    /**
     * @param $remotePath
     * @param $localPath
     * @param array $options
     *
     * @throws ParseException
     * @throws Exception
     * @throws FileNotUploadedException
     *
     * @return mixed
     */
    public function thumbnail($remotePath, $localPath, $options = [])
    {
        $thumbnailSize = isset($options['size']) ? $options['size'] : Jotta::THUMBNAIL_SIZE_MEDIUM;

        // Prepare API path.
        $requestPath = $this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint, $remotePath, ['mode' => 'thumb', 'ts' => $thumbnailSize]);

        $f = fopen($localPath, 'w');

        $response = $this->request($requestPath, 'get', [], ['save_to' => $f]);

        if (!$this->verify($remotePath, $localPath)) {
            throw new FileNotUploadedException();
        }

        return true;
    }

    /**
     * @param $localPath
     * @param $remotePath
     * @param int $overwriteMode
     *
     * @throws Exception
     * @throws ParseException
     *
     * @return array|bool|File|NamespaceContract|object|ResponseInterface|string
     */
    public function upload($localPath, $remotePath, $overwriteMode = Jotta::FILE_OVERWRITE_NEVER)
    {
        if (!file_exists($localPath) || !is_file($localPath)) {
            throw new Exception('File does not exist or not a file.');
        }

        $file = $this->get($remotePath);

        if (null !== $file) {
            switch ($overwriteMode) {
                case Jotta::FILE_OVERWRITE_NEVER:
                    return false;

                    break;
                case Jotta::FILE_OVERWRITE_IF_DIFFERENT:
                    if (!$file->isDifferentThan($localPath)) {
                        return false;
                    }

                    break;
                case Jotta::FILE_OVERWRITE_IF_NEWER:
                    if (!$file->isNewerThan($localPath)) {
                        return false;
                    }

                    break;
                case Jotta::FILE_OVERWRITE_IF_NEWER_OR_DIFFERENT:
                    if (!$file->isDifferentThan($localPath) && !$file->isNewerThan($localPath)) {
                        return false;
                    }

                    break;
            }
        }

        $requestPath = $this->getPath(Jotta::API_UPLOAD_URL, $this->device, $this->mountPoint, $remotePath);
        $filename = basename($localPath);

        return $this->serialize($this->request(
            $requestPath,
            'post',
            [
                'JSize' => filesize($filename),
                'JMd5' => md5(file_get_contents($filename)),
            ],
            [
                'multipart' => [
                    [
                        'name' => basename($filename),
                        'contents' => fopen($filename, 'r'),
                    ],
                ],
            ]
        ));
    }

    /**
     * @param $pathFrom
     * @param $pathTo
     * @param null $mountPointTo
     *
     * @throws ParseException
     *
     * @return array|NamespaceContract|object|ResponseInterface|string
     */
    public function move($pathFrom, $pathTo, $mountPointTo = null)
    {
        $mountPointTo = $mountPointTo ?: $this->mountPoint;

        $fullPathTo = $this->getPath(null, $this->device, $mountPointTo, $pathTo);
        $requestPath = $this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint, $pathFrom, [
            'mv' => $fullPathTo,
        ]);

        $response = $this->request($requestPath, 'post');

        return $this->serialize($response);
    }

    /**
     * @param $nameFrom
     * @param $nameTo
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
     * @throws ParseException
     *
     * @return array|NamespaceContract|object|ResponseInterface|string
     */
    public function delete($path)
    {
        $file = $this->get($path);
        if ($file->isDeleted()) {
            throw new \Exception('Deleting Trash items not supported.');
        }

        $requestPath = $this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint, $path, [
            'rm' => true,
        ]);

        $response = $this->request($requestPath, 'post');

        return $this->serialize($response);
    }
}
