<?php
/**
 *
 * @author k.vagin
 */

class Func_list
{
	public function get()
	{
		// stub
		$file = "/home/kirill/profiler__tmp.txt";
		$xlog = simplexml_load_file($file);

		$functions = array();

		$start_time = 0;
		foreach ($xlog->FUNC_CALL as $fnc) {
			$time = (int)$fnc->attributes()->timestamp;
			if ($start_time == 0) {
				$start_time = $time;
			}

			$function = '';
			$is_entry = false;
			$is_include = false;
			if (isset($fnc->NAME->ENTRY_POINT)) {
				$is_entry = true;
			}
			elseif (isset($fnc->NAME->require) || isset($fnc->NAME->include) || isset($fnc->NAME->include_once) || isset($fnc->NAME->require_once)) {
				$is_include = true;
			}
			else {
				$function = trim((string)$fnc->NAME);
			}

			$functions[] = array(
				'time' 	=> 		$time - $start_time,
				'name'	=> 		$function,
				'file'	=> 		trim((string)$fnc->FILE),
				'is_entry' => 	$is_entry,
				'is_include' => $is_include,
				'line' 		=> 	(int)$fnc->FILE->attributes()->line,
			);
		}

		return $functions;
	}

}