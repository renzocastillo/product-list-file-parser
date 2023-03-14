<?php
require_once('vendor/autoload.php');

use App\Controllers\ParserController;

$options               = getOptions();
$src_file_path         = $options['src_file_path'];
$combination_file_path = $options['combinations_file_path'];
$memory_usage          = $options['memory_usage'];
$time                  = $options['time'];

if ( isset( $src_file_path, $combination_file_path ) ) {
	$parser = new ParserController( $src_file_path, $combination_file_path );
	if ( ! empty( $time ) ) {
		$time_start = microtime( true );
	}

	$parser->processProductList();

	if ( ! empty( $time ) ) {
		$time_end       = microtime( true );
		$execution_time = number_format( ( $time_end - $time_start ) / 60, 2 );
		echo "Total execution time: $execution_time minutes \n";
	}
	if ( ! empty( $memory_usage ) ) {
		getMemoryUsage();
	}
} else {
	echo 'Please define your file path and your unique combinations file path';
}

function getOptions(): array {
	$short_options = "f:u:m::t::";
	$long_options  = [ "file:", "unique-combinations:" ,"memory-usage::","time::"];
	$src_file_path = $combination_file_path = $memory_usage= $time = '';
	$options = getopt( $short_options, $long_options );
	if ( ( isset( $options["f"] ) || isset( $options["file"] ) ) ) {
		$src_file_path = $options["f"] ?? $options["file"];
	}
	if ( ( isset( $options["u"] ) || isset( $options["unique-combinations"] ) ) ) {
		$combination_file_path = $options["u"] ?? $options["unique-combinations"];
	}
	if ( ( isset( $options["m"] ) || isset( $options["memory-usage"] ) ) ) {
		$memory_usage = true;
	}
	if ( ( isset( $options["t"] ) || isset( $options["time"] ) ) ) {
		$time = true;
	}

	return [
		'src_file_path'=>$src_file_path,
		'combination_file_path'=>$combination_file_path,
		'memory_usage'=>$memory_usage,
		'time'=>$combination_file_path
	];
}
function formatBytes($bytes, $precision = 2): string {
	$units = array("b", "kb", "mb", "gb", "tb");

	$bytes = max($bytes, 0);
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	$pow = min($pow, count($units) - 1);

	$bytes /= (1 << (10 * $pow));

	return round($bytes, $precision) . " " . $units[$pow];
}

function getMemoryUsage(){
	echo "Total memory usage: ".formatBytes(memory_get_peak_usage())."\n";
}