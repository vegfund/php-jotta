<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Traits;

use Exception;
use Vegfund\Jotta\Client\Responses\Namespaces\File;
use Vegfund\Jotta\Client\Responses\Namespaces\Folder;
use Vegfund\Jotta\Client\Responses\ResponseNamespace;
use Vegfund\Jotta\Client\Scopes\DirectoryScope;

/**
 * Trait DirectoryScopeConfig.
 *
 * @mixin DirectoryScope
 */
trait DirectoryScopeConfig
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
     * @return array
     */
    protected function applyFilters($collection)
    {
        return array_filter($collection, function (ResponseNamespace $item) {
            return ($item instanceof Folder) || (($this->withCompleted() && $item->isCompleted() || !$this->withCompleted() && !$item->isCompleted())
                && ($this->withDeleted() && $item->isDeleted() || !$this->withDeleted() && !$item->isDeleted())
                && ($this->withCorrupt() && $item->isCorrupt() || !$this->withCorrupt() && !$item->isCorrupt())
                && (null === $this->getRegex() || (null !== $this->getRegex() && false !== preg_match($this->regex, $item->getName())))
                && (null === $this->getUuid() || (null !== $this->getUuid() && $this->getUuid() === $item->getUuid)))
                ;
        });
    }
}
