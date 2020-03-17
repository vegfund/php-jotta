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
use Vegfund\Jotta\Jotta;
use Vegfund\Jotta\Support\JFileInfo;

class FileScope extends Scope
{
    /**
     * @param $remotePath
     * @return array|object|ResponseInterface|string|NamespaceContract
     * @throws Exception
     */
    public function get($remotePath)
    {
        $response = $this->request(
            $this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint, $remotePath)
        );

        return $this->serialize($response, 'file');
    }

    /**
     * @param $remotePath
     * @param null $localPath
     *
     * @return bool
     * @throws Exception
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
     * @param string $size
     * @return bool
     * @throws JottaException
     * @throws Exception
     */
    public function thumbnail($remotePath, $localPath, $size = Jotta::THUMBNAIL_SIZE_MEDIUM)
    {
        $f = fopen($localPath, 'w');

        $this->request($this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint, $remotePath, ['mode' => 'thumb', 'ts' => $size]), 'get', [], ['save_to' => $f]);

        fclose($f);

        if (!$this->verify($remotePath, $localPath)) {
            throw new JottaException('File not uploaded.');
        }

        return true;
    }

    /**
     * @param $localPath
     * @param $remotePath
     * @param int $overwriteMode
     * @return array|bool|object|ResponseInterface|string|NamespaceContract
     * @throws JottaException
     * @throws Exception
     */
    public function upload($localPath, $remotePath, $overwriteMode = Jotta::FILE_OVERWRITE_NEVER)
    {
        if (!file_exists($localPath) || !is_file($localPath)) {
            throw new JottaException('File does not exist or not a file.');
        }

        $file = $this->get($remotePath);

        return $this->blockOverwrite($file, $overwriteMode) ? false : $this->makeUpload($localPath, $remotePath);
    }

    /**
     * @param File $file
     * @param $overwriteMode
     *
     * @return bool
     */
    protected function blockOverwrite(File $file, $overwriteMode)
    {
        return null !== $file && (($overwriteMode === Jotta::FILE_OVERWRITE_NEVER) ||
            ($overwriteMode === Jotta::FILE_OVERWRITE_IF_DIFFERENT && !$file->isDifferentThan($localPath)) ||
            ($overwriteMode === Jotta::FILE_OVERWRITE_IF_NEWER && !$file->isNewerThan($localPath)) ||
            ($overwriteMode === Jotta::FILE_OVERWRITE_IF_NEWER_OR_DIFFERENT && (!$file->isDifferentThan($localPath) && !$file->isNewerThan($localPath))));
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

        return $this->serialize($this->request(
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
    }

    /**
     * @param $pathFrom
     * @param $pathTo
     * @param null $mountPointTo
     * @return array|object|ResponseInterface|string|NamespaceContract
     * @throws JottaException
     * @throws Exception
     */
    public function move($pathFrom, $pathTo, $mountPointTo = null)
    {
        $file = $this->get($pathFrom);
        if (!($file instanceof File)) {
            throw new JottaException('This is not a remote file.');
        }

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
     * @return array|object|ResponseInterface|string|NamespaceContract
     * @throws JottaException
     */
    public function rename($nameFrom, $nameTo)
    {
        return $this->move($nameFrom, $nameTo);
    }

    /**
     * @param $path
     * @return array|object|ResponseInterface|string|NamespaceContract
     * @throws JottaException
     * @throws Exception
     */
    public function delete($path)
    {
        if ($this->get($path)->isDeleted()) {
            throw new JottaException('Deleting Trash items not supported.');
        }

        $response = $this->request($this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint, $path, [
            'rm' => true,
        ]), 'post');

        return $this->serialize($response);
    }
}
