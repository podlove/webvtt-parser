<?php
use PHPUnit\Framework\TestCase;

use Podlove\Webvtt\Parser;

class ParserTest extends TestCase
{
    public function testHello()
    {
        $this->assertEquals((new Parser)->hello(), 'world');
    }
}
