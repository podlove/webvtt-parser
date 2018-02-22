<?php
use PHPUnit\Framework\TestCase;

use Podlove\Webvtt\Parser;

class ParserTest extends TestCase
{
    public function testEmpty()
    {
        $content = <<<DOC
WEBVTT

DOC;
        $this->assertEquals(Parser::parse($content), self::empty_result());
    }

    function testIgnoreBOM()
    {
        $bom = chr(239) . chr(187) . chr(191);
        $content = <<<DOC
WEBVTT

DOC;
        $this->assertEquals(Parser::parse($bom . $content), self::empty_result());
    }

    function testMissingWEBVTT()
    {
        $this->assertEquals(Parser::parse(""), [
            'result' => [],
            'messages' => ["Missing WEBVTT at beginning of file."]
        ]);
    }

    private static function empty_result()
    {
        return [
            'result' => [],
            'messages' => []
        ];
    }
}
