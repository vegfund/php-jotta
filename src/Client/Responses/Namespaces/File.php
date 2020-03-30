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
    const STATE_COMPLETED = 'COMPLETED';

    const STATE_CORRUPT = 'CORRUPT';

    const STATE_INCOMPLETE = 'INCOMPLETE';

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

    /**
     * @var CurrentRevision
     */
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
        try {
            return self::STATE_CORRUPT === $this->getRevision()->state;
        } catch (\Exception $e) {
            var_dump($this); die();
        }
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        return self::STATE_COMPLETED === $this->getRevision()->state;
    }

    /**
     * @return bool
     */
    public function isIncomplete()
    {
        return self::STATE_INCOMPLETE === $this->getRevision()->state;
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
        return $this->getRevision()->modified->getTimestamp() > JFileInfo::make($file)->getMTime();
    }

    /**
     * @param $file
     *
     * @return bool
     */
    public function isDifferentThan($file)
    {
        return ($this->getRevision()->getSize() !== JFileInfo::make($file)->getSize()) || ($this->getRevision()->md5 !== JFileInfo::make($file)->getMd5());
    }

    /**
     * @param $file
     *
     * @return bool
     */
    public function isSameAs($file)
    {
        $file = JFileInfo::make($file);

        return $this->isValid() && !$this->isDifferentThan($file);
    }

    /**
     * @return null|string
     */
    public function getMd5()
    {
        return $this->getRevision()->getMd5();
    }

    /**
     * @return null|int
     */
    public function getSize()
    {
        return $this->getRevision()->getSize();
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
