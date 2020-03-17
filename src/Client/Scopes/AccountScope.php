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
     * @throws Exception
     *
     * @return NamespaceContract|string|User
     */
    public function index()
    {
        $requestPath = $this->getPath(Jotta::API_BASE_URL, null, null);
        $response = $this->request($requestPath);

        return $this->serialize($response);
    }

    /**
     * @throws Exception
     *
     * @return string|NamespaceContract|User
     */
    public function data()
    {
        return $this->index();
    }
}
