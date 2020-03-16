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
 * Class User.
 *
 * @method Device[] getDevices()
 */
class User extends ResponseNamespace
{
    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $accountType;

    /**
     * @var int
     */
    public $maxDevices;

    /**
     * @var int
     */
    public $maxMobileDevices;

    /**
     * @var bool
     */
    public $locked;

    /**
     * @var int
     */
    public $usage;

    /**
     * @var int
     */
    public $capacity;

    /**
     * @var bool
     */
    public $readLocked;

    /**
     * @var bool
     */
    public $writeLocked;

    /**
     * @var bool
     */
    public $quotaWriteLocked;

    /**
     * @var bool
     */
    public $enableSync;

    /**
     * @var bool
     */
    public $enableFoldershare;

    /**
     * @var string
     */
    public $businessRole;

    /**
     * @var string
     */
    public $businessName;

    /**
     * @var array
     */
    public $devices = [];

    /**
     * @var array
     */
    protected $keyValueMap = [
        'username',
        'account-type',
        ['capacity'           => 'int'],
        ['locked' => 'bool'],
        ['max-devices'        => 'int'],
        ['max-mobile-devices' => 'int'],
        ['capacity'           => 'int'],
        ['usage'              => 'int'],
        ['read-locked'        => 'bool'],
        ['write-locked'       => 'bool'],
        ['quota-write-locked' => 'bool'],
        ['enable-sync'        => 'bool'],
        ['enable-foldershare' => 'bool'],
        'business-role',
        'business-name',
    ];

    /**
     * @var array
     */
    protected $enumMap = [
        'devices',
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
