<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Client\Responses\Namespaces;

use Sabre\Xml\Reader;
use Vegfund\Jotta\Client\Responses\ResponseNamespace;

/**
 * Class Folder.
 */
class Folder extends ResponseNamespace
{
    /**
     * @var array
     */
    public $folders = [];

    /**
     * @var array
     */
    public $files = [];

    /**
     * @var array
     */
    protected $keyValueMap = [
        'name',
        ['time' => 'datetime'],
        'host',
        'display_name',
        'type',
        'sid',
        ['size'     => 'int'],
        ['modified' => 'datetime'],
        'user',
        'path',
        'abspath',
    ];

    /**
     * @var array
     */
    protected $enumMap = [
        'folders',
        'files',
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
     * @throws \Sabre\Xml\LibXMLException
     * @throws \Sabre\Xml\ParseException
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
}
