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
use Vegfund\Jotta\Client\Exceptions\JottaException;
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
     * @throws JottaException
     *
     * @return null|mixed
     */
    public function __get($name)
    {
        if (null !== ($value = $this->getAttribute($name))) {
            return $value;
        }

        throw new JottaException('The attribute '.$name.' does not exist.');
    }

    /**
     * @param $name
     *
     * @return mixed|null
     */
    final public function getAttribute($name)
    {
        return (isset($this->attributes) && $this->attributes instanceof Attributes) ? $this->attributes->get($name) : null;
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @throws JottaException
     *
     * @return null|mixed
     */
    public function __call($name, $arguments)
    {
        if ('get' === substr($name, 0, 3)) {
            $name = Str::camel(substr($name, 3));

            return $this->{$name};
        }

        throw new JottaException('The method '.$name.' does not exist.');
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
        $children = $data;

        if ($data instanceof  Reader) {
            $this->setAttributes(new Attributes($data->parseAttributes()));
            $children = $data->parseInnerTree();
        }

        if (is_array($children)) {
            $this->attachKeyValues($children);
            $this->attachEnums($children);
            $this->attachObjectValues($children);
        }

        return $this;
    }

    /**
     * @param Attributes|null $attributes
     *
     * @todo Attributes casting
     */
    final protected function setAttributes(Attributes $attributes = null)
    {
        $this->attributes = $attributes ?: new Attributes([]);

        foreach ($attributes->all() as $key => $value) {
            if (!isset($this->{$key})) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * @param array $children
     */
    final protected function attachObjectValues($children)
    {
        foreach ($children as $child) {
            $field = substr($child['name'], 2);
            if (in_array($field, $this->objectValueMap)) {
                $this->{$field} = $child['value'];
            }
        }
    }

    /**
     * @param array $children
     */
    final protected function attachKeyValues($children)
    {
        $keyValues = $this->getKeyValueFields($children);

        foreach ($this->getKeyValueMap() as $field) {
            list($field, $fieldType) = [
                array_keys($field)[0],
                array_values($field)[0],
            ];

            if (isset($keyValues['{}'.$field])) {
                $this->{Str::camel($field)} = $this->castPrimitives($keyValues['{}'.$field], $fieldType);
            }
        }
    }

    /**
     * @return array
     */
    final protected function getKeyValueMap()
    {
        return array_map(function ($item) {
            if (!is_array($item)) {
                return [$item => 'string'];
            }

            return $item;
        }, $this->keyValueMap);
    }

    /**
     * @param array $children
     */
    final protected function attachEnums($children)
    {
        foreach ($children as $child) {
            $field = substr($child['name'], 2);
            if (in_array($field, $this->enumMap)) {
                $this->{$field} = $child['value'];
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
                $value = (int) $value;

                break;
            case 'float':
                $value = (float) $value;

                break;
            case 'bool':
                $value = (true === $value || 'true' === $value || '1' === $value || 1 === $value) && (false !== $value || 'false' !== $value || '0' !== $value || 0 !== $value);

                break;
            case 'datetime':
                $value = DateTime::createFromFormat('Y-m-d-\TH:i:sO', $value);

                break;
            default:
                $value = (string) $value;

                break;
        }

        return $value;
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

    /**
     * @param $fields
     *
     * @return ResponseNamespace
     */
    final public function except($fields)
    {
        $fields = is_array($fields) ? $fields : [$fields];

        $processed = clone $this;

        foreach ($fields as $field) {
            if (isset($processed->{$field})) {
                unset($processed->{$field});
            }
        }

        return $processed;
    }
}
