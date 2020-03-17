<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Scopes;

use Exception;
use Vegfund\Jotta\Client\Contracts\NamespaceContract;
use Vegfund\Jotta\Client\Responses\Namespaces\User;
use Vegfund\Jotta\Jotta;

class AccountScope extends Scope
{
    /**
     * @return NamespaceContract|string|User
     * @throws Exception
     */
    public function index()
    {
        $requestPath = $this->getPath(Jotta::API_BASE_URL, null, null);
        $response = $this->request($requestPath);

        return $this->serialize($response);
    }

    /**
     * @return string|NamespaceContract|User
     * @throws Exception
     */
    public function data()
    {
        return $this->index();
    }
}
