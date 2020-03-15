<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Resources;

use Vegfund\Jotta\Client\Responses\Namespaces\User;

/**
 * Class UserResource.
 *
 * @mixin User
 */
class UserResource extends AbstractResource
{
    /**
     * @return array
     */
    public function arrayDefinition()
    {
        return [
            'username' => $this->username,
            'account-type' => $this->accountType,
            'locked' => $this->locked,
            'capacity' => $this->capacity,
            'max-devices' => $this->maxDevices,
            'max-mobile-devices' => $this->maxMobileDevices,
            'usage' => $this->usage,
            'read-locked' => $this->readLocked,
            'write-locked' => $this->writeLocked,
            'quora-write-locked' => $this->quotaWriteLocked,
            'enable-sync' => $this->enableSync,
            'enable-foldershare' => $this->enableFoldershare,
            'business-role' => $this->businessRole,
            'business-name' => $this->businessName,
        ];
    }
}
