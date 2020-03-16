<?php

namespace Vegfund\Jotta\Tests\Unit\_003_Scopes;

use Vegfund\Jotta\Client\Responses\XmlResponseSerializer;

class Test007_FileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isDeleted
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isCompleted
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isValid
     * @throws \Sabre\Xml\ParseException
     */
    public function test001_is_deleted()
    {
        $body = '<?xml version="1.0" encoding="UTF-8"?>
                <file name="filename.txt" uuid="**obfuscated**" deleted="2020-03-16-T12:26:38Z" time="2020-03-16-T12:26:51Z" host="**obfuscated**">
                    <path xml:space="preserve">/**obfuscated**/Jotta/Sync</path>
                    <abspath xml:space="preserve">/**obfuscated**/Jotta/Sync</abspath>
                    <currentRevision>
                        <number>1</number>
                        <state>COMPLETED</state>
                        <created>2019-12-06-T18:57:56Z</created>
                        <modified>2019-12-06-T18:57:56Z</modified>
                        <mime>application/vnd.oasis.opendocument.spreadsheet-template</mime>
                        <size>40773</size>
                        <md5>0fef4bc5598f857901ed1e73bf5babd1</md5>
                        <updated>2020-03-04-T20:38:04Z</updated>
                    </currentRevision>
                </file>';

        $file = XmlResponseSerializer::parse($body, 'file');

        $this->assertTrue($file->isDeleted());
        $this->assertTrue($file->isCompleted());
        $this->assertFalse($file->isValid());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isCorrupt
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isCompleted
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isValid
     * @throws \Sabre\Xml\ParseException
     */
    public function test003_is_corrupt()
    {
        $body = '<?xml version="1.0" encoding="UTF-8"?>
                <file name="InSudFKKxbn4_test.txt" uuid="**obfuscated**" time="2020-03-16-T12:23:58Z" host="**obfuscated**">
                    <path xml:space="preserve">/**obfuscated**/Jotta/Archive</path>
                    <abspath xml:space="preserve">/**obfuscated**/Jotta/Archive</abspath>
                    <latestRevision>
                        <number>1</number>
                        <state>CORRUPT</state>
                        <created>2020-03-10-T17:02:49Z</created>
                        <modified>2020-03-10-T17:02:49Z</modified>
                        <mime>text/plain</mime>
                        <md5>e3bc508cc0f25ed6b86089f0b6e09972</md5>
                        <updated>2020-03-10-T17:02:49Z</updated>
                    </latestRevision>
                </file>';

        $file = XmlResponseSerializer::parse($body, 'file');

        $this->assertTrue($file->isCorrupt());
        $this->assertFalse($file->isCompleted());
        $this->assertFalse($file->isValid());
    }

    public function test005_is_completed()
    {
    }

    public function test007_is_valid()
    {
    }

    public function test009_is_newer()
    {
    }

    public function test011_is_different()
    {
    }

    public function test013_is_same_as()
    {
    }

    public function test015_get()
    {
    }

    public function test017_download()
    {
    }

    public function test019_thumbnail()
    {
    }

    public function test021_verify()
    {
    }

    public function test023_move_and_rename()
    {
    }

    public function test025_copy()
    {
    }

    public function test026_delete()
    {
    }

    public function test027_restore()
    {
    }

    public function test029_upload_fresh()
    {
    }

    public function test031_upload_never()
    {
    }

    public function test033_upload_always()
    {
    }

    public function test035_upload_if_newer()
    {
    }

    public function test037_upload_if_different()
    {
    }

    public function test039_upload_if_newer_or_different()
    {
    }
}
