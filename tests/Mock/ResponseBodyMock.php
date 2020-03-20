<?php

/*
 * This file is a part of the PHP client for unofficial Jottacloud API, with a built-in Flysystem adapter.
 *
 * @author Marek Kapusta <fundacja@vegvisir.org.pl>
 */

namespace Vegfund\Jotta\Tests\Mock;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Sabre\Xml\Service;
use Vegfund\Jotta\Jotta;

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
    public function mountPoint($options = [])
    {
        $definitions = [
            '{}name'     => Arr::get($options, 'name', Jotta::MOUNT_POINT_ARCHIVE),
            '{}path'     => '/'.Arr::get($options, 'username', getenv('JOTTA_USERNAME')).'/Jotta',
            '{}abspath'  => '/'.Arr::get($options, 'username', getenv('JOTTA_USERNAME')).'/Jotta',
            '{}size'     => Arr::get($options, 'size', rand(1024, 99999999)),
            '{}modified' => strftime('%F-T%TZ', Arr::get($options, 'modified', time() - rand(0, 60 * 60 * 24 * 365 * 4))),
            '{}device'   => 'Jotta',
            '{}user'     => getenv('JOTTA_USERNAME'),
            //            '{}folders'  => [
            //                [
            //                    '{}folder' => [
            //                        'attributes' => [
            //                            'name' => 'somefolder',
            //                        ],
            //                    ],
            //                ],
            //            ],
        ];

        $folders = Arr::get($options, 'folders', []);
        if (count($folders) > 0) {
            $definitions['{}folders'] = [];
        }
        foreach ($folders as $folder) {
            $attributes = [
                'name' => $folder['name'],
            ];
            if (isset($folder['deleted'])) {
                $attributes['deleted'] = strftime('%F-T%TZ', $folder['deleted']);
            }
            $definitions['{}folders'][] = [
                [
                    'name'       => '{}folder',
                    'attributes' => $attributes,
                    'value'      => [
                        '{}abspath' => '/'.Arr::get($options, 'username', getenv('JOTTA_USERNAME')).'/Jotta/'.Arr::get($options, 'name', Jotta::MOUNT_POINT_ARCHIVE),
                    ],
                ],
            ];
        }

        $files = Arr::get($options, 'files', []);
        if (count($files) > 0) {
            $definitions['{}files'] = [];
        }
        foreach ($files as $file) {
            $attributes = [
                'name' => $file['name'],
                'uuid' => Arr::get($file, 'uuid', Uuid::uuid4()->toString()),
            ];
            if (isset($file['deleted'])) {
                $attributes['deleted'] = strftime('%F-T%TZ', $file['deleted']);
            }
            $definitions['{}files'][] = [
                [
                    'name'       => '{}file',
                    'attributes' => $attributes,
                    'value'      => [
                        '{}abspath'         => '/'.Arr::get($options, 'username', getenv('JOTTA_USERNAME')).'/Jotta/'.Arr::get($options, 'name', Jotta::MOUNT_POINT_ARCHIVE),
                        '{}currentRevision' => [
                            'number'   => 1,
                            'state'    => Arr::get($file, 'state', 'COMPLETED'),
                            'created'  => strftime('%F-T%TZ', Arr::get($file, 'crated', time() - 60 * 60)),
                            'modified' => strftime('%F-T%TZ', Arr::get($file, 'crated', time() - 60 * 60)),
                            'mime'     => Arr::get($file, 'mime', 'text/plain'),
                            'size'     => Arr::get($file, 'size', strlen($file['name']) * 1024),
                            'md5'      => Arr::get($file, 'md5', md5($file['name'])),
                            'updated'  => strftime('%F-T%TZ', Arr::get($file, 'crated', time() - 60)),
                        ],
                    ],
                ],
            ];
        }

        $definitions['{}metadata'] = [
            'name'       => '{}metadata',
            'attributes' => [
                'first'      => '',
                'max'        => '',
                'total'      => (string) (count($files) + count($folders)),
                'num_folder' => (string) count($folders),
                'num_files'  => (string) count($files),
            ],
        ];

        return $this->write('{}mountPoint', $definitions);
    }

    /**
     * @return string
     */
    public function file()
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
            <file name="filename" uuid="948ececd-edcb-4127-a4e5-66157ac9dac0" deleted="2020-03-16-T13:11:37Z" time="2020-03-16-T13:41:03Z" host="host-name">
                <path xml:space="preserve">/**username**/Jotta/Sync/Dokumenty</path>
                <abspath xml:space="preserve">/**username**/Jotta/Sync/Dokumenty</abspath>
                <currentRevision>
                    <number>1</number>
                    <state>COMPLETED</state>
                    <created>2020-02-21-T17:47:54Z</created>
                    <modified>2020-02-21-T17:47:54Z</modified>
                    <mime>application/octet-stream</mime>
                    <size>3402</size>
                    <md5>9fc9f50b0a9a09280e8ed1f6fa34a31a</md5>
                    <updated>2020-03-08-T18:30:13Z</updated>
                </currentRevision>
            </file>';
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
