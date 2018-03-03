# Podlove WebVTT Parser

PHP library to parse WebVTT files.

It follows the [W3C spec](https://w3c.github.io/webvtt/), but it's not complete. For example, it does not have special treatment for CSS styles.

## Usage

```php
use Podlove\Webvtt\Parser;
use Podlove\Webvtt\ParserException;

$parser = new Podlove\Webvtt\Parser();
$content = "WEBVTT\n\n00:00:00.000 --> 01:22:33.440\nHello world\n\n01:22:33.440 --> 01:22:34.440\n<v Eric>Hi again\n";
$result = $parser->parse($content);
// [
//   "cues" => [
//     [
//       "voice" => "",
//       "start" => 0,
//       "end" => 4953.44,
//       "text" => "Hello world",
//       "identifier" => "",
//     ],
//     [
//       "voice" => "Eric",
//       "start" => 4953.44,
//       "end" => 4954.44,
//       "text" => "Hi again",
//       "identifier" => "",
//     ],
//   ],
// ]
```
