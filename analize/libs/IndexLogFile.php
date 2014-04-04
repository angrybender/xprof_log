<?php
/**
 *
 * @author k.vagin
 */

require_once __DIR__ . '/../vendor/active_record/ActiveRecord.php';

require_once __DIR__ . '/Storage.php';
require_once __DIR__ . '/LogFileParser.php';

require_once __DIR__ . '/../models/LogDumps.php';
require_once __DIR__ . '/../models/FunctionsCall.php';
require_once __DIR__ . '/../models/ProjectsFiles.php';
require_once __DIR__ . '/../models/VarsDump.php';
require_once __DIR__ . '/../models/VarsParsed.php';

class IndexLogFile {

	protected $dump_file_id = 0;
	protected $parsed_file = array();
	public $on_index_progress = false;

	public function __construct()
	{
		Storage::get();
	}

	public function index($full_path)
	{
		$exist = \Models\LogDumps::find(array(
			'full_path' => $full_path
		));

		if (!empty($exist)) {
			return true;
		}

		$this->add_file($full_path);
		$log = new LogFileParser($full_path);
		$this->parsed_file = $log->get_functions_call();
		$this->index_functions_call();
	}

	protected function add_file($full_path)
	{
		$log = new \Models\LogDumps();
		$log->full_path = $full_path;
		$log->save();

		$this->dump_file_id = $log->id_dump;
	}

	protected function index_functions_call()
	{
		$function = null;
		foreach ($this->parsed_file as $i => $calle) {
			$function = \Models\FunctionsCall::create(array(
				'timestamp' 	=> $calle['time'],
				'name'			=> $calle['name'],
				'line'			=> $calle['line'],
				'file'			=> \Models\ProjectsFiles::add($calle['file']),
				'dump'			=> $this->dump_file_id
			), false);

			$this->add_function_args($function->id_function, $calle['args']);

			if ($this->on_index_progress) {
				call_user_func_array($this->on_index_progress, array($i, count($this->parsed_file)));
			}
		}

		if ($this->on_index_progress) {
			call_user_func_array($this->on_index_progress, array(count($this->parsed_file), count($this->parsed_file)));
		}
	}


	protected function add_function_args($function_id, array $args_source)
	{
		foreach ($args_source as $i => $arg) {
			$var = \Models\VarsDump::create(array(
				'function' 	=> $function_id,
				'type'		=> "arg_" .($i+1),
				'source'	=> $arg
			), false);

			$var_id = $var->var_id;

			$parsed = LogFileParser::parse_serialized_zval($arg);
			if (is_scalar($parsed)) {
				\Models\VarsParsed::create(array(
					'var' => $var_id,
					'assoc_value' => $parsed
				), false);
			}
			else {
				foreach ($parsed as $key => $value) {
					\Models\VarsParsed::create(array(
						'var' => $var_id,
						'assoc_value' => $value,
						'assoc_key' => $key
					), false);
				}
			}
		}
	}
} 