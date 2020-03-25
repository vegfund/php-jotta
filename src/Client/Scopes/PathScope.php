<?php

namespace Vegfund\Jotta\Client\Scopes;

use Exception;
use Vegfund\Jotta\Client\Contracts\NamespaceContract;
use Vegfund\Jotta\Client\Exceptions\JottaException;
use Vegfund\Jotta\Client\Responses\Namespaces\Device;
use Vegfund\Jotta\Client\Responses\Namespaces\File;
use Vegfund\Jotta\Client\Responses\Namespaces\Folder;
use Vegfund\Jotta\Client\Responses\Namespaces\MountPoint;
use Vegfund\Jotta\Client\Responses\Namespaces\User;
use Vegfund\Jotta\Jotta;
use Vegfund\Jotta\JottaClient;

/**
 * Class PathScope
 * @package Vegfund\Jotta\Client\Scopes
 */
class PathScope extends Scope
{
    /**
     * @param $path
     * @param JottaClient $jottaClient
     * @return AccountScope|DeviceScope|DirectoryScope|FileScope|Scope
     * @throws JottaException
     */
    public function detect($path, JottaClient $jottaClient)
    {
        $serialized = $this->serialize($this->request($this->getPath(Jotta::API_BASE_URL, $this->device, $this->mountPoint, $path)));

        try {
            $segments = explode('/', $serialized->getPath());
        } catch (Exception $e) {
            $segments = [];
        }

        switch(get_class($serialized)) {
            case User::class:
                return $jottaClient->account();
                break;
            case Device::class:
                return $jottaClient->device(['device' => $serialized->getName()]);
                break;
            case MountPoint::class:
                return $jottaClient->mountPoint(['device' => $segments[2], 'mount_point' => $serialized->getName()]);
                break;
            case Folder::class:
                return $jottaClient->folder(['device' => $segments[2], 'mount_point' => $segments[3]]);
                break;
            case File::class:
                return $jottaClient->file(['device' => $segments[2], 'mount_point' => $segments[3]]);
                break;
            default:
                throw new JottaException('Unknown root');
        }
    }
}