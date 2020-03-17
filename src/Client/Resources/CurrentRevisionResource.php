<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Resources;

/**
 * Class CurrentRevisionResource.
 */
class CurrentRevisionResource extends AbstractResource
{
    /**
     * CurrentRevisionResource constructor.
     *
     * @param $resource
     */
    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    /**
     * @return array
     */
    public function arrayDefinition()
    {
        return [
            'state' => $this->state,
        ];
    }
}
