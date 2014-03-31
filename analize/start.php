<?php
/**
 *
 * @author k.vagin
 */

$file = "/home/kirill/profiler__tmp.txt";

$xlog = simplexml_load_file($file);


$functions = array();

foreach ($xlog->FUNC_CALL as $fnc) {
	if (isset($fnc->NAME->ENTRY_POINT)) {
		echo "ENTRY_POINT", PHP_EOL;
	}
	elseif (isset($fnc->NAME->require) || isset($fnc->NAME->include) || isset($fnc->NAME->include_once) || isset($fnc->NAME->require_once)) {
		echo "include", PHP_EOL;
	}
	else {
		echo trim((string)$fnc->NAME), PHP_EOL;
	}
}