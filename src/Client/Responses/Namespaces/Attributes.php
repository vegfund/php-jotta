<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Responses\Namespaces;

/**
 * Class Attributes.
 */
class Attributes
{
    /**
     * Attributes constructor.
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        foreach ($attributes as $name => $attribute) {
            $this->{$name} = $attribute;
        }
    }

    /**
     * @param $attribute
     *
     * @return null|mixed
     */
    public function get($attribute)
    {
        if (isset($this->{$attribute})) {
            return $this->{$attribute};
        }

        return null;
    }
}
