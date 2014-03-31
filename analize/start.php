<?php
/**
 *
 * @author k.vagin
 */

$file = "/home/kirill/profiler__tmp.txt";

$xlog = simplexml_load_file($file);

//echo date('H:I:s', (int)$xlog->FUNC_CALL[0]->attributes()->timestamp), PHP_EOL;
