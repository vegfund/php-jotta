<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Resources;

class FileResource extends AbstractResource
{
    /**
     * @return array
     */
    public function arrayDefinition()
    {
        return [
            'name' => $this->name,
            'uuid' => $this->uuid,
            'path' => $this->path,
            'abspath' => $this->abspath,
            'current_revision' => (new CurrentRevisionResource($this->currentRevision))->toArray(),
            'latest_revision' => (new CurrentRevisionResource($this->latestRevision))->toArray(),
        ];
    }
}
