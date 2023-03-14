<?php

namespace App\Controllers;
use App\Models\Product;

class ParserController {
	private string $src_file_path;
	private string $output_file_path;
	public function __construct( $src_file_path, $combinations_file_path ) {

		$this->src_file_path    = $src_file_path;
		$this->output_file_path = $combinations_file_path;
	}

	public function processProductList() {
		// validate paths
		$src_file_path= $this->processFilePath($this->src_file_path);
		// Handle file open
		$source_file = fopen($src_file_path['path'], "r") or die("Unable to open file!");
		$file_headers = $this->processFileLine(fgets($source_file),$src_file_path['extension']);
		//Retrieve headers  to recognize the column order
		$col= $this->getPositionsFromHeader($file_headers);
		//Create an array which saves each combination
		$combinations = [];
		// Read each file line until end of file
		while (($line = fgets($source_file)) !== false){
			// process the line read.
			//$line = fgets($source_file);
			print_r($line);
			//yield $line;
			$product_row = $this->processFileLine($line,$src_file_path['extension']);
			 //print_r($product_row);
			// save each row in Product model
			try{
				if(!empty($product_row[$col['make']]) && !empty($product_row[$col['model']])){
					$product = new Product($product_row[$col['make']],$product_row[$col['model']]);
					$product->setCondition($product_row[$col['condition']]);
					$product->setGrade($product_row[$col['grade']]);
					$product->setCapacity($product_row[$col['capacity']]);
					$product->setColour($product_row[$col['colour']]);
					$product->setNetwork($product_row[$col['network']]);
					// output the product object
					print_r($product);

					//$product_string = join($product_row);
					$product_string = serialize($product);
					//echo $product_string."\n";
					// Compare the current row and check if it already exists at the combination array
					$key = array_search($product_string,array_column($combinations,'product'));
					if($key){
						//echo 'found occurrence: '.$key."\n";
						//print_r($combinations[$key]);
						// If it already exists don't add it but increase the count
						$combinations[$key]['count']++;
					}else{
						$combinations[]=[ 'product'=>$product_string,'count'=>1];
					}
				}else{
				}
			}catch(\Exception   $e){
				echo $e->getMessage();
			}
		}
		//print_r($combinations);
		//print_r($combinations);
		fclose($source_file);
		// Once all the file has been read now print the combination array in unique combination file
		$output_file_path = $this->processFilePath($this->output_file_path);
		$this->outputCombination($combinations,$output_file_path['path']);
	}

	public function processFilePath($file_path): array {
		//echo $src_file_path;
		$path_parts = pathinfo($file_path);
		//echo $combination_file_path;
		// validate correct extension filename
		// validate if file exists/
		// validate if each path exists
		// validate if file extension is authorized
		// validate is field path is readable
		// validate if combination file path exists and is writable if not create
		return ['path'=>$file_path,'extension'=>$path_parts['extension']];
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
	public function getPositionsFromHeader($file_headers): array {
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

	public function processFileLine($line,$file_extension){
		$row=[];
		switch ($file_extension){
			case 'csv':
				$row= explode(',',trim(str_replace('"', '',$line)));
				break;
			case 'tsv':
				$row= explode(' ',trim(str_replace('"', '',$line)));
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