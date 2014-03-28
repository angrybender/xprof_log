<?php
/**
 *
 * @author k.vagin
 */

$file = "/home/kirill/profiler__tmp_filePOPYdB.txt";

$xlog = simplexml_load_file($file);

print_r($xlog->FUNC_CALL[0]);
