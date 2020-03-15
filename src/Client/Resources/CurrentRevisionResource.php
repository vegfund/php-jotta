<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Resources;

use Vegfund\Jotta\Client\Contracts\NamespaceContract;
use Vegfund\Jotta\Client\Responses\Namespaces\CurrentRevision;

class CurrentRevisionResource extends AbstractResource
{
    public function __construct($resource)
    {
        if (!($resource instanceof NamespaceContract)) {
            $resource = (new CurrentRevision())->fill($resource);
        }
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
