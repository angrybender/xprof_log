<?php

class Test {
	public static function method()
	{

	}

	public function g($arg1, $arg2, $arg3 = 1)
	{
//		self::method();
	}
}

// start profiling
xhprof_enable();

$obj = new Test();

$obj->g(10, 'a', array(1,2, array(true, 'abc')));

echo 'test';


// stop profiler
$xhprof_data = xhprof_disable();