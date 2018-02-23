<?php
use PHPUnit\Framework\TestCase;

use Podlove\Webvtt\Parser;
use Podlove\Webvtt\ParserException;

class ParserTest extends TestCase
{
    public function testEmpty()
    {
        $content = "WEBVTT\u{000A}\u{000A}";
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
        $content = "WEBVTT\u{000A}\u{000A}";
        $this->assertEquals((new Parser())->parse($bom . $content), self::empty_result());
    }

    public function testIgnoreStuffAfterSignature()
    {
        $content = "WEBVTT bla bla\u{000A}\u{000A}";
        $this->assertEquals((new Parser())->parse($content), self::empty_result());
    }

    public function testSimpleCue()
    {
        $content = "WEBVTT\u{000A}\u{000A}00:00:00.000 --> 01:22:33.440
Hello world\u{000A}";
        $result = (new Parser())->parse($content);

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
     * @expectedExceptionMessage Expected line terminator
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
