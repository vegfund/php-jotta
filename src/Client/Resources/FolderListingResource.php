<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Resources;

class FolderListingResource extends AbstractResource
{
    public function arrayDefinition()
    {
        if (isset($this->resource->folders)) {
            $folders = array_filter($this->folders, function ($item) {
                return !isset($item->getAttributes()->deleted);
            });

            $folders = array_map(
                function ($item) {
                    $item['type'] = 'folder';

                    return $item;
                },
                $folders
            );
        } else {
            $folders = [];
        }

        if (isset($this->resource->files)) {
            $files = array_filter($this->files, function ($item) {
                return !isset($item->getAttributes()->deleted);
            });

            $files = array_map(
                function ($item) {
                    $item['type'] = 'file';

                    return $item;
                },
                $files
            );
        } else {
            $files = [];
        }

        return array_merge($folders, $files);
    }
}
