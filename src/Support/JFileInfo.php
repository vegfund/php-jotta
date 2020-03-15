<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Support;

/**
 * Class JFileInfo.
 */
class JFileInfo extends \SplFileInfo
{
    /**
     * @return string
     */
    public function getMd5()
    {
        return md5(file_get_contents($this->getRealPath()));
    }
}
