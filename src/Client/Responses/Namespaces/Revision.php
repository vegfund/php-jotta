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

/**
 * Class CurrentRevision.
 */
class Revision extends ResponseNamespace
{
    /**
     * @var string
     */
    public $state;

    /**
     * @var array
     */
    protected $keyValueMap = [
        ['number' => 'int'],
        'state',
        ['created'  => 'datetime'],
        ['modified' => 'datetime'],
        'mime',
        ['size' => 'int'],
        'md5',
        ['updated' => 'datetime'],
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
}
