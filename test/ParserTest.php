<?php
use PHPUnit\Framework\TestCase;

use Podlove\Webvtt\Parser;

class ParserTest extends TestCase
{
    public function testEmpty()
    {
        $content = "WEBVTT\u{000A}\u{000A}";
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

    function testMissingWEBVTT()
    {
        $result = (new Parser())->parse("");
        $this->assertEquals($result['messages'][0], "Missing WEBVTT at beginning of file.");
    }

    private static function empty_result()
    {
        return [
            'result' => [],
            'messages' => []
        ];
    }
}
