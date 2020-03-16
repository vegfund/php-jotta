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
use Vegfund\Jotta\Client\Contracts\NamespaceContract;
use Vegfund\Jotta\Client\Contracts\ScopeContract;
use Vegfund\Jotta\Client\Responses\XmlResponseSerializer;
use Vegfund\Jotta\Jotta;
use Vegfund\Jotta\JottaClient;
use Vegfund\Jotta\Traits\ScopeConfig;

/**
 * Class Scope.
 */
abstract class Scope implements ScopeContract
{
    use ScopeConfig;

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
     * @param JottaClient $client
     * @param string      $mountPoint
     * @param string      $basePath
     * @param string      $device
     */
    public function __construct(JottaClient $client, $mountPoint = Jotta::MOUNT_POINT_ARCHIVE, $basePath = '', $device = Jotta::DEVICE_JOTTA)
    {
        $this->jottaClient = $client;
        $this->username = $client->getUsername();

        $this->setDevice($device)->setMountPoint($mountPoint)->setBasePath($basePath)->setApiUrl();
    }

    /**
     * Parse (or return ResponseInterface) provided data.
     *
     * @param ResponseInterface|Stream|string $body
     * @param mixed                           $namespace
     *
     * @throws Exception
     *
     * @return array|NamespaceContract|object|ResponseInterface|string
     */
    final public function serialize($body, $namespace = 'auto')
    {
        if ($body instanceof ResponseInterface) {
            $body = $body->getBody();
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
     * @param null   $async
     *
     * @throws Exception
     *
     * @return null|ResponseInterface
     */
    final protected function request($path, $method = 'get', $headers = [], $clientOptions = [], $async = null)
    {
        if ($async === null || !is_bool($async)) {
            if ($this->requestType === 'auto') {
                $async = false;
            } else {
                $async = $this->requestType === 'async';
            }
        }

        try {
            return $this->getClient()->request($path, $method, $headers, $clientOptions, $async);
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
