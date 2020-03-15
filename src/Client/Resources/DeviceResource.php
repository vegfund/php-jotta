<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Resources;

use Vegfund\Jotta\Client\Responses\Namespaces\Device;

/**
 * Class DeviceResource.
 *
 * @mixin Device
 */
class DeviceResource extends AbstractResource
{
    /**
     * @return array
     */
    public function arrayDefinition()
    {
        return [
            'name' => $this->name,
            'display-name' => $this->displayName,
            'type' => $this->type,
            'sid' => $this->sid,
            'size' => $this->size,
            'modified' => $this->modified,
            'user' => $this->user,
        ];
    }
}
