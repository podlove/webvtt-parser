<?php

use PHPUnit\Framework\TestCase;
use Podlove\Webvtt\Parser;

/**
 * @internal
 * @coversNothing
 */
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

    public function testIgnoreBOM()
    {
        $bom = chr(239).chr(187).chr(191);
        $content = "WEBVTT\n\n";
        $this->assertEquals((new Parser())->parse($bom.$content), self::empty_result());
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

        $this->assertEquals($result['cues'][0]['text'], 'Hello world');
        $this->assertEquals($result['cues'][0]['start'], 0);
        $this->assertEquals($result['cues'][0]['end'], 4953.44);
        $this->assertEquals(count($result['cues']), 1);
    }

    public function testSimpleCueWithTrailingNewlines()
    {
        $content = "WEBVTT\n\n00:00:00.000 --> 01:22:33.440
Hello & world\n\n\n\n\n\n\n";
        $result = (new Parser())->parse($content);

        $this->assertEquals($result['cues'][0]['text'], 'Hello & world');
        $this->assertEquals($result['cues'][0]['start'], 0);
        $this->assertEquals($result['cues'][0]['end'], 4953.44);
        $this->assertEquals(count($result['cues']), 1);
    }

    public function testCueWithVoice()
    {
        $content = "WEBVTT\n\n00:00:00.000 --> 01:22:33.440
<v Eric Teubert>Hello world\n";
        $result = (new Parser())->parse($content);

        $this->assertEquals($result['cues'][0]['voice'], 'Eric Teubert');
        $this->assertEquals($result['cues'][0]['text'], 'Hello world');
    }

    public function testCueWithClassyVoice()
    {
        $content = "WEBVTT\n\n00:00:00.000 --> 01:22:33.440
<v.somestyle Eric Teubert>Hello world\n";
        $result = (new Parser())->parse($content);

        $this->assertEquals($result['cues'][0]['voice'], 'Eric Teubert');
        $this->assertEquals($result['cues'][0]['text'], 'Hello world');
    }

    public function testCueWithIdentifier()
    {
        $content = "WEBVTT\n\nintro\n00:00:00.000 --> 01:22:33.440
Hello world\n";
        $result = (new Parser())->parse($content);

        $this->assertCount(1, $result['cues']);
        $this->assertEquals($result['cues'][0]['identifier'], 'intro');
        $this->assertEquals($result['cues'][0]['text'], 'Hello world');
        $this->assertEquals($result['cues'][0]['start'], 0);
        $this->assertEquals($result['cues'][0]['end'], 4953.44);
    }

    public function testMultipleCues()
    {
        $content = "WEBVTT\n\n00:00:00.000 --> 01:22:33.440
Hello world\n\n01:22:33.440 --> 01:22:34.440
Hi again\n";
        $result = (new Parser())->parse($content);

        $this->assertEquals($result['cues'][0]['text'], 'Hello world');
        $this->assertEquals($result['cues'][0]['start'], 0);
        $this->assertEquals($result['cues'][0]['end'], 4953.44);

        $this->assertEquals($result['cues'][1]['text'], 'Hi again');
        $this->assertEquals($result['cues'][1]['start'], 4953.44);
        $this->assertEquals($result['cues'][1]['end'], 4954.44);
    }

    public function testIgnoreNotes()
    {
        $content = "WEBVTT\n\nNOTE this is a note\n\n00:00:00.000 --> 01:22:33.440
Hello world\n";
        $result = (new Parser())->parse($content);

        $this->assertCount(1, $result['cues']);
        $this->assertEquals($result['cues'][0]['text'], 'Hello world');
        $this->assertEquals($result['cues'][0]['start'], 0);
        $this->assertEquals($result['cues'][0]['end'], 4953.44);
    }

    public function testIgnoreMultilineNotes()
    {
        $content = "WEBVTT\n\nNOTE\nthis is a\nmultiline\nnote\n\n00:00:00.000 --> 01:22:33.440
Hello world\n";
        $result = (new Parser())->parse($content);

        $this->assertCount(1, $result['cues']);
        $this->assertEquals($result['cues'][0]['text'], 'Hello world');
        $this->assertEquals($result['cues'][0]['start'], 0);
        $this->assertEquals($result['cues'][0]['end'], 4953.44);
    }

    /**
     * @expectedException \Podlove\Webvtt\ParserException
     * @expectedExceptionMessage Missing WEBVTT at beginning of file at line 1
     */
    public function testMissingWEBVTT()
    {
        $result = (new Parser())->parse('');
    }

    /**
     * @expectedException \Podlove\Webvtt\ParserException
     * @expectedExceptionMessage Expected "line terminator"
     */
    public function testMissingLineTerminator()
    {
        $result = (new Parser())->parse('WEBVTT');
    }

    /**
     * @expectedException \Podlove\Webvtt\ParserException
     * @expectedExceptionMessage missing cue timings at line 8
     */
    public function testSpecialCase()
    {
        $content = "WEBVTT\n\n00:09:43.101 --> 00:09:45.800
<v andreasbogk>foo.,

[00:09:45-8 @timpritlove] bar.
n
00:09:56.601 --> 00:10:05.400
<v andreasbogk>baz.

00:10:05.401 --> 00:10:14.200
<v andreasbogk>hey
";
        $result = (new Parser())->parse($content);
    }

//     /**
//      * @expectedException \Podlove\Webvtt\ParserException
//      * @expectedExceptionMessage invalid character at line 5
//      */
//     public function testDetectAmpersandCase()
//     {
//         $content = "WEBVTT\n\n00:09:43.101 --> 00:09:45.800
    // Hello & world
    // ";
//
//         $result = (new Parser())->parse($content);
//    }

    /**
     * @expectedException \Podlove\Webvtt\ParserException
     * @expectedExceptionMessage Cue identifier cannot be standalone.
     */
    public function testStandaloneIdentifier()
    {
        $content = 'WEBVTT

00:11.000 --> 00:13.000
<v Roger Bingham>We are in New York City

[01:45:07-2 Outro]
';

        $result = (new Parser())->parse($content);
    }

    private static function empty_result()
    {
        return [
            'cues' => [],
        ];
    }
}
