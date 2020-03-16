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
     * JFileInfo constructor.
     * @param $file_name
     */
    public function __construct($file_name)
    {
        if($file_name instanceof \SplFileInfo) {
            $file_name = $file_name->getRealPath();
        }
        parent::__construct($file_name);
    }

    /**
     * @return string
     */
    public function getMd5()
    {
        return md5(file_get_contents($this->getRealPath()));
    }
}
