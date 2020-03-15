<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Exceptions;

use GuzzleHttp\Exception\ClientException;

/**
 * Class ClientXmlException.
 */
class ClientXmlException extends \RuntimeException
{
    /**
     * @var \SimpleXMLElement
     */
    protected $xml;

    /**
     * @var ClientException
     */
    protected $exception;

    /**
     * ClientXmlException constructor.
     */
    public function __construct(ClientException $exception)
    {
        $this->xml = simplexml_load_string((string) $exception->getResponse()->getBody());
        $this->exception = $exception;

        parent::__construct($this->getMessage(), $this->getCode());
    }

    /**
     * Gets a string representation of the thrown object.
     *
     * @see https://php.net/manual/en/throwable.tostring.php
     *
     * @return string <p>Returns the string representation of the thrown object.</p>
     *
     * @since 7.0
     */
    public function __toString()
    {
        return $this->getMessage();
    }
}
