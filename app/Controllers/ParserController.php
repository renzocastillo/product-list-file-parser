<?php

namespace App\Controllers;
use App\Models\Product;
use Exception;
use function array_column;
use function array_search;
use function fgets;
use function fopen;
use function serialize;

class ParserController {
	public function processProductList(string $src_file_path,string $combinations_file_path) {
		// validate paths
		try {
			$src_file_path = $this->validateFile( $src_file_path, 'r');
			$combinations_file_path = $this->validateFile( $combinations_file_path, 'w' );
			//Create an array which saves each combination
			$combinations = [];
			$line_count   = 1;
			// read file line with generator
			$lines = $this->getFileLines( $src_file_path );
			foreach ( $lines as $line ) {
				if ( ! empty( $line ) ) {
					//print_r($line);
					// process the  line
					$row = $this->parseFileLine( $line,  pathinfo($src_file_path)['extension'] );
					//print_r($row);
					if ( $line_count == 1 ) {
						//Retrieve headers  to recognize the column order
						$col = $this->getColumnsOrderFromHeader( $row );
						//print_r( $col );

					} else {
						try {
							//print_r( $row );
							// save each row in Product model
							$product = new Product( $row[ $col['make'] ], $row[ $col['model'] ] );
							$product->setCondition( $row[ $col['condition'] ] );
							$product->setGrade( $row[ $col['grade'] ] );
							$product->setCapacity( $row[ $col['capacity'] ] );
							$product->setColour( $row[ $col['colour'] ] );
							$product->setNetwork( $row[ $col['network'] ] );
							// output the product object
							print_r( $product );
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
						} catch ( Exception $e ) {
							echo "Failed to process line $line_count: $line \n";
							echo $e->getMessage();
						}
					}
				}
				$line_count ++;
			}
			//print_r($combinations);
			// Once all the file has been read now print the combination array in unique combination file
			$this->outputCombination( $combinations, $combinations_file_path );
		} catch ( Exception $e ) {
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
	 * @throws Exception
	 */
	public function validateFile($path,$mode): string {
		return $this->validateFilePermissions($this->validateFilePath($path),$mode);
	}

	/**
	 * @param $file_path string file path
	 *
	 * @return string
	 * @throws Exception
	 */
	public function validateFilePath( string $file_path): string {
		if(empty ($file_path)){
			throw new Exception("Error: File path value is empty \n");
		}
		if(!strpos($file_path, ".")){
			throw new Exception("Error: File path doesn't have an extension format \n");
		}
		$path_parts = pathinfo($file_path);
		$extension = $path_parts['extension'];
		$base_name = $path_parts['basename'];
		$allowed_extensions = ['csv','tsv','json','xml'];
		// validate if file extension is authorized
		if(!in_array($extension,$allowed_extensions)){
			throw new Exception("Error: Can't use $base_name because .$extension is not a valid extension. Only ".implode(',',$allowed_extensions)." extensions are allowed.\n");
		}
		return $file_path;
	}

	/**
	 * @param $file_path string file path
	 * @param $mode string r for read mode and w for write mode
	 * @return  string
	 * @throws Exception
	 */
	public function validateFilePermissions( string $file_path, string $mode): string {
		if(!in_array($mode,['r','w'])){
			throw new Exception("Error: Cant use $mode mode.Only r,w file modes are available \n");
		}
		if($mode == 'r'){
			// validate if file exists/
			if( !file_exists($file_path) ){
				throw new \Exception("Error: Can't use $file_path because the file doesn't exist.\n");
			}
			// validate is field path is readable
			if(!is_readable($file_path)){
				throw new \Exception("Error: Can't use $file_path because the file is not readable.\n");
			}
		}else{
			// validate if file exists and is writable
			if( file_exists($file_path) && !is_writable($file_path) ){
				throw new \Exception("Error: Can't use $file_path because the file is not writable.\n");
			}
		}
		return $file_path;
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
				$product->make,
				$product->model,
				$product->colour,
				$product->capacity,
				$product->network,
				$product->grade,
				$product->condition,
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
		//we start looping through each file header
		foreach($file_headers as $key=> $value){
			//we search if the current file header exists in the model headers array and we retrieve the key
			$new_key=array_search($value,$model_headers);
			// we create a positions array which has the positions as values
			$positions_array[$new_key]=$key;
		}
		//print_r($positions_array);
		return $positions_array;
	}

	public function parseFileLine($line,$file_extension){
		$row=[];
		$line = trim($line);
		switch ($file_extension){
			case 'csv':
				$row= explode(',',$line);
				break;
			case 'tsv':
				$row= explode("\t",$line);
				break;
			case 'json':
				break;
			case 'xml':
				break;
			default:
				break;
		}
		foreach ($row as &$col) {
			$col= str_replace('"', '', $col);
		}
		return $row;
	}
}