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
	$parser->parse();
} else {
	echo 'Please define your file path and your unique combinations file path';
}
