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

		// NULL -> REPLACEMENT
		$this->content = str_replace("\u{0000}", "\u{FFFD}", $this->content);
		// CRLF -> LF
		$this->content = str_replace(self::CR . self::LF, self::LF, $this->content);
		// CR -> LF
		$this->content = str_replace(self::CR, self::LF, $this->content);

		$this->skip_bom();
		$this->skip_signature();
		$this->skip_signature_trails();
		$this->skip_line_terminator();
		$this->skip_line_terminator();

		return [
			'result' => [],
			'messages' => $this->messages
		];
	}

	private function next($length = 1)
	{
		return substr($this->content, $this->pos, $length);
	}

	private function skip_bom()
	{
		$bom = chr(239) . chr(187) . chr(191);
		
		if ($this->next(3) == $bom) {
			$this->pos += 3;
		}
	}

	private function skip_signature()
	{
		if ($this->next(6) == "WEBVTT") {
			$this->pos += 6;
		} else {
			$this->messages[] = "Missing WEBVTT at beginning of file.";
		}		
	}

	private function skip_signature_trails()
	{
		if (in_array($this->next(), [self::SPACE, self::TAB])) {
			$this->pos++;
			while ($this->next() !== self::LF && !$this->is_end_reached()) {
			    $this->pos++;
			}
		}
	}

	private function is_end_reached() {
		return $this->pos + 1 >= strlen($this->content);
	}

	private function skip_line_terminator()
	{
		if ($this->next() === self::LF) {
			$this->pos += 1;
			$this->line++;
		} else {
			$this->messages[] = "Expected line terminator at line {$this->line}";
		}
	}
}
