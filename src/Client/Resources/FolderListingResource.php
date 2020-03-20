<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Resources;

use Vegfund\Jotta\Client\Responses\Namespaces\Folder;
use Vegfund\Jotta\Client\Responses\Namespaces\MountPoint;

/**
 * Class FolderListingResource.
 * @mixin MountPoint
 * @mixin Folder
 */
class FolderListingResource extends AbstractResource
{
    /**
     * @return array
     */
    public function arrayDefinition()
    {
        return array_merge($this->getFolders(), $this->getFiles());
    }
}
