<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Scopes;

use Exception;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Sabre\Xml\ParseException;
use Vegfund\Jotta\Client\Contracts\NamespaceContract;
use Vegfund\Jotta\Client\Contracts\ScopeContract;
use Vegfund\Jotta\Client\Responses\XmlResponseSerializer;
use Vegfund\Jotta\Jotta;
use Vegfund\Jotta\JottaClient;

/**
 * Class Scope.
 */
abstract class Scope implements ScopeContract
{
    /**
     * @var JottaClient
     */
    protected $jottaClient;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var null|string
     */
    protected $device;

    /**
     * @var null|string
     */
    protected $mountPoint;

    /**
     * @var string
     */
    protected $apiUrl = Jotta::API_BASE_URL;

    /**
     * @var string
     */
    protected $basePath = '';

    /**
     * @var bool
     */
    protected $shouldSerialize = true;

    /**
     * @var bool
     */
    protected $shouldThrowExceptions = true;

    /**
     * @var string
     */
    protected $requestType = 'auto';

    /**
     * Scope constructor.
     *
     * @param string $mountPoint
     * @param string $basePath
     * @param string $device
     */
    public function __construct(JottaClient $client, $mountPoint = Jotta::MOUNT_POINT_ARCHIVE, $basePath = '', $device = Jotta::DEVICE_JOTTA)
    {
        $this->jottaClient = $client;
        $this->username = $client->getUsername();

        $this->setDevice($device)->setMountPoint($mountPoint)->setBasePath($basePath)->setApiUrl();
    }

    /**
     * @param string $username
     *
     * @return Scope
     */
    final public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    final public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $apiUrl
     *
     * @return Scope
     */
    public function setApiUrl($apiUrl = Jotta::API_BASE_URL)
    {
        $this->apiUrl = $apiUrl;

        return $this;
    }

    /**
     * @param string $device
     *
     * @return Scope
     */
    final public function setDevice($device = Jotta::DEVICE_JOTTA)
    {
        $this->device = Jotta::DEVICE_JOTTA;

        return $this;
    }

    /**
     * @return string
     */
    final public function getDevice()
    {
        return $this->device;
    }

    /**
     * @param string $mountPoint
     *
     * @return Scope
     */
    final public function setMountPoint($mountPoint = Jotta::MOUNT_POINT_ARCHIVE)
    {
        $this->mountPoint = $mountPoint;

        return $this;
    }

    /**
     * @return string
     */
    final public function getMountPoint()
    {
        return $this->mountPoint;
    }

    /**
     * @param string $basePath
     *
     * @return $this
     */
    final public function setBasePath($basePath = '')
    {
        $this->basePath = $basePath;

        return $this;
    }

    /**
     * @return string
     */
    final public function getBasePath()
    {
        return $this->basePath;
    }

    final public function setAsync()
    {
        $this->async = true;
    }

    final public function setSync()
    {
        $this->sync = true;
    }

    /**
     * @return $this
     */
    final public function withExceptions()
    {
        $this->shouldThrowExceptions = true;

        return $this;
    }

    /**
     * @return $this
     */
    final public function withoutExceptions()
    {
        $this->shouldThrowExceptions = false;

        return $this;
    }

    /**
     * Parse (or return ResponseInterface) provided data.
     *
     * @param ResponseInterface|Stream|string $body
     * @param mixed                           $namespace
     *
     * @throws Exception
     * @throws ParseException
     *
     * @return array|NamespaceContract|object|ResponseInterface|string
     */
    final public function serialize($body, $namespace = 'auto')
    {
        if (null === $body || false === $body) {
            return $body;
        }

        if ($body instanceof ResponseInterface) {
            $body = $body->getBody();
        }
        if (!$this->shouldSerialize) {
            return (string) $body;
        }

        try {
            return XmlResponseSerializer::parse((string) $body, $namespace);
        } catch (Exception $e) {
            if ($this->shouldThrowExceptions) {
                throw $e;
            }

            return null;
        }
    }

    /**
     * @param $path
     * @param string $method
     * @param array  $headers
     * @param array  $clientOptions
     *
     * @throws Exception
     *
     * @return null|ResponseInterface
     */
    final protected function request($path, $method = 'get', $headers = [], $clientOptions = [])
    {
        try {
            return $this->getClient()->request($path, $method, $headers, $clientOptions, $this->async);
        } catch (Exception $e) {
            if ($this->shouldThrowExceptions) {
                throw new Exception($e->getMessage(), $e->getCode());
            }

            return null;
        }
    }

    /**
     * @return JottaClient
     */
    final protected function getClient()
    {
        return $this->jottaClient;
    }

    /**
     * Get a path for Jottacloud API request call.
     *
     * @param string $apiUrl
     * @param null   $device
     * @param null   $mountPoint
     * @param string $path
     * @param array  $queryParams
     *
     * @return string
     */
    protected function getPath($apiUrl = Jotta::API_BASE_URL, $device = null, $mountPoint = null, $path = '', $queryParams = [])
    {
        $segments = [
            $apiUrl,
            $this->username,
            $device,
            $mountPoint,
            $path,
        ];

        return implode('/', array_filter(array_map(function ($segment) {
            if (null !== $segment && '' !== $segment) {
                return $this->normalizePathSegment($segment);
            }

            return null;
        }, $segments))).(0 === \count($queryParams) ? '' : '?'.http_build_query($queryParams));
    }

    /**
     * Normalize path segment for path generation.
     *
     * @param string $segment
     *
     * @return string
     */
    protected function normalizePathSegment($segment)
    {
        return preg_replace([
            '/^(\\/)*/',
            '/(\\/)*$/',
        ], '', $segment);
    }

    /**
     * @param $path
     *
     * @return string
     */
    protected function getRootPath($path)
    {
        return $this->normalizePathSegment(str_replace(basename($path), '', $path));
    }

    /**
     * @param $path
     *
     * @return string
     */
    protected function getRelativePath($path)
    {
        return $this->normalizePathSegment(str_replace(getcwd(), '', $path));
    }
}
