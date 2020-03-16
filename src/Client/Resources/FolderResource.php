<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Resources;

/**
 * Class FolderResource.
 */
class FolderResource extends AbstractResource
{
    /**
     * @return array
     */
    public function arrayDefinition()
    {
        return [
            'name' => $this->name,
        ];
    }
}
