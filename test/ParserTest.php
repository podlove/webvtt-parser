<?php
use PHPUnit\Framework\TestCase;

use Podlove\Webvtt\Parser;
use Podlove\Webvtt\ParserException;

class ParserTest extends TestCase
{
    public function testEmpty()
    {
        $content = "WEBVTT\n\n";
        $this->assertEquals((new Parser())->parse($content), self::empty_result());
    }

    public function testVariousLineTerminators()
    {
        // CRLF
        $content = "WEBVTT\u{000D}\u{000A}\u{000D}\u{000A}";
        $this->assertEquals((new Parser())->parse($content), self::empty_result());
        // LF
        $content = "WEBVTT\u{000A}\u{000A}";
        $this->assertEquals((new Parser())->parse($content), self::empty_result());
        // CR
        $content = "WEBVTT\u{000D}\u{000D}";
        $this->assertEquals((new Parser())->parse($content), self::empty_result());
    }

    function testIgnoreBOM()
    {
        $bom = chr(239) . chr(187) . chr(191);
        $content = "WEBVTT\n\n";
        $this->assertEquals((new Parser())->parse($bom . $content), self::empty_result());
    }

    public function testIgnoreStuffAfterSignature()
    {
        $content = "WEBVTT bla bla\n\n";
        $this->assertEquals((new Parser())->parse($content), self::empty_result());
    }

    public function testSimpleCue()
    {
        $content = "WEBVTT\n\n00:00:00.000 --> 01:22:33.440
Hello world\n";
        $result = (new Parser())->parse($content);

        $this->assertEquals($result['cues'][0]['text'], "Hello world");
        $this->assertEquals($result['cues'][0]['start'], 0);
        $this->assertEquals($result['cues'][0]['end'], 4953.44);
    }

    public function testCueWithVoice()
    {
        $content = "WEBVTT\n\n00:00:00.000 --> 01:22:33.440
<v Eric Teubert>Hello world\n";
        $result = (new Parser())->parse($content);

        $this->assertEquals($result['cues'][0]['voice'], "Eric Teubert");
        $this->assertEquals($result['cues'][0]['text'], "Hello world");
    }

    public function testCueWithClassyVoice()
    {
        $content = "WEBVTT\n\n00:00:00.000 --> 01:22:33.440
<v.somestyle Eric Teubert>Hello world\n";
        $result = (new Parser())->parse($content);

        $this->assertEquals($result['cues'][0]['voice'], "Eric Teubert");
        $this->assertEquals($result['cues'][0]['text'], "Hello world");
    }

    public function testCueWithIdentifier()
    {
        $content = "WEBVTT\n\nintro\n00:00:00.000 --> 01:22:33.440
Hello world\n";
        $result = (new Parser())->parse($content);

        $this->assertCount(1, $result['cues']);
        $this->assertEquals($result['cues'][0]['identifier'], "intro");
        $this->assertEquals($result['cues'][0]['text'], "Hello world");
        $this->assertEquals($result['cues'][0]['start'], 0);
        $this->assertEquals($result['cues'][0]['end'], 4953.44);
    }

    public function testMultipleCues()
    {
        $content = "WEBVTT\n\n00:00:00.000 --> 01:22:33.440
Hello world\n\n01:22:33.440 --> 01:22:34.440
Hi again\n";
        $result = (new Parser())->parse($content);

        $this->assertEquals($result['cues'][0]['text'], "Hello world");
        $this->assertEquals($result['cues'][0]['start'], 0);
        $this->assertEquals($result['cues'][0]['end'], 4953.44);

        $this->assertEquals($result['cues'][1]['text'], "Hi again");
        $this->assertEquals($result['cues'][1]['start'], 4953.44);
        $this->assertEquals($result['cues'][1]['end'], 4954.44);
    }

    public function testIgnoreNotes()
    {
        $content = "WEBVTT\n\nNOTE this is a note\n\n00:00:00.000 --> 01:22:33.440
Hello world\n";
        $result = (new Parser())->parse($content);

        $this->assertCount(1, $result['cues']);
        $this->assertEquals($result['cues'][0]['text'], "Hello world");
        $this->assertEquals($result['cues'][0]['start'], 0);
        $this->assertEquals($result['cues'][0]['end'], 4953.44);
    }

    public function testIgnoreMultilineNotes()
    {
        $content = "WEBVTT\n\nNOTE\nthis is a\nmultiline\nnote\n\n00:00:00.000 --> 01:22:33.440
Hello world\n";
        $result = (new Parser())->parse($content);

        $this->assertCount(1, $result['cues']);
        $this->assertEquals($result['cues'][0]['text'], "Hello world");
        $this->assertEquals($result['cues'][0]['start'], 0);
        $this->assertEquals($result['cues'][0]['end'], 4953.44);
    }

    /**
     * @expectedException \Podlove\Webvtt\ParserException
     * @expectedExceptionMessage Missing WEBVTT at beginning of file
     **/
    public function testMissingWEBVTT()
    {
        $result = (new Parser())->parse("");
    }

    /**
     * @expectedException \Podlove\Webvtt\ParserException
     * @expectedExceptionMessage Expected "line terminator"
     **/
    public function testMissingLineTerminator()
    {
        $result = (new Parser())->parse("WEBVTT");
    }

    private static function empty_result()
    {
        return [
            'cues' => []
        ];
    }
}
