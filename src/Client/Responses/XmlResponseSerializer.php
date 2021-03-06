<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Responses;

use DOMDocument;
use Exception;
use GuzzleHttp\Psr7\Stream;
use Sabre\Xml\ParseException;
use Sabre\Xml\Service;

/**
 * Class AbstractResponse.
 */
class XmlResponseSerializer
{
    /**
     * @var Stream|string
     */
    protected $body;

    /**
     * @var array|object|string
     */
    protected $xml;

    /**
     * @var Service
     */
    protected $xmlService;

    /**
     * @var ResponseNamespace
     */
    protected $xmlRoot;

    /**
     * XmlResponseSerializer constructor.
     *
     * @param $body
     * @param $namespace
     *
     * @throws ParseException
     * @throws Exception
     */
    public function __construct($body, $namespace)
    {
        $this->body = (string) $body;

        $namespace = 'auto' === $namespace ? $this->getRootNamespace($body) : $namespace;

        $this->xmlService = $this->getXmlService();
        $this->xmlService->elementMap = ElementMapper::nms($namespace);
        $this->xml = $this->xmlService->parse($this->body);
        $this->xmlRoot = $this->xml;
    }

    /**
     * @param $body
     * @param mixed $namespace
     *
     * @throws ParseException
     *
     * @return array|object|string
     */
    public static function parse($body, $namespace)
    {
        return (new static($body, $namespace))->getParsed();
    }

    /**
     * @return array|object|string
     */
    public function getParsed()
    {
        return $this->xml;
    }

    /**
     * @return string
     */
    public function getRaw()
    {
        return (string) $this->body;
    }

    /**
     * @return Service
     */
    protected function getXmlService()
    {
        return isset($this->xmlService) ? $this->xmlService : new Service();
    }

    /**
     * @param $body
     *
     * @throws Exception
     *
     * @return string
     */
    protected function getRootNamespace($body)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML($body);

        if ('error' === ($nms = $dom->documentElement->tagName)) {
            $domDocument = (new \DOMDocument('1.0', 'UTF-8'));
            $domDocument->loadXML($body);
            $code = $domDocument->getElementsByTagName('code')->item(0)->nodeValue;
            $message = $domDocument->getElementsByTagName('message')->item(0)->nodeValue;

            throw new Exception($message, $code);
        }

        return $nms;
    }
}
