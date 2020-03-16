<?php

namespace Vegfund\Jotta\Tests\Unit\_003_Scopes;

use Vegfund\Jotta\Client\Responses\XmlResponseSerializer;

class Test009_FolderTest extends \PHPUnit\Framework\TestCase
{
    public function test001_delete()
    {
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\Folder::isDeleted
     * @throws \Sabre\Xml\ParseException
     */
    public function test003_is_deleted()
    {
        $body = '<?xml version="1.0" encoding="UTF-8"?>
                <folder name="Dokumenty" deleted="2020-03-16-T13:11:37Z" time="2020-03-16-T13:11:47Z" host="**obfuscated**">
                    <path xml:space="preserve">/**obfuscated**/Jotta/Sync</path>
                    <abspath xml:space="preserve">/**obfuscated**/Jotta/Sync</abspath>
                    <folders>
                        <folder name="Foldername" deleted="2020-03-16-T13:11:37Z">
                            <abspath xml:space="preserve">/**obfuscated**/Jotta/Sync/Foldername</abspath>
                        </folder>
                    </folders>
                    <files>
                    </files>
                </folder>';

        $folder = XmlResponseSerializer::parse($body, 'auto');
        $this->assertTrue($folder->isDeleted());
    }
}
