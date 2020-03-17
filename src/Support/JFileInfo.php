<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Support;

use SplFileInfo;

/**
 * Class JFileInfo.
 */
class JFileInfo extends SplFileInfo
{
    /**
     * @param $file
     * @return static
     */
    public static function make($file)
    {
        if ($file instanceof self) {
            return $file;
        }

        if ($file instanceof SplFileInfo) {
            return new static($file->getRealPath());
        }

        return new static($file);
    }

    /**
     * @return string
     */
    public function getMd5()
    {
        return md5(file_get_contents($this->getRealPath()));
    }
}
