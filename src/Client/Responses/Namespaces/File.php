<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Responses\Namespaces;

use Sabre\Xml\LibXMLException;
use Sabre\Xml\ParseException;
use Sabre\Xml\Reader;
use Vegfund\Jotta\Client\Contracts\NamespaceContract;
use Vegfund\Jotta\Client\Responses\ResponseNamespace;
use Vegfund\Jotta\Support\JFileInfo;

/**
 * @todo Attach CurrentRevision/LatestRevision
 * Class File.
 */
class File extends ResponseNamespace
{
    public $name;
    public $abspath;
    public $path;
    public $host;
    /**
     * @var CurrentRevision
     */
    public $currentRevision;
    public $latestRevision;
    /**
     * @var array
     */
    protected $keyValueMap = [
        'name',
        'uuid',
        'path',
        'abspath',
        'host',
    ];

    protected $objectValueMap = [
        'currentRevision',
        'latestRevision',
        'metadata'
    ];

    /**
     * The deserialize method is called during xml parsing.
     *
     * This method is called statically, this is because in theory this method
     * may be used as a type of constructor, or factory method.
     *
     * Often you want to return an instance of the current class, but you are
     * free to return other data as well.
     *
     * You are responsible for advancing the reader to the next element. Not
     * doing anything will result in a never-ending loop.
     *
     * If you just want to skip parsing for this element altogether, you can
     * just call $reader->next();
     *
     * $reader->parseInnerTree() will parse the entire sub-tree, and advance to
     * the next element.
     *
     * @throws LibXMLException
     * @throws ParseException
     *
     * @return mixed
     */
    public static function xmlDeserialize(Reader $reader)
    {
        return (new self())->attachFields($reader);
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return isset($this->getAttributes()->deleted) && $this->getAttributes()->deleted;
    }

    /**
     * @throws LibXMLException
     * @throws ParseException
     *
     * @return bool
     */
    public function isCorrupt()
    {
        if (!($this->currentRevision instanceof NamespaceContract)) {
            $currentRevision = (new CurrentRevision())->fill($this->currentRevision);
        } else {
            $currentRevision = $this->currentRevision;
        }

        if (!($this->latestRevision instanceof NamespaceContract)) {
            $latestRevision = (new CurrentRevision())->fill($this->latestRevision);
        } else {
            $latestRevision = $this->latestRevision;
        }

        return 'CORRUPT' === $latestRevision->state || 'CORRUPT' === $currentRevision->state;
    }

    /**
     * @throws LibXMLException
     * @throws ParseException
     *
     * @return bool
     */
    public function isCompleted()
    {
        if (!($this->currentRevision instanceof NamespaceContract)) {
            $currentRevision = (new CurrentRevision())->fill($this->currentRevision);
        } else {
            $currentRevision = $this->currentRevision;
        }

        if (!($this->latestRevision instanceof NamespaceContract)) {
            $latestRevision = (new CurrentRevision())->fill($this->latestRevision);
        } else {
            $latestRevision = $this->latestRevision;
        }

        return 'COMPLETED' === $latestRevision->state || 'COMPLETED' === $currentRevision->state;
    }

    /**
     * @throws LibXMLException
     * @throws ParseException
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->isCompleted() && !$this->isCorrupt() && !$this->isDeleted();
    }

    /**
     * @param $file
     *
     * @return bool
     */
    public function isNewerThan($file)
    {
        if (is_string($file)) {
            $file = new JFileInfo($file);
        }
        if ($file instanceof \SplFileInfo && !($file instanceof JFileInfo)) {
            $file = new JFileInfo($file->getRealPath());
        }

        return $this->currentRevision->modified->getTimestamp() >= $file->getMTime();
    }

    /**
     * @param $file
     *
     * @return bool
     */
    public function isDifferentThan($file)
    {
        if (is_string($file)) {
            $file = new JFileInfo($file);
        }
        if ($file instanceof \SplFileInfo && !($file instanceof JFileInfo)) {
            $file = new JFileInfo($file->getRealPath());
        }

        return $this->currentRevision->md5 !== $file->getMd5();
    }

    /**
     * @param $file
     *
     *@throws ParseException
     * @throws LibXMLException
     *
     * @return bool
     */
    public function isSameAs($file)
    {
        if (is_string($file)) {
            $file = new JFileInfo($file);
        }
        if ($file instanceof \SplFileInfo && !($file instanceof JFileInfo)) {
            $file = new JFileInfo($file->getRealPath());
        }

        return $this->isValid() && !$this->isNewerThan($file) && !$this->isDifferentThan($file);
    }
}
