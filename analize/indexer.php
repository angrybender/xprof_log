<?php
/**
 * CLI script
 * @author k.vagin
 */

include __DIR__ . '/libs/IndexLogFile.php';
require_once __DIR__ . '/libs/ViewCliProgress.php';

$indexer = new IndexLogFile();

$path = ini_get('xhprof.dump_dir');
$files = glob("{$path}profiler__tmp*");

$indexer->on_index_progress = function($current, $all) {
	if (($current+1) % 10 == 0) {
		\ViewCli\show_status($current+1, $all);
	}
};

foreach ($files as $i => $file) {
	echo "File: {$file}, ", ($i+1), " from ", count($files), PHP_EOL;
	$indexer->index($file);
	echo PHP_EOL;
}

echo "Complete.", PHP_EOL;