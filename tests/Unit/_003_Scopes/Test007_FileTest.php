<?php

namespace Vegfund\Jotta\Tests\Unit\_003_Scopes;

use Illuminate\Support\Str;
use Vegfund\Jotta\Client\Responses\Namespaces\CurrentRevision;
use Vegfund\Jotta\Client\Responses\Namespaces\File;
use Vegfund\Jotta\Client\Responses\XmlResponseSerializer;
use Vegfund\Jotta\Support\JFileInfo;

class Test007_FileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isDeleted
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isCompleted
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isValid
     *
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
     *
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

    /**
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isCorrupt
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isCompleted
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isDeleted
     *
     * @throws \Sabre\Xml\ParseException
     */
    public function test005_is_completed()
    {
        $body = '<?xml version="1.0" encoding="UTF-8"?>
                <file name="InSudFKKxbn4_test.txt" uuid="**obfuscated**" time="2020-03-16-T12:23:58Z" host="**obfuscated**">
                    <path xml:space="preserve">/**obfuscated**/Jotta/Archive</path>
                    <abspath xml:space="preserve">/**obfuscated**/Jotta/Archive</abspath>
                    <latestRevision>
                        <number>1</number>
                        <state>COMPLETED</state>
                        <created>2020-03-10-T17:02:49Z</created>
                        <modified>2020-03-10-T17:02:49Z</modified>
                        <mime>text/plain</mime>
                        <md5>e3bc508cc0f25ed6b86089f0b6e09972</md5>
                        <updated>2020-03-10-T17:02:49Z</updated>
                    </latestRevision>
                </file>';

        $file = XmlResponseSerializer::parse($body, 'file');

        $this->assertFalse($file->isCorrupt());
        $this->assertTrue($file->isCompleted());
        $this->assertFalse($file->isDeleted());
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isValid
     *
     * @throws \Sabre\Xml\ParseException
     */
    public function test007_is_valid()
    {
        $body = '<?xml version="1.0" encoding="UTF-8"?>
                <file name="InSudFKKxbn4_test.txt" uuid="**obfuscated**" time="2020-03-16-T12:23:58Z" host="**obfuscated**">
                    <path xml:space="preserve">/**obfuscated**/Jotta/Archive</path>
                    <abspath xml:space="preserve">/**obfuscated**/Jotta/Archive</abspath>
                    <latestRevision>
                        <number>1</number>
                        <state>COMPLETED</state>
                        <created>2020-03-10-T17:02:49Z</created>
                        <modified>2020-03-10-T17:02:49Z</modified>
                        <mime>text/plain</mime>
                        <md5>e3bc508cc0f25ed6b86089f0b6e09972</md5>
                        <updated>2020-03-10-T17:02:49Z</updated>
                    </latestRevision>
                </file>';

        $file = XmlResponseSerializer::parse($body, 'file');

        $this->assertTrue($file->isValid());
    }

    /**
     * @throws \Sabre\Xml\ParseException
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isNewerThan
     */
    public function test009_is_newer()
    {
        $body = '<?xml version="1.0" encoding="UTF-8"?>
                <file name="InSudFKKxbn4_test.txt" uuid="**obfuscated**" time="2020-01-16-T12:23:58Z" host="**obfuscated**">
                    <path xml:space="preserve">/**obfuscated**/Jotta/Archive</path>
                    <abspath xml:space="preserve">/**obfuscated**/Jotta/Archive</abspath>
                    <currentRevision>
                        <number>1</number>
                        <state>COMPLETED</state>
                        <created>2020-03-10-T17:02:49Z</created>
                        <modified>2020-03-10-T17:02:49Z</modified>
                        <mime>text/plain</mime>
                        <md5>e3bc508cc0f25ed6b86089f0b6e09972</md5>
                        <updated>2020-03-10-T17:02:49Z</updated>
                    </currentRevision>
                </file>';

        $remoteFile = XmlResponseSerializer::parse($body, 'auto');

        $localFileMock = \Mockery::mock(JFileInfo::class);
        $localFileMock->shouldReceive('getMTime')->andReturn(time() / 2);
        $this->assertTrue($remoteFile->isNewerThan($localFileMock));

        $localFileMock = \Mockery::mock(JFileInfo::class);
        $localFileMock->shouldReceive('getMTime')->andReturn(time());
        $this->assertFalse($remoteFile->isNewerThan($localFileMock));
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::isDifferentThan
     *
     * @throws \Sabre\Xml\ParseException
     */
    public function test011_is_different()
    {
        $body = '<?xml version="1.0" encoding="UTF-8"?>
                <file name="InSudFKKxbn4_test.txt" uuid="**obfuscated**" time="2020-01-16-T12:23:58Z" host="**obfuscated**">
                    <path xml:space="preserve">/**obfuscated**/Jotta/Archive</path>
                    <abspath xml:space="preserve">/**obfuscated**/Jotta/Archive</abspath>
                    <currentRevision>
                        <number>1</number>
                        <state>COMPLETED</state>
                        <created>2020-03-10-T17:02:49Z</created>
                        <modified>2020-03-10-T17:02:49Z</modified>
                        <mime>text/plain</mime>
                        <md5>e3bc508cc0f25ed6b86089f0b6e09972</md5>
                        <updated>2020-03-10-T17:02:49Z</updated>
                    </currentRevision>
                </file>';

        $remoteFile = XmlResponseSerializer::parse($body, 'auto');

        $localFileMock = \Mockery::mock(JFileInfo::class);
        $localFileMock->shouldReceive('getMd5')->andReturn(md5(Str::random(128)));
        $localFileMock->shouldReceive('getMTime')->andReturn(\DateTime::createFromFormat('Y-m-d-\TH:i:sO', '2020-03-10-T17:02:49Z')->getTimestamp());
        $this->assertTrue($remoteFile->isDifferentThan($localFileMock));
        $this->assertFalse($remoteFile->isSameAs($localFileMock));

        $body = '<?xml version="1.0" encoding="UTF-8"?>
                <file name="InSudFKKxbn4_test.txt" uuid="**obfuscated**" time="2020-01-16-T12:23:58Z" host="**obfuscated**">
                    <path xml:space="preserve">/**obfuscated**/Jotta/Archive</path>
                    <abspath xml:space="preserve">/**obfuscated**/Jotta/Archive</abspath>
                    <currentRevision>
                        <number>1</number>
                        <state>COMPLETED</state>
                        <created>2020-03-10-T17:02:49Z</created>
                        <modified>2020-03-10-T17:02:49Z</modified>
                        <mime>text/plain</mime>
                        <md5>e3bc508cc0f25ed6b86089f0b6e09972</md5>
                        <updated>2020-03-10-T17:02:49Z</updated>
                    </currentRevision>
                </file>';

        $remoteFile = XmlResponseSerializer::parse($body, 'auto');

        $localFileMock = \Mockery::mock(JFileInfo::class);
        $localFileMock->shouldReceive('getMd5')->andReturn('e3bc508cc0f25ed6b86089f0b6e09972');
        $localFileMock->shouldReceive('getMTime')->andReturn(\DateTime::createFromFormat('Y-m-d-\TH:i:sO', '2020-03-10-T17:02:49Z')->getTimestamp());
        $this->assertFalse($remoteFile->isDifferentThan($localFileMock));
        $this->assertTrue($remoteFile->isSameAs($localFileMock));
    }

    /**
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::getAttribute
     * @covers \Vegfund\Jotta\Client\Responses\Namespaces\File::__call
     *
     * @throws \Sabre\Xml\ParseException
     */
    public function test015_get()
    {
        $body = '<?xml version="1.0" encoding="UTF-8"?>
                <file name="InSudFKKxbn4_test.txt" uuid="**obfuscated**" time="2020-03-16-T12:23:58Z" host="**obfuscated**">
                    <path xml:space="preserve">/**obfuscated**/Jotta/Archive</path>
                    <abspath xml:space="preserve">/**obfuscated**/Jotta/Archive</abspath>
                    <currentRevision>
                        <number>1</number>
                        <state>COMPLETED</state>
                        <created>2020-03-10-T17:02:49Z</created>
                        <modified>2020-03-10-T17:02:49Z</modified>
                        <mime>text/plain</mime>
                        <md5>e3bc508cc0f25ed6b86089f0b6e09972</md5>
                        <updated>2020-03-10-T17:02:49Z</updated>
                    </currentRevision>
                </file>';

        $file = XmlResponseSerializer::parse($body, 'file');

        $this->assertInstanceOf(File::class, $file);
        $this->assertSame('**obfuscated**', $file->getAttribute('host'));
        $this->assertSame('**obfuscated**', $file->getAttribute('uuid'));
        $this->assertInstanceOf(CurrentRevision::class, $file->getCurrentRevision());
        $this->assertIsInt($file->getCurrentRevision()->number);
        $this->assertInstanceOf(\DateTime::class, $file->getCurrentRevision()->getUpdated());
        $this->assertSame(\DateTime::createFromFormat('Y-m-d-\TH:i:sO', '2020-03-10-T17:02:49Z')->getTimestamp(), $file->getCurrentRevision()->getUpdated()->getTimestamp());
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
