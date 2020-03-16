<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Responses;

use DateTime;
use Illuminate\Support\Str;
use Sabre\Xml\LibXMLException;
use Sabre\Xml\ParseException;
use Sabre\Xml\Reader;
use Sabre\Xml\XmlDeserializable;
use Vegfund\Jotta\Client\Contracts\NamespaceContract;
use Vegfund\Jotta\Client\Responses\Namespaces\Attributes;

/**
 * Class AbstractNamespace.
 */
abstract class ResponseNamespace implements NamespaceContract, XmlDeserializable
{
    /**
     * @var array
     */
    protected $keyValueMap = [];

    /**
     * @var array
     */
    protected $enumMap = [];

    /**
     * @var array
     */
    protected $objectValueMap = [];

    /**
     * @var Attributes
     */
    public $attributes;

    /**
     * @param $name
     *
     * @return null|mixed
     */
    public function __get($name)
    {
        if (isset($this->{$name}) && null !== $this->{$name}) {
            return $name;
        }

        if (isset($this->attributes) && ($this->attributes instanceof Attributes) && isset($this->attributes->{$name}) && null !== $this->attributes->{$name}) {
            return $this->attributes->{$name};
        }

        return null;
    }

    final public function getAttribute($name)
    {
        if (isset($this->attributes) && $this->attributes instanceof Attributes) {
            return $this->attributes->get($name);
        }
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return null|mixed
     */
    public function __call($name, $arguments)
    {
        if ('get' === substr($name, 0, 3)) {
            $name = Str::camel(substr($name, 3));

            return $this->{$name};
        }

        return null;
    }

    /**
     * @param $data
     *
     * @throws LibXMLException
     * @throws ParseException
     *
     * @return $this
     */
    public function fill($data)
    {
        $this->attachFields($data);

        return $this;
    }

    /**
     * @param $data
     *
     * @throws LibXMLException
     * @throws ParseException
     *
     * @return NamespaceContract
     *
     * @todo Attributes for each object type
     */
    final protected function attachFields($data)
    {
        $attributes = null;
        $children = $data;

        if ($data instanceof  Reader) {
            $attributes = new Attributes($data->parseAttributes());
            $children = $data->parseInnerTree();
        }

        if (is_array($children)) {
            $this->attachKeyValues($children);
            $this->attachEnums($children);
            $this->attachObjectValues($children);
            $this->setAttributes($attributes);
        }

        return $this;
    }

    /**
     * @todo Attributes casting
     */
    final protected function setAttributes(Attributes $attributes = null)
    {
        if (null === $attributes) {
            return;
        }

        $this->attributes = $attributes;
    }

    /**
     * @param array $children
     */
    final protected function attachObjectValues($children)
    {
        foreach ($this->objectValueMap as $item) {
            foreach ($children as $child) {
                if ($child['name'] === '{}'.$item) {
                    $this->{$item} = $child['value'];
                }
            }
        }
    }

    /**
     * @param array $children
     */
    final protected function attachKeyValues($children)
    {
        $keyValues = $this->getKeyValueFields($children);

        foreach ($this->keyValueMap as $field) {
            $fieldType = 'string';
            if (\is_array($field)) {
                list($field, $fieldType) = [
                    array_keys($field)[0],
                    array_values($field)[0],
                ];
            }

            if (isset($keyValues['{}'.$field])) {
                $this->{Str::camel($field)} = $this->castPrimitives($keyValues['{}'.$field], $fieldType);
            }
        }
    }

    /**
     * @param array $children
     */
    final protected function attachEnums($children)
    {
        foreach ($this->enumMap as $item) {
            foreach ($children as $child) {
                if ($child['name'] === '{}'.$item) {
                    $this->{$item} = $child['value'];
                }
            }
        }
    }

    /**
     * @param $value
     * @param string $type
     *
     * @return bool|DateTime|false|float|int|string
     */
    final protected function castPrimitives($value, $type = 'string')
    {
        switch ($type) {
            case 'int':
                return (int) $value;

                break;
            case 'float':
                return (float) $value;

                break;
            case 'bool':
                return true === $value || 'true' === $value || '1' === $value;

                break;
            case 'datetime':
                return DateTime::createFromFormat('Y-m-d-\TH:i:sO', $value);

                break;
            default:
                return (string) $value;

                break;
        }
    }

    /**
     * @param array $children
     *
     * @return array
     */
    final protected function getKeyValueFields($children)
    {
        $keyValueFields = [];

        foreach ($children as $child) {
            if (!\is_object($child['value'])) {
                $keyValueFields[$child['name']] = $child['value'];
            }
        }

        return $keyValueFields;
    }
}
