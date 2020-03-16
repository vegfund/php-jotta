<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta;

use Vegfund\Jotta\Client\Scopes\AccountScope;
use Vegfund\Jotta\Client\Scopes\DeviceScope;
use Vegfund\Jotta\Client\Scopes\FileScope;
use Vegfund\Jotta\Client\Scopes\FolderScope;
use Vegfund\Jotta\Client\Scopes\MountPointScope;

/**
 * Class Jotta. A Gateway for most API calls.
 */
class Jotta
{
    /**
     * Device Jotta.
     */
    const DEVICE_JOTTA = 'Jotta';

    /**
     * Mount point archive.
     */
    const MOUNT_POINT_ARCHIVE = 'Archive';

    /**
     * Mount point shared.
     */
    const MOUNT_POINT_SHARED = 'Shared';

    /**
     * Mount point sync.
     */
    const MOUNT_POINT_SYNC = 'Sync';

    /**
     * API url for filesystem.
     */
    const API_BASE_URL = 'https://jottacloud.com/jfs';

    /**
     * API url for uploads.
     */
    const API_UPLOAD_URL = 'https://up.jottacloud.com/jfs';

    const FILE_OVERWRITE_ALWAYS = 1;

    const FILE_OVERWRITE_NEVER = 2;

    const FILE_OVERWRITE_IF_NEWER = 3;

    const FILE_OVERWRITE_IF_DIFFERENT = 4;

    const FILE_OVERWRITE_IF_NEWER_OR_DIFFERENT = 5;

    const THUMBNAIL_SIZE_SMALL = 'WS';

    const THUMBNAIL_SIZE_MEDIUM = 'WM';

    const THUMBNAIL_SIZE_LARGE = 'WL';

    const THUMBNAIL_SIZE_EXTRA_LARGE = 'WXL';

    /**
     * @var JottaClient
     */
    protected $client;

    /**
     * Jotta constructor.
     *
     * @param string $username jottacloud username
     * @param string $password jottacloud password
     */
    public function __construct($username, $password)
    {
        $this->client = new JottaClient($username, $password);
    }

    /**
     * Get the Jottacloud client.
     *
     * @return JottaClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get the Jottacloud client.
     *
     * @param string $username jottacloud username
     * @param string $password jottacloud password
     *
     * @return JottaClient
     */
    public static function client($username, $password)
    {
        return (new static($username, $password))->getClient();
    }

    /**
     * Get the Account scope.
     *
     * @param string $username jottacloud username
     * @param string $password jottacloud password
     *
     * @param array $options
     * @return AccountScope
     */
    public static function account($username, $password, $options = [])
    {
        return self::client($username, $password)->account($options);
    }

    /**
     * Get the Device scope.
     *
     * @param string $username jottacloud username
     * @param string $password jottacloud password
     *
     * @param array $options
     * @return DeviceScope
     */
    public static function device($username, $password, $options = [])
    {
        return self::client($username, $password)->device($options);
    }

    /**
     * Get the File scope.
     *
     * @param string $username jottacloud username
     * @param string $password jottacloud password
     *
     * @param array $options
     * @return FileScope
     */
    public static function file($username, $password, $options = [])
    {
        return self::client($username, $password)->file($options);
    }

    /**
     * Get the Folder scope.
     *
     * @param string $username jottacloud username
     * @param string $password jottacloud password
     *
     * @param array $options
     * @return FolderScope
     */
    public static function folder($username, $password, $options = [])
    {
        return self::client($username, $password)->folder($options);
    }

    /**
     * Get the MountPoint scope.
     *
     * @param string $username jottacloud username
     * @param string $password jottacloud password
     *
     * @param array $options
     * @return MountPointScope
     */
    public static function mountPoint($username, $password, $options = [])
    {
        return self::client($username, $password)->mountPoint($options);
    }
}
