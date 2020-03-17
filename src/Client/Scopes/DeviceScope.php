<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Scopes;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Vegfund\Jotta\Client\Contracts\NamespaceContract;
use Vegfund\Jotta\Client\Exceptions\JottaException;
use Vegfund\Jotta\Jotta;

/**
 * Class DeviceScope.
 */
class DeviceScope extends Scope
{
    /**
     * @throws Exception
     *
     * @return array|NamespaceContract[]
     */
    public function all()
    {
        $account = $this->serialize($this->request(
            $this->getPath(Jotta::API_BASE_URL, null, null)
        ));

        return $account->getDevices();
    }

    /**
     * @param string $device
     *
     * @throws JottaException
     * @throws Exception
     *
     * @return array|NamespaceContract|object|ResponseInterface|string
     */
    public function get($device = Jotta::DEVICE_JOTTA)
    {
        if (Jotta::DEVICE_JOTTA !== $device) {
            throw new JottaException('CLI devices are not supported.');
        }

        return $this->serialize($this->request(
            $this->getPath(Jotta::API_BASE_URL, ($device ?: Jotta::DEVICE_JOTTA), null)
        ));
    }
}
