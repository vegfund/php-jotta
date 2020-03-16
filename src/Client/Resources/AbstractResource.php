<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Resources;

use Vegfund\Jotta\Client\Contracts\NamespaceContract;

/**
 * Class AbstractResource.
 */
abstract class AbstractResource
{
    /**
     * @var NamespaceContract
     */
    protected $resource;

    /**
     * AbstractResource constructor.
     * @param NamespaceContract $resource
     */
    public function __construct(NamespaceContract $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param $name
     *
     * @return null|mixed
     */
    public function __get($name)
    {
        return !isset($this->{$name}) ? (!isset($this->resource->{$name}) ? null : $this->resource->{$name}) : $this->{$name};
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->resource, $name)) {
            return $this->resource->{$name}(...$arguments);
        }
    }

    /**
     * @return array
     */
    abstract public function arrayDefinition();

    /**
     * @param array $data
     *
     * @return array
     */
    public static function collection($data = [])
    {
        return array_map(function ($item) {
            return (new static($item))->toArray();
        }, $data);
    }

    /**
     * @return array
     */
    final public function toArray()
    {
        return array_filter($this->arrayDefinition());
    }
}
