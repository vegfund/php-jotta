<?php

namespace Vegfund\Jotta\Traits;

use Vegfund\Jotta\Client\Scopes\Scope;
use Vegfund\Jotta\Jotta;

/**
 * Trait ScopeConfig.
 *
 * @mixin Scope
 */
trait ScopeConfigTrait
{
    /**
     * @param string $username
     *
     * @return $this
     */
    final public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    final public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $apiUrl
     *
     * @return $this
     */
    public function setApiUrl($apiUrl = Jotta::API_BASE_URL)
    {
        $this->apiUrl = $apiUrl;

        return $this;
    }

    /**
     * @param string $device
     *
     * @return $this
     */
    final public function setDevice($device = Jotta::DEVICE_JOTTA)
    {
        $this->device = Jotta::DEVICE_JOTTA;

        return $this;
    }

    /**
     * @return string
     */
    final public function getDevice()
    {
        return $this->device;
    }

    /**
     * @param string $mountPoint
     *
     * @return $this
     */
    final public function setMountPoint($mountPoint = Jotta::MOUNT_POINT_ARCHIVE)
    {
        $this->mountPoint = $mountPoint;

        return $this;
    }

    /**
     * @return string
     */
    final public function getMountPoint()
    {
        return $this->mountPoint;
    }

    /**
     * @param string $basePath
     *
     * @return $this
     */
    final public function setBasePath($basePath = '')
    {
        $this->basePath = $basePath;

        return $this;
    }

    /**
     * @return string
     */
    final public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @return $this
     */
    final public function setAsyncRequest()
    {
        $this->requestType = 'async';

        return $this;
    }

    /**
     * @return $this
     */
    final public function setSyncRequest()
    {
        $this->requestType = 'sync';

        return $this;
    }

    /**
     * @return $this
     */
    final public function setAutoRequest()
    {
        $this->requestType = 'auto';

        return $this;
    }

    /**
     * @return $this
     */
    final public function withExceptions()
    {
        $this->shouldThrowExceptions = true;

        return $this;
    }

    /**
     * @return $this
     */
    final public function withoutExceptions()
    {
        $this->shouldThrowExceptions = false;

        return $this;
    }
}
