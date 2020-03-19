<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Traits;

use Exception;
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
    protected $regex;

    /**
     * @var string
     */
    protected $uuid;

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
}
