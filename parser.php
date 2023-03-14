<?php
require_once('vendor/autoload.php');

use App\Controllers\ParserController;

$short_options = "f:u:";
$long_options  = [ "file:", "unique-combinations:" ];

$options = getopt( $short_options, $long_options );

if ( ( isset( $options["f"] ) || isset( $options["file"] ) ) ) {
	$src_file_path = $options["f"] ?? $options["file"];
}
if ( ( isset( $options["u"] ) || isset( $options["unique-combinations"] ) ) ) {
	$combination_file_path = $options["u"] ?? $options["unique-combinations"];
}

if ( isset( $src_file_path, $combination_file_path ) ) {
	$parser = new ParserController( $src_file_path, $combination_file_path );
	$parser->processProductList();
	getMemoryUsage();
} else {
	echo 'Please define your file path and your unique combinations file path';
}


function formatBytes($bytes, $precision = 2) {
	$units = array("b", "kb", "mb", "gb", "tb");

	$bytes = max($bytes, 0);
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	$pow = min($pow, count($units) - 1);

	$bytes /= (1 << (10 * $pow));

	return round($bytes, $precision) . " " . $units[$pow];
}

function getMemoryUsage(){
	print formatBytes(memory_get_peak_usage());
}