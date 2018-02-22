<?php
namespace Podlove\Webvtt;

class Parser {

	private $pos;
	private $line;
	private $content;
	private $messages;

	const LF    = "\u{000A}";
	const CR    = "\u{000D}";
	const SPACE = "\u{0020}";
	const TAB   = "\u{0009}";

	public function parse($content)
	{
		$this->pos = 0;
		$this->line = 1;
		$this->content = $content;
		$this->messages = [];

		$this->skip_bom();
		$this->skip_webvtt();
		// $this->skip_webvtt_trails();
		$this->skip_line_terminator();
		$this->skip_line_terminator();

		return [
			'result' => [],
			'messages' => $this->messages
		];
	}

	private function skip_bom()
	{
		$bom = chr(239) . chr(187) . chr(191);
		
		if (substr($this->content, $this->pos, 3) == $bom) {
			$this->pos += 3;
		}
	}

	private function skip_webvtt()
	{
		if (substr($this->content, $this->pos, 6) == "WEBVTT") {
			$this->pos += 6;
		} else {
			$this->messages[] = "Missing WEBVTT at beginning of file.";
		}		
	}

	private function skip_webvtt_trails()
	{
		// if (in_array(substr($this->content, $pos, 1), [$space, $tab])) {
		// 	$this->pos++;
		// }
	}

	private function skip_line_terminator()
	{
		if (substr($this->content, $this->pos, 2) === self::CR . self::LF) {
			$this->pos += 2;
			$this->line++;
		} else if (substr($this->content, $this->pos, 1) === self::LF || substr($this->content, $this->pos, 1) === self::CR) {
			$this->pos += 1;
			$this->line++;
		} else {
			$this->messages[] = "Expected line terminator at line {$this->line}";
		}
	}
}
