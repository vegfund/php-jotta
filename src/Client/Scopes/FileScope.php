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
use Vegfund\Jotta\Client\Responses\Namespaces\File;
use Vegfund\Jotta\Jotta;

class FileScope extends Scope
{
    /**
     * @param $remotePath
     * @return array|NamespaceContract|object|ResponseInterface|string
     * @throws ParseException
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
     * @param string $size
     * @return bool
     * @throws JottaException
     * @throws ParseException
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
     * @throws ParseException
     */
    public function upload($localPath, $remotePath, $overwriteMode = Jotta::FILE_OVERWRITE_NEVER)
    {
        if (!file_exists($localPath) || !is_file($localPath)) {
            throw new JottaException('File does not exist or not a file.');
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
                'JMd5'  => md5(file_get_contents($filename)),
            ],
            [
                'multipart' => [
                    [
                        'name'     => basename($filename),
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
     * @throws ParseException
     *
     * @return array|NamespaceContract|object|ResponseInterface|string
     */
    public function rename($nameFrom, $nameTo)
    {
        return $this->move($nameFrom, $nameTo);
    }

    /**
     * @param $path
     * @return array|object|ResponseInterface|string|NamespaceContract
     * @throws JottaException
     * @throws ParseException
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
