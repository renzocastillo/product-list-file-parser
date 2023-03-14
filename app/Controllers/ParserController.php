<?php

namespace App\Controllers;
use App\Models\Product;
use function array_column;
use function array_search;
use function fgets;
use function fopen;
use function PHPUnit\Framework\throwException;
use function serialize;

class ParserController {
	private string $src_file_path;
	private string $output_file_path;
	public function __construct( $src_file_path, $combinations_file_path ) {

		$this->src_file_path    = $src_file_path;
		$this->output_file_path = $combinations_file_path;
	}

	public function processProductList() {
		try {
			// validate paths
			$src_file_path = $this->validateFilePath( $this->src_file_path ,'r');
			$output_file_path = $this->validateFilePath( $this->output_file_path,'w' );
			$source_file = fopen( $src_file_path['path'], "r" ) or die( "Unable to open file!" );
			$file_headers = $this->parseFileLine( fgets( $source_file ), $src_file_path['extension'] );
			//Retrieve headers  to recognize the column order
			$col = $this->getColumnsOrderFromHeader( $file_headers );
			//Create an array which saves each combination
			$combinations = [];
			$line_count   = 1;
			// read file line with generator
			$lines = $this->getFileLines( $src_file_path['path'] );
			foreach ( $lines as $line ) {
				if ( ! empty( $line ) ) {
					// process the  line
					$product_row = $this->parseFileLine( $line, $src_file_path['extension'] );
					//print_r( $product_row );
					// save each row in Product model
					try {
						$product = new Product( $product_row[ $col['make'] ], $product_row[ $col['model'] ] );
						$product->setCondition( $product_row[ $col['condition'] ] );
						$product->setGrade( $product_row[ $col['grade'] ] );
						$product->setCapacity( $product_row[ $col['capacity'] ] );
						$product->setColour( $product_row[ $col['colour'] ] );
						$product->setNetwork( $product_row[ $col['network'] ] );
						// output the product object
						print_r($product);
						$product_string = serialize( $product );
						//echo $product_string."\n";
						// Compare the current row and check if it already exists at the combination array
						$key = array_search( $product_string, array_column( $combinations, 'product' ) );
						if ( $key ) {
							//echo 'found occurrence: '.$key."\n";
							//print_r($combinations[$key]);
							// If it already exists don't add it but increase the count
							$combinations[ $key ]['count'] ++;
						} else {
							$combinations[] = [ 'product' => $product_string, 'count' => 1 ];
						}
					} catch ( \Exception $e ) {
						echo "Failed to process current line $line_count: $line";
						echo $e->getMessage();
					}
				}
				$line_count ++;
			}
			//print_r($combinations);
			// Once all the file has been read now print the combination array in unique combination file
			$this->outputCombination( $combinations, $output_file_path['path'] );
		} catch ( \Exception $e ) {
			echo $e->getMessage();
		}
	}

	public function getFileLines($file_path): \Generator {
		// Handle file open
		$source_file = fopen($file_path, "r") or die("Unable to open file!");
		// Read each file line until end of file
		while (! feof($source_file)){
			yield trim(fgets($source_file));
		}
		fclose($source_file);
	}

	/**
	 * @param $file_path string file path
	 * @param $mode string r for read mode and w for write mode
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function validateFilePath( string $file_path, string $mode): array {
		//echo $src_file_path;
		if(!in_array($mode,['r','w'])){
			throw new \Exception("Error: Cant use $mode mode.Only r,w file modes are available \n");
		}
		$path_parts = pathinfo($file_path);
		$extension = $path_parts['extension'];
		$base_name = $path_parts['basename'];
		$allowed_extensions = ['csv','tsv','json','xml'];
		// validate if file extension is authorized
		if(!in_array($extension,$allowed_extensions)){
			throw new \Exception("Error: Can't use $base_name because .$extension is not a valid extension. Only ".implode(',',$allowed_extensions)." extensions are allowed.\n");
		}
		if($mode == 'r'){
			// validate if file exists/
			if( !file_exists($file_path) ){
				throw new \Exception("Error: Can't use $base_name because the file doesn't exist.\n");
			}
			// validate is field path is readable
			if(!is_readable($file_path)){
				throw new \Exception("Error: Can't use $base_name because the file is not readable.\n");
			}
		}else{
			// validate if file exists and is writable
			if( file_exists($file_path) && !is_writable($file_path) ){
				throw new \Exception("Error: Can't use $base_name because the file is not writable.\n");
			}
		}
		return ['path'=>$file_path,'extension'=>$extension];
	}


	public function outputCombination($combinations_array,$output_file_path){
		//first we open the file
		$output_file= fopen($output_file_path,'w');
		$file_headers=[
			'make'=>'make',
			'model'=>'model',
			'colour'=>'colour',
			'capacity'=>'capacity',
			'network'=>'network',
			'grade'=>'grade',
			'condition'=>'condition',
			'count'=>'count'
		];
		fwrite($output_file, implode(',',$file_headers).PHP_EOL);
		foreach($combinations_array as $combination){
			$product = unserialize($combination['product']);
			$count = $combination['count'];
			$combination_row = [
				$product->getMake(),
				$product->getModel(),
				$product->getColour(),
				$product->getCapacity(),
				$product->getNetwork(),
				$product->getGrade(),
				$product->getCondition(),
				$count
			];
			//we write per each line
			fwrite($output_file, implode(',',$combination_row).PHP_EOL);
		}
		//we close it
		fclose($output_file);
	}
	public function getColumnsOrderFromHeader($file_headers): array {
		//echo $headers;
		$model_headers=[
			'make'=>'brand_name',
			'model'=>'model_name',
			'colour'=>'colour_name',
			'capacity'=>'gb_spec_name',
			'network'=>'network_name',
			'grade'=>'grade_name',
			'condition'=>'condition_name'
		];

		//print_r($model_headers);
		//print_r($file_headers);

		$positions_array=[];
		foreach($file_headers as $key=> $value){
			$new_key=array_search($value,$model_headers);
			$positions_array[$new_key]=$key;
		}
		//print_r($positions_array);
		return $positions_array;
	}

	public function parseFileLine($line,$file_extension){
		$row=[];
		switch ($file_extension){
			case 'csv':
				$row= explode(',',trim(str_replace('"', '',$line)));
				break;
			case 'tsv':
				$row= explode("\t",trim(str_replace('"', '',$line)));
				break;
			case 'json':
				break;
			case 'xml':
				break;
			default:
				break;
		}
		return $row;
	}
}