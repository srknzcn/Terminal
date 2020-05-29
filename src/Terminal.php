<?php
namespace CLI;

/**
 * PHP CLI/Terminal for color, blink and format output messages
 *
 * @author SRKNZCN <serkanozcan@gmail.com>
 */
class Terminal
{

	private $ATTRIBUTES = array(
		'clear'         => 0,
		'reset'         => 0,
		'bold'          => 1,
		'dark'          => 2,
		'faint'         => 2,
		'underline'     => 4,
		'underscore'    => 4,
		'blink'         => 5,
		'reverse'       => 7,
		'concealed'     => 8,
		'black'         => 30, 'onblack' => 40,
		'red'           => 31, 'onred' => 41,
		'green'         => 32, 'ongreen' => 42,
		'yellow'        => 33, 'onyellow' => 43,
		'blue'          => 34, 'onblue' => 44,
		'magenta'       => 35, 'onmagenta' => 45,
		'cyan'          => 36, 'oncyan' => 46,
		'white'         => 37, 'onwhite' => 47,
		'brightblack'   => 90, 'onbrightblack' => 100,
		'brightred'     => 91, 'onbrightred' => 101,
		'brightgreen'   => 92, 'onbrightgreen' => 102,
		'brightyellow'  => 93, 'onbrightyellow' => 103,
		'brightblue'    => 94, 'onbrightblue' => 104,
		'brightmagenta' => 95, 'onbrightmagenta' => 105,
		'brightcyan'    => 96, 'onbrightcyan' => 106,
		'brightwhite'   => 97, 'onbrightwhite' => 107
	);
	private $ATTRIBUTES_R = array();
	public $EACHLINE = FALSE;
	static $instance = NULL;

	public function __construct()
	{
		// PHP_VERSION_ID is available as of PHP 5.2.7, if our
		// version is lower than that, then throw exception
		if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300) {
			throw new RuntimeException("ANSIColor requires PHP 5.3+");
		}
		// Reverse lookup for uncolor
		foreach ($this->ATTRIBUTES as $att => $val) {
			$this->ATTRIBUTES_R[$val] = $att;
		}
	}

	/**
	 * Prints message to terminal screen with given attributes.
	 *
	 * @return Terminal
	 */
	public static function write($string, $color = 'green')
	{
		if (self::$instance instanceof Terminal)
			self::$instance;
		else
			self::$instance = new Terminal();

		$params = array();
		$args = func_get_args();
		for ($i = 1; $i < count($args); $i++)
			$params[] = $args[$i];
		echo self::$instance->colored($args[0], $params);
		return self::$instance;
	}

	/**
	 * Prints message to terminal screen with given attributes and adds new line.
	 *
	 * @return Terminal
	 */
	public static function writeln($string = ' ', $color = 'green')
	{
		if (self::$instance instanceof Terminal)
			self::$instance;
		else
			self::$instance = new Terminal();

		$params = array();
		$args = func_get_args();
		for ($i = 1; $i < count($args); $i++)
			$params[] = $args[$i];
		echo self::$instance->colored((isset($args[0]) ? $args[0] : ''), $params) . "\n";
		return self::$instance;
	}

	/**
	 * Dies script and prints the message
	 *
	 * @return void
	 */
	public static function dieln($string = ' ', $color = 'green')
	{
		if (self::$instance instanceof Terminal)
			self::$instance;
		else
			self::$instance = new Terminal();

		$params = array();
		$args = func_get_args();
		for ($i = 1; $i < count($args); $i++)
			$params[] = $args[$i];
		echo self::$instance->colored((isset($args[0]) ? $args[0] : ''), $params) . "\n";
		exit;
	}

	/**
	 * __call
	 *
	 * $ac = new ANSIColor();
	 * echo $ac->Bold_Blue_OnMagenta('foo') . "\n";
	 *
	 * @param mixed $method - color codes seperated by underscore
	 * @param mixed $args - one arg, the text to color
	 * @access public
	 * @return string surrounded by escape codes
	 */
	public function __call($method, $args)
	{
		return $this->colored($args[0], explode('_', strtolower($method)));
	}

	/**
	 * color
	 *
	 * @param array $codes
	 * @access public
	 * @return escape code for a geven set of color attributes
	 */
	private function color($codes = array())
	{
		$attribute = '';
		foreach ($codes as $code) {
			$code = strtolower($code);
			if (isset($this->ATTRIBUTES[$code])) {
				$attribute .= "{$this->ATTRIBUTES[$code]};";
			}
			else {
				throw new InvalidArgumentException("Invalid attribute name $code");
			}
		}
		$attribute = substr($attribute, 0, -1);
		return empty($attribute) ? FALSE : chr(27) . "[$attribute" . "m";
	}

	/**
	 * uncolor
	 * $ac->uncolor(array('1;42', chr(27) . "[m", '', chr(27) . "[0m")); // array('bold','ongreen','reset')
	 *
	 * @param array $codes - escap codes to lookup
	 * @access public
	 * @return array of named color attributes for a given set of escape codes
	 */
	private function uncolor($codes = array())
	{
		$nums = array();
		$result = array();
		$patts = array('/^' . chr(27) . '\[/', '/m$/');
		foreach ($codes as $code) {
			$esc = preg_replace($patts, '', $code);
			if (preg_match('/^((?:\d+;)*\d*)$/', $esc, $matches)) {
				if ($matches[0] != '') {
					$nums = array_merge($nums, explode(';', $matches[0]));
				}
			}
			else {
				throw new InvalidArgumentException("Bad escape sequence $esc");
			}
		}
		foreach ($nums as $num) {
			$num += 0; // Strip leading zeroes
			if (isset($this->ATTRIBUTES_R[$num])) {
				$result[] = $this->ATTRIBUTES_R[$num];
			}
			else {
				throw new InvalidArgumentException("No name for escape sequence $num");
			}
		}
		return $result;
	}

	/**
	 * colored
	 *
	 * echo $ac->colored("Yellow on magenta.", array('bold','yellow','onmagenta')) . "\n";
	 *
	 * @param string $txt - text to color
	 * @param array $codes - the attributes for the text
	 * @access public
	 * @return string surrounded by escape codes
	 */
	private function colored($txt = '', $codes = array())
	{
		$attr = $this->color($codes);
		if (!empty($this->EACHLINE)) {
			$eachline = $this->EACHLINE;
			$parts = preg_split("/(\Q$eachline\E)/", $txt, 0, PREG_SPLIT_DELIM_CAPTURE);
			if (count($parts) > 0) {
				return implode('', array_map(function ($n) use ($eachline, $attr) {
					return $n != "$eachline" ? $attr . $n . chr(27) . "[0m" : $n;
				}, $parts));
			}
		}
		else {
			return $attr . $txt . chr(27) . "[0m";
		}
	}

	/**
	 * colorstrip
	 *
	 * $txt = chr(27) . "[1mBold " . chr(27) . "[31;42mon green" . chr(27) . "[0m" . chr(27) . "[m";
	 * echo $ac->colorstrip($txt); //Bold on green
	 *
	 * @param mixed $txt - text with ANSI color codes
	 * @access public
	 * @return string - txt w/ removed ANSI color codes
	 */
	private function colorStrip($txt)
	{
		return preg_replace('/' . chr(27) . '\[[\d;]*m/', '', $txt);
	}

	/**
	 * colorvalid
	 *
	 * @param mixed $color
	 * @access public
	 * @return boolean - indicates wether the color is valid
	 */
	private function colorValid($color)
	{
		return isset($this->ATTRIBUTES[$color]);
	}

}
