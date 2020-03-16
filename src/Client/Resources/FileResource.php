<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Resources;

use Sabre\Xml\LibXMLException;
use Sabre\Xml\ParseException;

/**
 * Class FileResource
 * @package Vegfund\Jotta\Client\Resources
 */
class FileResource extends AbstractResource
{
    /**
     * @return array
     * @throws LibXMLException
     * @throws ParseException
     */
    public function arrayDefinition()
    {
        return [
            'name'             => $this->name,
            'uuid'             => $this->uuid,
            'path'             => $this->path,
            'abspath'          => $this->abspath,
            'current_revision' => (new CurrentRevisionResource($this->currentRevision))->toArray(),
            'latest_revision'  => (new CurrentRevisionResource($this->latestRevision))->toArray(),
        ];
    }
}
