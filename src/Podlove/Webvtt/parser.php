<?php
namespace Podlove\Webvtt;

class ParserException extends \Exception {

}

/**
 * NOTES
 * 
 * - build a generic "error message, position, line, expected ... but got ..." reporting routine
 * - this is great: https://w3c.github.io/webvtt/
 */
class Parser {

	private $pos;
	private $line;
	private $content;

	const LF    = "\u{000A}";
	const CR    = "\u{000D}";
	const SPACE = "\u{0020}";
	const TAB   = "\u{0009}";

	public function parse($content)
	{
		$this->pos = 0;
		$this->line = 1;
		$this->content = $content;

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
			'cues' => []
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
			throw new ParserException("Missing WEBVTT at beginning of file.");
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
			throw new ParserException("Expected line terminator at line {$this->line}");
		}
	}
}
