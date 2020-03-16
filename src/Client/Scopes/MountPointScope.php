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
use Vegfund\Jotta\Client\Resources\FolderListingResource;
use Vegfund\Jotta\Client\Responses\Namespaces\Device;
use Vegfund\Jotta\Client\Responses\Namespaces\Folder;
use Vegfund\Jotta\Jotta;

class MountPointScope extends Scope
{
    /**
     * @throws ParseException
     *
     * @return mixed
     */
    public function all()
    {
        /**
         * @var Device
         */
        $device = $this->jottaClient->device()->get();

        return $device->getMountPoints();
    }

    /**
     * @throws ParseException
     *
     * @return array|NamespaceContract|object|ResponseInterface|string
     */
    public function get()
    {
        $response = $this->request(
            $this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint)
        );

        return $this->serialize($response);
    }

    /**
     * Create a remote folder.
     *
     * @param mixed $name
     *
     * @throws ParseException
     * @throws Exception
     *
     * @return array|Folder|NamespaceContract|object|ResponseInterface|string
     */
    public function create($name)
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
     * @return array
     * @throws ParseException
     */
    public function list()
    {
        $mountPoint = $this->get();

        return (new FolderListingResource($mountPoint))->toArray();
    }

    /**
     * @param array $options
     *
     * @throws ParseException
     *
     * @return array
     */
    public function listRecursive($options = [])
    {
        $folderScope = $this->getClient()->folder([
            'device'      => $this->device,
            'mount_point' => $this->mountPoint,
        ]);

        return $folderScope->listRecursive('', $options);
    }

    /**
     * @throws ParseException
     * @throws Exception
     *
     * @return array|NamespaceContract|object|ResponseInterface|string
     */
    public function delete()
    {
        $forbidden = [
            Jotta::MOUNT_POINT_ARCHIVE,
            Jotta::MOUNT_POINT_SHARED,
            Jotta::MOUNT_POINT_SYNC,
        ];

        if (\in_array($this->mountPoint, $forbidden, true)) {
            throw new Exception('The mount point '.$this->mountPoint.' cannot be deleted.');
        }

        $response = $this->request(
            $this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint, null, ['rm' => 'true']),
            'post'
        );

        return $this->serialize($response);
    }
}
