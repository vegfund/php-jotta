<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Resources;

use Vegfund\Jotta\Client\Responses\Namespaces\MountPoint;

/**
 * Class MountPointResource.
 *
 * @mixin MountPoint
 */
class MountPointResource extends AbstractResource
{
    /**
     * @return array
     */
    public function arrayDefinition()
    {
        return [
            'name' => $this->name,
            'path' => $this->path,
            'abspath' => $this->abspath,
            'size' => $this->size,
            'modified' => $this->modified,
            'device' => $this->device,
            'user' => $this->user,
        ];
    }
}
