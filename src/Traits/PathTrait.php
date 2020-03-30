<?php

namespace Vegfund\Jotta\Traits;

/**
 * Trait PathTrait
 * @package Vegfund\Jotta\Traits
 */
trait PathTrait
{
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

    /**
     * @param $path
     * @return false|string
     */
    protected function getMountPointFromPath($path)
    {
        $path = $this->normalizePathSegment($path);
        return substr($path, 0, strpos($path, '/'));
    }

    /**
     * @param $path
     * @return false|string
     */
    protected function stripMountPointFromPath($path)
    {
        $path = $this->normalizePathSegment($path);
        return substr($path, strpos($path, '/') + 1);
    }
}