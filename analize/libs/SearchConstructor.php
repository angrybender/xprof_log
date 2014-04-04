<?php
/**
 *
 * @author k.vagin
 */

class SearchDumpConstructor {

	const ARG_SCALAR = 'scalar';
	const ARG_VECTOR = 'vector';

	protected $conditions = array(
		'function_name' => array(),
		'function_line' => array(),
		'call_file' => array(),
		'entry_file' => null,
		'time' => array(
			'from' 	=> 0,
			'to'	=> 0
		),
		'arg' => array(),
	);

	protected static $query_template =
		"
			SELECT log_dumps.*
			FROM log_dumps
			%s
			WHERE %s
			GROUP BY log_dumps.id_dump
		";

	public function by_function_name($name)
	{
		$this->conditions['function_name'][] = (string)$name;
	}

	public function by_function_line($line)
	{
		$this->conditions['function_line'][] = (int)$line;
	}

	public function by_call_file($file)
	{
		$this->conditions['call_file'][] = (string)$file;
	}

	public function by_entry_point($file)
	{
		$this->conditions['entry_file'] = (string)$file;
	}

	/**
	 * @param $timestamp_from - seconds
	 * @param $timestamp_to - seconds
	 */
	public function by_time($timestamp_from, $timestamp_to)
	{
		$this->conditions['time']['from'] = 1000000*(int)$timestamp_from;
		$this->conditions['time']['to'] = 1000000*(int)$timestamp_to;
	}

	public function by_arg($type, $key, $value, $number)
	{
		$this->conditions['arg'][] = array(
			'type' => $type,
			'key' => $key,
			'value' => $value,
			'number' => $number,
		);
	}

	public function get_query()
	{
		// собираем WHERE часть запроса
		$where = array();

		// по точке входа - это нулевая линия вызова, пустая функция и определенный файл
		if (!empty($this->conditions['entry_file'])) {
			$path = $this->get_where(array($this->conditions['entry_file']), 'projects_files.full_path', 'LIKE');
			$where[] = "({$path} AND functions_call.name = '' AND functions_call.line = 0)";
		}

		// по файлу вызова
		if (!empty($this->conditions['call_file'])) {
			$where[] = $this->get_where($this->conditions['call_file'], 'projects_files.full_path', 'LIKE');
		}

		// по имени функции
		if (!empty($this->conditions['function_name'])) {
			$where[] = $this->get_where($this->conditions['function_name'], 'functions_call.name', 'LIKE');
		}

		// по линии вызова
		if (!empty($this->conditions['function_line'])) {
			$where[] = $this->get_where($this->conditions['function_line'], 'functions_call.line');
		}

		// время либо между либо до, либо после
		if ($this->conditions['time']['from'] > 0 && $this->conditions['time']['to'] > 0) {
			$where[] = "functions_call.timestamp <= {$this->conditions['time']['to']} AND functions_call.timestamp >= {$this->conditions['time']['from']}";
		}
		elseif ($this->conditions['time']['from'] > 0 && $this->conditions['time']['to'] == 0) {
			$where[] = "functions_call.timestamp >= {$this->conditions['time']['from']}";
		}

		// аргументы вызываемой функции
		foreach ($this->conditions['arg'] as $arg) {
			$arg['value'] = mysql_real_escape_string(addslashes($arg['value']));
			if ($arg['type'] === 'scalar') {
				$where[] = "(vars_parsed.assoc_value LIKE '%{$arg['value']}%' AND vars_parsed.assoc_key IS NULL AND vars_dump.type = 'arg_{$arg['number']}')";
			}
			else {
				$where[] = "(vars_parsed.assoc_value LIKE '%{$arg['value']}%' AND vars_parsed.assoc_key = '{$arg['key']}' vars_dump.type = 'arg_{$arg['number']}')";
			}
		}

		// JOIN часть
		$join = array();
		$join['functions_call'] = $this->get_join('functions_call.dump', 'log_dumps.id_dump');

		if (!empty($this->conditions['call_file']) || !empty($this->conditions['entry_file'])) {
			$join['projects_files'] = $this->get_join('projects_files.id_file', 'functions_call.file');
		}

		if (!empty($this->conditions['arg'])) {
			$join['vars_dump'] 		= $this->get_join('vars_dump.function', 'functions_call.id_function');
			$join['vars_parsed'] 	= $this->get_join('vars_parsed.var', 'vars_dump.var_id');
		}

		// формирование запроса:
		return sprintf(self::$query_template, join(PHP_EOL, $join), join(' OR ', $where));
	}

	protected function get_where(array $field_values, $field_name, $type = 'WHERE')
	{
		$where = array();
		foreach ($field_values as $value) {
			if (is_string($value) && $type == 'WHERE' || empty($value)) {
				$where[] = "{$field_name} = '{$value}'";
			}
			elseif (is_string($value) && $type == 'LIKE') {
				$value = mysql_real_escape_string(addslashes($value));
				$where[] = "{$field_name} LIKE '%{$value}%'";
			}
			else {
				$where[] = "{$field_name} = {$value}";
			}
		}

		return join(' AND ', $where);
	}

	protected function get_join($to_table, $from_table)
	{
		$to_table_parse = explode('.', $to_table);
		$from_table_parse = explode('.', $from_table);

		$to_table = $to_table_parse[0];
		$key_to = $to_table_parse[1];

		$from_table = $from_table_parse[0];
		$key_from = $from_table_parse[1];

		return "JOIN {$to_table} ON {$to_table}.{$key_to} = {$from_table}.{$key_from}";
	}
}