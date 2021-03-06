<?php
namespace is\Helpers;

class Datetimes {

	static public function amount($item) {
		
		$constants = [
			'min' => 60,
			'minute' => 60,
			'hour' => 3600,
			'day' => 86400,
			'week' => 604800,
			'month' => 2628000,
			'year' => 31556926
		];
		
		if (!Strings::match($item, ':')) {
			return (float) $item;
		}
		
		$item = Parser::fromString($item);
		$item = Objects::split($item);
		
		$sum = null;
		
		Objects::each($item, function ($val, $key) use (&$sum, $constants) {
			
			if (System::type($key, 'string')) {
				$key = $constants[$key];
			}
			if (System::type($val, 'string')) {
				$val = $constants[$val];
			}
			
			$sum += $key * $val;
			
		});
		
		return $sum;
		
	}
	
	static public function format($item) {
		
		$formats = [
			
			// ISO 8601
			
			'YYYY' => 'Y', // год, 4 знака: 2019
			'YY' => 'y', // год, 2 знака: 19
			'MM' => 'm', // месяц, 2 знака, с нулем: 01-12
			'M' => 'n', // месяц, 1-2 знака, без нуля: 1-12
			'DD' => 'd', // день, 2 знака, с нулем: 01-31
			'D' => 'j', // день, 1-2 знака, без нуля: 1-31
			
			'hh' => 'H', // часы, 24-часовой формат, с нулем: 01-24
			'h' => 'G', // часы, 24-часовой формат, без нуля: 1-24
			'gg' => 'h', // часы, 12-часовой формат, с нулем: 01-12
			'g' => 'g', // часы, 12-часовой формат, без нуля: 1-12
			'mm' => 'i', // минуты, с нулем: 00-59
			'm' => 'i', // минуты, с нулем: 00-59
			'ss' => 's', // секунды, с нулем: 00-59
			's' => 's', // секунды, с нулем: 00-59
			
			// дополнительные значения
			
			'yy' => 'Y', // год, 4 знака: 2019
			'y' => 'y', // год, 2 знака: 19
			'nn' => 'm', // месяц, 2 знака, с нулем: 01-12
			'n' => 'n', // месяц, 1-2 знака, без нуля: 1-12
			'dd' => 'd', // день, 2 знака, с нулем: 01-31
			'd' => 'j', // день, 1-2 знака, без нуля: 1-31
			
			'ww' => 'z', // день в году, 0-365
			'w' => 'N', // день недели, 1-7
			
			'p' => 'a', // префикс: am/pm
			'z' => 'Z', // временная зона, в миллисекундах: от -43200 до 50400
			
			'aa' => 'U.v', // абсолютное время, число секунд и миллисекунд с эпохи unix
			'a' => 'U', // абсолютное время, число секунд с эпохи unix
			
			// именованные значения
			
			'year' => 'Y', // год, 4 знака: 2019
			'month' => 'm', // месяц, 2 знака, с нулем: 01-12
			'day' => 'd', // день, 2 знака, с нулем: 01-31
			'hour' => 'H', // часы, 24-часовой формат, с нулем: 01-24
			'min' => 'i', // минуты, с нулем: 00-59
			'sec' => 's', // секунды, с нулем: 00-59
			'msec' => 'v', // миллисекунды, с нулем: 000-999
			
			'ampm' => 'a', // префикс: am/pm
			'week' => 'W', // номер недели в году, 1-42
			'days' => 't', // число дней в месяце: 28-31
			'zone' => 'P', // временная зона, двоеточие между часами и минутами: +02:00
			
			'abs' => 'U' // абсолютное время, число секунд с эпохи unix
			
		];
		
		return $formats[$item];
		
	}

	static public function convertFormat($format) {
		
		if (Strings::match($format, '{')) {
			
			$format = preg_replace('/(\w(?!\w*\}))/ui', '\\\\$1', $format);
			$format = preg_replace_callback('/\{\w+\}/ui', function($match){
				$item = reset($match);
				$item = Strings::get($item, 1, 1, true);
				return self::format($item);
			}, $format);
			
		} else {
			$format = constant('\DateTimeInterface::' . Prepare::upper($format));
		}
		
		return $format;
		
	}

	static public function create($string = null, $format) {
		
		$format_in = self::convertFormat($format);
		
		if (!$string) {
			$string = Strings::match($format, '{aa}') ? self::mtime() : time();
		}
		
		$global = new \DateTime;
		$date = $global -> createFromFormat('!' . $format_in, $string);
		
		if (!$date) {
			
			$pos_last = Strings::find($format, '}', 'r');
			$len = Strings::len($format) - 1;
			
			if (!System::set($pos_last)) {
				return;
			}
			
			$format = Strings::get(
				$format,
				0,
				$len > $pos_last ? $len : Strings::find($format, '{', 'r')
			);
			
			$date = self::create($string, $format);
			
		}
		
		return $date;
		
	}

	static public function convert($string = null, $input, $output) {
		
		$date = self::create($string, $input);
		
		if (!$date) {
			return;
		}
		
		$format_out = self::convertFormat($output);
		
		return $date -> format($format_out);
		
	}

	static public function compare($now, $min = null, $max = null, $format) {
		
		$result = 0;
		
		$now = $now ? self::convert($now, $format, '{abs}') : time();
		$min = $min ? self::convert($min, $format, '{abs}') : null;
		$max = $max ? self::convert($max, $format, '{abs}') : null;
		
		if ($min && $now < $min) {
			$result = -1;
		} elseif ($max && $now > $max) {
			$result = 1;
		}
		
		return $result;
		
		// если результат 0, т.е. !$result
		// это значит, что указанная дата находится в допустимом диапазоне
		
	}

	static public function time() {
		return time();
	}

	static public function mtime() {
		return round(microtime(true)*1000)/1000;
	}
	
	static public function milliseconds() {
		return Strings::get(microtime(), 2, 3);
	}

}

?>