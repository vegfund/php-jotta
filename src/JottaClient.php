<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta;

use Exception;
use GrahamCampbell\GuzzleFactory\GuzzleFactory;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DependencyInjection\ScopeInterface;
use Vegfund\Jotta\Client\Contracts\ScopeContract;
use Vegfund\Jotta\Client\Exceptions\JottaException;
use Vegfund\Jotta\Client\Responses\Namespaces\Device;
use Vegfund\Jotta\Client\Responses\Namespaces\File;
use Vegfund\Jotta\Client\Responses\Namespaces\MountPoint;
use Vegfund\Jotta\Client\Scopes\AccountScope;
use Vegfund\Jotta\Client\Scopes\DeviceScope;
use Vegfund\Jotta\Client\Scopes\DirectoryScope;
use Vegfund\Jotta\Client\Scopes\FileScope;
use Vegfund\Jotta\Client\Scopes\FolderScope;
use Vegfund\Jotta\Client\Scopes\MountPointScope;
use Vegfund\Jotta\Client\Scopes\Scope;

/**
 * Class JottaClient.
 *
 * @see https://github.com/paaland/node-jfs/blob/master/src/jfsuploader.js
 * @see https://www.jottacloud.com/jfs/user/Jotta/Archive/truking?mvDir=/user/Jotta/Archive/newtruking2
 */
class JottaClient
{
    /**
     * @var string Username
     */
    protected $username;

    /**
     * @var string Password
     */
    protected $password;

    /**
     * @var GuzzleClient Guzzle client
     */
    protected $httpClient;

    /**
     * Client constructor.
     *
     * @param string            $username jottacloud username
     * @param string            $password jottacloud password
     * @param null|GuzzleClient $client   guzzleHttp client
     */
    public function __construct($username, $password, GuzzleClient $client = null)
    {
        $this->username = $username;
        $this->password = $password;

        $this->httpClient = $client ?? new GuzzleClient([
            'handler' => GuzzleFactory::handler(),
        ]);
    }

    /**
     * Get the GuzzleHttp client.
     *
     * @return GuzzleClient
     */
    public function getClient()
    {
        return $this->httpClient;
    }

    /**
     * Get the username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get the Account scope.
     *
     * @param array $options options array
     *
     * @return AccountScope|Scope
     *@throws JottaException
     *
     */
    public function account($options = [])
    {
        return $this->getScope(AccountScope::class, $options);
    }

    /**
     * @param array $options options array
     *
     * @throws JottaException
     *
     * @return DeviceScope|Scope
     */
    public function device($options = [])
    {
        return $this->getScope(DeviceScope::class, $options);
    }

    /**
     * @param array $options options array
     *
     * @throws JottaException
     *
     * @return FileScope|Scope
     */
    public function file($options = [])
    {
        return $this->getScope(FileScope::class, $options);
    }

    /**
     * @param array $options options array
     *
     * @throws JottaException
     *
     * @return DirectoryScope|Scope
     */
    public function folder($options = [])
    {
        return $this->directory($options)->setMode(DirectoryScope::MODE_FOLDER);
    }

    /**
     * @param array $options options array
     *
     * @throws JottaException
     *
     * @return DirectoryScope|Scope
     */
    public function mountPoint($options = [])
    {
        return $this->directory($options)->setMode(DirectoryScope::MODE_MOUNT_POINT);
    }

    /**
     * @param array $options
     * @return DirectoryScope|Scope
     * @throws JottaException
     */
    public function directory($options = [])
    {
        return $this->getScope(DirectoryScope::class, $options);
    }

    /**
     * Perform an HTTP request.
     *
     * @param string $path          API endpoint path
     * @param string $method        HTTP method
     * @param array  $headers       request headers
     * @param mixed  $clientOptions guzzleHttp options array
     * @param mixed  $async         asynchronous request flag
     *
     * @throws Exception
     *
     * @return ResponseInterface
     */
    public function request($path, $method = 'get', $headers = [], $clientOptions = [], $async = false)
    {
        $options = array_merge([
            'headers' => $this->getHeaders($headers),
        ], $clientOptions);

        $requestMethod = $async ? 'requestAsync' : 'request';

        try {
            $response = $this->httpClient->{$requestMethod}($method, $path, $options);
        } catch (ClientException $exception) {
            $this->handleException($exception);
        }

        return $response;
    }

    /**
     * @param string $name    scope name
     * @param mixed  $options scope options array
     *
     * @throws JottaException
     *
     * @return null|Scope|ScopeContract
     */
    public function getScope($name, $options = [])
    {
        if (class_exists($name) && (is_a($name, Scope::class, true))) {
            /**
             * @var Scope
             */
            $scope = (new $name($this));

            if (isset($options['device'])) {
                $scope = $scope->setDevice($options['device']);
            }
            if (isset($options['mount_point'])) {
                $scope = $scope->setMountPoint($options['mount_point']);
            }
            if (isset($options['base_path'])) {
                $scope = $scope->setBasePath($options['base_path']);
            }

            return $scope;
        }

        throw new JottaException('Scope '.$name.' does not exist or not a ScopeContract');
    }

    /**
     * @param ClientException|Exception|RequestException|ServerException $exception exception to handle
     *
     * @throws Exception
     */
    protected function handleException($exception)
    {
        switch (\get_class($exception)) {
            case ClientException::class:
                $body = (string) $exception->getResponse()->getBody();
                $domDocument = (new \DOMDocument('1.0', 'UTF-8'));
                $domDocument->loadXML($body);
                $code = $domDocument->getElementsByTagName('code')->item(0)->nodeValue;
                $message = $domDocument->getElementsByTagName('message')->item(0)->nodeValue;

                throw new Exception($message, $code);
                break;
            default:
                throw $exception;
                break;
        }
    }

    /**
     * Merge HTTP headers.
     *
     * @param array $headers headers to be merged
     *
     * @return array
     */
    protected function getHeaders($headers = [])
    {
        return array_merge([
            'Authorization' => 'Basic '.base64_encode($this->username.':'.$this->password),
        ], $headers);
    }
}
