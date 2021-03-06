<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Scopes;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Vegfund\Jotta\Client\Contracts\NamespaceContract;
use Vegfund\Jotta\Client\Exceptions\JottaException;
use Vegfund\Jotta\Client\Responses\Namespaces\File;
use Vegfund\Jotta\Client\Responses\Namespaces\Folder;
use Vegfund\Jotta\Client\Responses\Namespaces\MountPoint;
use Vegfund\Jotta\Jotta;
use Vegfund\Jotta\Support\JFileInfo;

/**
 * Class FileScope.
 */
class FileScope extends Scope
{
    /**
     * @param $remotePath
     *
     * @throws Exception
     * @throws JottaException
     *
     * @return array|object|ResponseInterface|string|NamespaceContract
     */
    public function get($remotePath)
    {
        $response = $this->request(
            $this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint, $remotePath)
        );

        $result = $this->serialize($response);

        if (!($result instanceof File)) {
            if (null !== $result) {
                throw new JottaException('This is not a remote file.');
            }

            return null;
        }

        return $result;
    }

    /**
     * @param $remotePath
     * @param null $localPath
     *
     * @throws Exception
     *
     * @return bool
     *
     * @see https://github.com/albertony/jafs/blob/master/JottacloudFileSystem/JaFS.cs (VerifyFile)
     */
    public function verify($remotePath, $localPath = null)
    {
        if (null === $localPath) {
            // only check the remote file
            $file = $this->get($remotePath);

            return null !== $file && null !== $file->currentRevision && $file->isValid() && (null === $localPath || null !== $localPath && md5(file_get_contents($localPath)) === $file->currentRevision->md5);
        } else {
            if (!file_exists($localPath) || !is_file($localPath)) {
                throw new JottaException('File does not exist or not a file.');
            }

            $fileInfo = JFileInfo::make($localPath);

            $headers = [
                'JMd5'      => $fileInfo->getMd5(),
                'JSize'     => $fileInfo->getSize(),
                'JCreated'  => $fileInfo->getCTime(),
                'JModified' => $fileInfo->getMTime(),
            ];

            $requestPath = $this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint, $remotePath, ['cphash' => 'true']);

            try {
                $serialized = $this->serialize($this->request($requestPath, 'post', $headers));

                return true;
            } catch (Exception $e) {
                return false;
            }
        }
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
        $file = $this->get($remotePath);

        if (file_exists($localPath) && is_file($localPath)) {
            $fileinfo = JFileInfo::make($localPath);

            switch ($overwriteMode) {
                case Jotta::FILE_OVERWRITE_NEVER:
                    return null;
                    break;
                case Jotta::FILE_OVERWRITE_IF_NEWER_OR_DIFFERENT:
                    if ($file->getMd5() === $fileinfo->getMd5() && !$file->isNewerThan($localPath)) {
                        return null;
                    }
                    break;
                case Jotta::FILE_OVERWRITE_IF_NEWER:
                    if (!$file->isNewerThan($localPath)) {
                        return null;
                    }
                    break;
                case Jotta::FILE_OVERWRITE_IF_DIFFERENT:
                    if ($file->getMd5() === $fileinfo->getMd5()) {
                        return null;
                    }
                    break;
            }
        }

        // Prepare API path.
        $requestPath = $this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint, $remotePath, ['mode' => 'bin']);

        @mkdir(dirname($localPath));

        $f = fopen($localPath, 'w');

        return $this->request($requestPath, 'get', [], ['save_to' => $f]);
    }

    /**
     * @param $remotePath
     * @param $localPath
     * @param string $size
     *
     * @throws JottaException
     * @throws Exception
     *
     * @return bool
     */
    public function thumbnail($remotePath, $localPath, $size = Jotta::THUMBNAIL_SIZE_MEDIUM)
    {
        @mkdir(dirname($localPath));
        $f = fopen($localPath, 'w');

        $this->request($this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint, $remotePath, ['mode' => 'thumb', 'ts' => $size]), 'get', [], ['save_to' => $f]);

        return true;
    }

    /**
     * @param $localPath
     * @param $remotePath
     * @param int $overwriteMode
     *
     * @throws JottaException
     * @throws Exception
     *
     * @return array|bool|object|ResponseInterface|string|NamespaceContract
     */
    public function upload($localPath, $remotePath = '', $overwriteMode = Jotta::FILE_OVERWRITE_NEVER)
    {
        if (!file_exists($localPath) || !is_file($localPath)) {
            throw new JottaException('File does not exist or not a file.');
        }

        if ($remotePath !== '') {
            try {
                $result = $this->serialize($this->request($this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint, $remotePath)));

                if ($result instanceof File) {
                    // overwriting
                    $fileinfo = JFileInfo::make($localPath);

                    switch ($overwriteMode) {
                        case Jotta::FILE_OVERWRITE_NEVER:
                            return null;
                            break;
                        case Jotta::FILE_OVERWRITE_IF_NEWER_OR_DIFFERENT:
                            if ($result->getMd5() === $fileinfo->getMd5() && $result->isNewerThan($fileinfo)) {
                                return null;
                            }
                            break;
                        case Jotta::FILE_OVERWRITE_IF_NEWER:
                            if ($result->isNewerThan($localPath)) {
                                return null;
                            }
                            break;
                        case Jotta::FILE_OVERWRITE_IF_DIFFERENT:
                            if ($result->getMd5() === $fileinfo->getMd5()) {
                                return null;
                            }
                            break;
                    }
                } elseif ($result instanceof Folder || $result instanceof MountPoint) {
                    $remotePath = implode('/', [$remotePath, basename($localPath)]);
                }
            } catch (Exception $e) {
                // File doesn't exist on remote storage
            }
        } else {
            $remotePath = implode('/', [$remotePath, basename($localPath)]);
        }

        return $this->makeUpload($localPath, $remotePath);
    }

    /**
     * @param $localPath
     * @param $remotePath
     *
     * @throws Exception
     *
     * @return array|object|ResponseInterface|string|NamespaceContract
     */
    protected function makeUpload($localPath, $remotePath)
    {
        $requestPath = $this->getPath(Jotta::API_UPLOAD_URL, $this->device, $this->mountPoint, $remotePath);
        $file = JFileInfo::make($localPath);

        $this->serialize($this->request(
            $requestPath,
            'post',
            [
                'JSize' => $file->getSize(),
                'JMd5'  => $file->getMd5(),
            ],
            [
                'multipart' => [
                    [
                        'name'     => $file->getFilename(),
                        'contents' => fopen($file->getRealPath(), 'r'),
                    ],
                ],
            ]
        ));

        if ($this->verify($remotePath, $localPath)) {
            return $this->get($remotePath);
        }

        throw new JottaException('File cannot be uploaded.');
    }

    /**
     * @param $pathFrom
     * @param $pathTo
     * @param null $mountPointTo
     *
     * @throws JottaException
     * @throws Exception
     *
     * @return array|object|ResponseInterface|string|NamespaceContract
     */
    public function move($pathFrom, $pathTo, $mountPointTo = null)
    {
        $file = $this->get($pathFrom);
        if (!($file instanceof File)) {
            throw new JottaException('This is not a remote file.');
        }

        $mountPointTo = $mountPointTo ?: $this->mountPoint;

        $fullPathTo = $this->getPath(null, $this->device, $mountPointTo, $pathTo);
        if (0 !== strpos($fullPathTo, '/')) {
            $fullPathTo = '/'.$fullPathTo.'/'.basename($pathFrom);
        }
        $requestPath = $this->getPath(Jotta::API_UPLOAD_URL, $this->device, $this->mountPoint, $pathFrom, [
            'mv' => $fullPathTo,
        ]);

        $response = $this->request($requestPath, 'post');

        return $this->serialize($response);
    }

    /**
     * @param $nameFrom
     * @param $nameTo
     *
     * @param null $mountPointTo
     * @return array|object|ResponseInterface|string|NamespaceContract
     * @throws JottaException
     */
    public function rename($nameFrom, $nameTo, $mountPointTo = null)
    {
        return $this->move($nameFrom, $nameTo, $mountPointTo);
    }

    /**
     * @param $path
     *
     * @throws JottaException
     * @throws Exception
     *
     * @return array|object|ResponseInterface|string|NamespaceContract
     */
    public function delete($path)
    {
        if ($this->get($path)->isDeleted()) {
            throw new JottaException('Deleting Trash items not supported.');
        }

        $response = $this->request($this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint, $path, [
            'rm' => 'true',
        ]), 'post');

        return $this->serialize($response);
    }
}
