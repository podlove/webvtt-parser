<?php
namespace Podlove\Webvtt;

class Parser {
	public static function parse($str)
	{
		$pos = 0;
		$messages = [];

		// skip optional BOM
		$bom = chr(239) . chr(187) . chr(191);
		if (substr($str, $pos, 3) == $bom) {
			$pos += 3;
		}

		if (substr($str, $pos, 6) == "WEBVTT") {
			$pos += 6;
		} else {
			$messages[] = "Missing WEBVTT at beginning of file.";
		}

		return [
			'result' => [],
			'messages' => $messages
		];
	}
}
