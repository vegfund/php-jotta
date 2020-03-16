<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Responses;

use function Sabre\Xml\Deserializer\repeatingElements;
use Sabre\Xml\Reader;
use Vegfund\Jotta\Client\Responses\Namespaces\CurrentRevision;
use Vegfund\Jotta\Client\Responses\Namespaces\Device;
use Vegfund\Jotta\Client\Responses\Namespaces\File;
use Vegfund\Jotta\Client\Responses\Namespaces\Folder;
use Vegfund\Jotta\Client\Responses\Namespaces\Metadata;
use Vegfund\Jotta\Client\Responses\Namespaces\MountPoint;
use Vegfund\Jotta\Client\Responses\Namespaces\User;

/**
 * Class ElementMapper.
 *
 * @todo Maybe move definitions to namespace classes?
 */
class ElementMapper
{
    /**
     * @param $namespace
     *
     * @return mixed
     */
    public function getNms($namespace)
    {
        return \call_user_func([$this, $namespace]);
    }

    /**
     * @param $namespace
     *
     * @return mixed
     */
    public static function nms($namespace)
    {
        return (new static())->getNms($namespace);
    }

    /**
     * @return array
     */
    protected function user()
    {
        return [
            '{}user'    => User::class,
            '{}devices' => function (Reader $reader) {
                return repeatingElements($reader, '{}device');
            },
            '{}device' => Device::class,
        ];
    }

    /**
     * @return array
     */
    protected function device()
    {
        return [
            '{}device'      => Device::class,
            '{}mountPoints' => function (Reader $reader) {
                return repeatingElements($reader, '{}mountPoint');
            },
            '{}mountPoint' => MountPoint::class,
        ];
    }

    /**
     * @return array
     */
    protected function mountPoint()
    {
        return [
            '{}mountPoint' => MountPoint::class,
            '{}folders'    => function (Reader $reader) {
                return repeatingElements($reader, '{}folder');
            },
            '{}folder' => Folder::class,
            '{}files'  => function (Reader $reader) {
                return repeatingElements($reader, '{}file');
            },
            '{}file'     => File::class,
            '{}metadata' => Metadata::class,
        ];
    }

    /**
     * @return array
     */
    protected function folder()
    {
        return [
            '{}folders' => function (Reader $reader) {
                return repeatingElements($reader, '{}folder');
            },
            '{}folder' => Folder::class,
            '{}files'  => function (Reader $reader) {
                return repeatingElements($reader, '{}file');
            },
            '{}file'     => File::class,
            '{}metadata' => Metadata::class,
        ];
    }

    /**
     * @return array
     */
    protected function file()
    {
        return [
            '{}file'            => File::class,
            '{}currentRevision' => CurrentRevision::class,
            '{}latestRevision'  => CurrentRevision::class,
            '{}metadata'        => Metadata::class,
        ];
    }

    protected function metadata()
    {
        return [
            '{}metadata' => Metadata::class,
        ];
    }

    /**
     * @return array
     */
    protected function currentRevision()
    {
        return [
            '{}currentRevision' => CurrentRevision::class,
        ];
    }
}
