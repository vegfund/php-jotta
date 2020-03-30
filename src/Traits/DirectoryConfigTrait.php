<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Traits;

use Exception;
use Vegfund\Jotta\Client\Responses\Namespaces\File;
use Vegfund\Jotta\Client\Responses\ResponseNamespace;
use Vegfund\Jotta\Client\Scopes\DirectoryScope;

/**
 * Trait DirectoryScopeConfig.
 *
 * @mixin DirectoryScope
 */
trait DirectoryConfigTrait
{
    /**
     * @var bool
     */
    protected $deleted = false;

    /**
     * @var bool
     */
    protected $corrupt = false;

    /**
     * @var bool
     */
    protected $completed = true;

    /**
     * @var bool
     */
    protected $incomplete = false;

    /**
     * @var string
     */
    protected $regex = null;

    /**
     * @var string
     */
    protected $uuid = null;

    /**
     * @param bool $deleted
     *
     * @return $this
     */
    public function deleted($deleted = false)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return bool
     */
    public function withDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param bool $corrupt
     *
     * @return $this
     */
    public function corrupt($corrupt = false)
    {
        $this->corrupt = $corrupt;

        return $this;
    }

    /**
     * @return bool
     */
    public function withCorrupt()
    {
        return $this->corrupt;
    }

    /**
     * @param bool $completed
     *
     * @return $this
     */
    public function completed($completed = false)
    {
        $this->completed = $completed;

        return $this;
    }

    /**
     * @return bool
     */
    public function withCompleted()
    {
        return $this->completed;
    }

    /**
     * @param bool $incomplete
     *
     * @return $this
     */
    public function incomplete($incomplete = false)
    {
        $this->incomplete = $incomplete;

        return $this;
    }

    /**
     * @return bool
     */
    public function withIncomplete()
    {
        return $this->incomplete;
    }

    /**
     * @param string $regex
     *
     * @return $this
     */
    public function regex($regex)
    {
        $this->regex = $regex;

        return $this;
    }

    /**
     * @return string
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * @param string $uuid
     *
     * @throws Exception
     *
     * @return $this
     */
    public function uuid($uuid)
    {
        if (!is_string($uuid) || !preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $uuid)) {
            throw new Exception('This is not a valid UUID.');
        }
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param $collection
     *
     * @return array
     */
    protected function applyFilters($collection)
    {
        return array_filter($collection, function (ResponseNamespace $item) {
            if (null !== $this->getRegex() && 0 === preg_match($this->regex, $item->getName())) {
                return false;
            }

            if (null !== $this->getUuid() && $this->getUuid() !== $item->getUuid()) {
                return false;
            }

            if (!$this->withDeleted() && $item->isDeleted()) {
                return false;
            }

            if ($item instanceof File && !$this->withCorrupt() && $item->isCorrupt()) {
                return false;
            }

            if ($item instanceof File && !$this->withCompleted() && !$item->isCompleted()) {
                return false;
            }

            if ($item instanceof File && !$this->withIncomplete() && $item->isIncomplete()) {
                return false;
            }

            return true;
        });
    }
}
