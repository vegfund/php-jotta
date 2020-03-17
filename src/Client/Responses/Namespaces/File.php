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
use Vegfund\Jotta\Client\Responses\ResponseNamespace;
use Vegfund\Jotta\Support\JFileInfo;

/**
 * @todo Attach CurrentRevision/LatestRevision
 * Class File.
 */
class File extends ResponseNamespace
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $abspath;

    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
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

    /**
     * @var array
     */
    protected $objectValueMap = [
        'currentRevision',
        'latestRevision',
        'metadata',
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
        return null !== $this->getAttribute('deleted');
    }

    /**
     * @return bool
     */
    public function isCorrupt()
    {
        return 'CORRUPT' === $this->getRevision()->state || 'CORRUPT' === $this->getRevision()->state;
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        return 'COMPLETED' === $this->getRevision()->state || 'COMPLETED' === $this->getRevision()->state;
    }

    /**
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
        return $this->getRevision()->modified->getTimestamp() >= $file->getMTime();
    }

    /**
     * @param $file
     *
     * @return bool
     */
    public function isDifferentThan($file)
    {
        return $this->getRevision()->md5 !== JFileInfo::make($file)->getMd5();
    }

    /**
     * @param $file
     *
     * @return bool
     */
    public function isSameAs($file)
    {
        $file = JFileInfo::make($file);

        return $this->isValid() && !$this->isNewerThan($file) && !$this->isDifferentThan($file);
    }

    /**
     * @return CurrentRevision
     */
    protected function getRevision()
    {
        $revision = $this->currentRevision;
        if (null !== $this->latestRevision) {
            $revision = $this->latestRevision;
        }

        return $revision;
    }
}
