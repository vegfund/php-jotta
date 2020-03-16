<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Resources;

use Sabre\Xml\LibXMLException;
use Sabre\Xml\ParseException;
use Vegfund\Jotta\Client\Contracts\NamespaceContract;
use Vegfund\Jotta\Client\Responses\Namespaces\CurrentRevision;

/**
 * Class CurrentRevisionResource
 * @package Vegfund\Jotta\Client\Resources
 */
class CurrentRevisionResource extends AbstractResource
{
    /**
     * CurrentRevisionResource constructor.
     * @param $resource
     * @throws LibXMLException
     * @throws ParseException
     */
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
