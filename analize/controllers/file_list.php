<?php
/**
 *
 * @author k.vagin
 */

class File_list
{
	public function get()
	{
		$path = ini_get('xhprof.dump_dir');
		return array(
			'files' => glob("{$path}profiler__tmp*")
		);
	}
}