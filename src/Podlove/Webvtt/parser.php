<?php
namespace Podlove\Webvtt;

class Parser {

	private $pos;
	private $content;
	private $messages;

	public function parse($content)
	{
		$this->pos = 0;
		$this->content = $content;
		$this->messages = [];

		$this->skip_bom();
		$this->skip_webvtt();

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
}
