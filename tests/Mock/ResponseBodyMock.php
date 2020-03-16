<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Tests\Mock;

use Exception;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Sabre\Xml\Service;

/**
 * Class ResponseBodyMock.
 */
class ResponseBodyMock
{
    /**
     * @var Service
     */
    protected $service;

    /**
     * ResponseBodyMock constructor.
     */
    public function __construct()
    {
        $this->service = new Service();
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return string
     */
    public function __call($name, $arguments)
    {
        return $this->error();
    }

    /**
     * @throws Exception
     *
     * @return string
     */
    public function user()
    {
        return $this->write('{}user', [
            '{}username'           => getenv('JOTTA_USERNAME'),
            '{}account-type'       => 'free',
            '{}locked'             => false,
            '{}capacity'           => -1,
            '{}max-devices'        => -1,
            '{}max-mobile-devices' => -1,
            '{}usage'              => rand(1024, 99999999),
            '{}read-locked'        => false,
            '{}write-locked'       => false,
            '{}quora-write-locked' => false,
            '{}enable-sync'        => false,
            '{}enable-foldershare' => true,
            '{}business-role'      => 'ADMIN',
            '{}business-name'      => 'Business name',
            '{}devices'            => [
                [
                    '{}device' => [
                        '{}name'         => 'Jotta',
                        '{}display_name' => 'Jotta',
                        '{}type'         => 'JOTTA',
                        '{}sid'          => Uuid::uuid4()->toString(),
                        '{}size'         => rand(1024, 99999999),
                        '{}modified'     => strftime('%F-T%TZ', time() - rand(0, 60 * 60 * 24 * 365 * 4)),
                    ],
                ],
                [
                    '{}device' => [
                        '{}name'         => 'Flysystem',
                        '{}display_name' => 'Flysystem',
                        '{}type'         => 'CLI',
                        '{}sid'          => Uuid::uuid4()->toString(),
                        '{}size'         => rand(1024, 99999999),
                        '{}modified'     => '',
                    ],
                ],
            ],
        ]);
    }

    /**
     * @throws Exception
     *
     * @return string
     */
    public function device()
    {
        return $this->write('{}device', [
            '{}name'         => 'whatever',
            '{}display_name' => 'whatever',
            '{}sid'          => Uuid::uuid4()->toString(),
            '{}size'         => rand(1024, 99999999),
            '{}modified'     => strftime('%F-T%TZ', time() - rand(0, 60 * 60 * 24 * 365 * 4)),
            '{}user'         => getenv('JOTTA_USERNAME'),
            '{}mountPoints'  => [
                [
                    '{}mountPoint' => [
                        [
                            '{}name'     => 'Archive',
                            '{}size'     => rand(1024, 99999999),
                            '{}modified' => strftime('%F-T%TZ', time() - rand(0, 60 * 60 * 24 * 365 * 4)),
                        ],
                    ],
                ],
                [
                    '{}mountPoint' => [
                        [
                            '{}name'     => 'Shared',
                            '{}size'     => rand(1024, 99999999),
                            '{}modified' => strftime('%F-T%TZ', time() - rand(0, 60 * 60 * 24 * 365 * 4)),
                        ],
                    ],
                ],
                [
                    '{}mountPoint' => [
                        [
                            '{}name'     => 'Sync',
                            '{}size'     => rand(1024, 99999999),
                            '{}modified' => strftime('%F-T%TZ', time() - rand(0, 60 * 60 * 24 * 365 * 4)),
                        ],
                    ],
                ],
            ],
            [
                'name'       => 'metadata',
                'attributes' => [
                    'first'           => '',
                    'max'             => '',
                    'total'           => '3',
                    'num_mountpoints' => '3',
                ],
            ],
        ]);
    }

    /**
     * @return string
     */
    public function error()
    {
        return $this->write('{}error', [
            '{}code'     => 404,
            '{}message'  => 'Error message',
            '{}reason'   => 'Reason',
            '{}cause'    => 'Cause',
            '{}hostname' => Str::random(24),
            '{}x-id'     => rand(102267900827, 992267900827),
        ]);
    }

    /**
     * @return string
     */
    public function mountPoint()
    {
        return $this->write('{}mountPoint', [
            '{}name'     => 'Archive',
            '{}path'     => 'path',
            '{}abspath'  => 'path',
            '{}size'     => rand(1024, 99999999),
            '{}modified' => strftime('%F-T%TZ', time() - rand(0, 60 * 60 * 24 * 365 * 4)),
            '{}device'   => 'Jotta',
            '{}user'     => getenv('JOTTA_USERNAME'),
            '{}folders'  => [
                [
                    '{}folder' => [
                        'attributes' => [
                            'name' => 'somefolder',
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function file()
    {
        return $this->write('{}file', [
            '{}name' => 'file.txt',
        ]);
    }

    /**
     * @return string
     */
    public function folder()
    {
        return $this->write('{}folder', [
            '{}path'    => 'path',
            '{}abspath' => 'abspath',
            '{}folders' => [
                [
                    '{}folder' => [
                        'attributes' => [
                            'name' => 'somefolder',
                        ],
                    ],
                ],
            ],
            '{}files' => [
                [
                    '{}file' => [
                        ['name'          => 'file',
                            'attributes' => ['a' => 'b'],

                            'abspath' => 'abspath',
                        ],
                    ],
                    //                        '{}currentRevision' => [
                    //                            [
                    //                                '{}number' => 1,
                    //                                '{}state' => 'COMPLETED',
                    //                                '{}created' => strftime('%F-T%TZ', time() - rand(0, 60 * 60 * 24 * 365 * 4)),
                    //                                '{}modified' => strftime('%F-T%TZ', time() - rand(0, 60 * 60 * 24 * 365 * 2)),
                    //                                '{}mime' => 'mime',
                    //                                '{}size' => rand(1024, 99999999),
                    //                                '{}md5' => md5(Str::random(128)),
                    //                                '{}updated' => strftime('%F-T%TZ', time() - rand(0, 60 * 60 * 24 * 365 * 2)),
                    //                            ]
                    //                        ]
                ],
            ],
        ]);
    }

    /**
     * @param $rootElementName
     * @param $data
     *
     * @return string
     */
    protected function write($rootElementName, $data)
    {
        return str_replace(' xmlns=""', '', $this->service->write($rootElementName, $data));
    }
}
