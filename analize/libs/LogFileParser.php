<?php
/**
 *
 * @author k.vagin
 */

class LogFileParser {

	protected $xml_tree = '';

	public function __construct($full_path)
	{
		$this->xml_tree = simplexml_load_file($full_path);
	}

	public function get_functions_call()
	{
		$calls = array();

		foreach ($this->xml_tree->FUNC_CALL as $func_tree) {
			$calls[] = array(
				'name' 	=> trim($func_tree->NAME),
				'file'	=> trim($func_tree->FILE),
				'line'	=> (int)$func_tree->FILE->attributes()->line,
				'time'	=> (int)$func_tree->attributes()->timestamp,
				'args'	=> $this->fetch_arguments($func_tree->ARGS),
				'super'	=> $this->fetch_super_globals($func_tree->SUPERGLOBALS),
			);
		}

		return $calls;
	}

	protected function fetch_arguments(SimpleXMLElement $args)
	{
		$result = array();

		foreach ($args as $arg) {
			$result[] = trim($arg);
		}

		return $result;
	}

	protected function fetch_super_globals(SimpleXMLElement $tree)
	{
		$result = array();
		foreach ($tree->children() as $element)
		{
			$result[$element->getName()] = trim($element);
		}

		return $result;
	}

	/**
	 * fixme - обрабатывает вложенные массивы на одном уровне
	 * @param $s_val
	 * @return string
	 */
	public static function parse_serialized_zval($s_val)
	{
		$s_val = trim($s_val);
		if (substr($s_val, 0, 5) !== "Array") {
			return $s_val;
		}

		preg_match_all("/\[(.*)\] =\> (.*)/um", $s_val, $matches, PREG_SET_ORDER);

		$result = array();
		foreach ($matches as $match) {
			if ($match[1]) {
				$result[$match[1]] = $match[2];
			}
			else {
				$result[] = $match[2];
			}
		}

		return $result;
	}
} 