<?php

namespace App\Controllers;
use App\Models\Product;

class ParserController {
	public $src_file_path;
	public $combination_file_path;
	public function __construct( $src_file_path, $combination_file_path ) {

		$this->src_file_path         = $src_file_path;
		$this->combination_file_path = $combination_file_path;
	}

	public function parse() {
		// validate paths
		$this->validatePaths($this->src_file_path,$this->combination_file_path);
		// Handle file open
		$source_file = fopen($this->src_file_path, "r") or die("Unable to open file!");
		$file_extension = 'csv';
		$file_headers = $this->processFileLine(fgets($source_file),$file_extension);
		//Retrieve headers  to recognize the columnn order
		$col= $this->getPositionsFromHeader($file_headers);
		//Create an array which saves each combination
		$products = [];
		$counts = [];
		$combinations = [];
		// Read each file line until end of file
		while (($line = fgets($source_file)) !== false) {
			// process the line read.
			$product_row = $this->processFileLine($line,$file_extension);
			// save each row in Product model
			$product = new Product($product_row[$col['make']],$product_row[$col['model']]);
			$product->setCondition($product_row[$col['condition']]);
			$product->setGrade($product_row[$col['grade']]);
			$product->setCapacity($product_row[$col['capacity']]);
			$product->setColour($product_row[$col['colour']]);
			$product->setNetwork($product_row[$col['network']]);
			// output the product object
			//print_r($product);
			//print_r($product);
			//$product_string = join($product_row);
			$product_string = serialize($product);
			 //echo $product_string."\n";
			// Compare the current row and check if it already exists at the combination array
			$key = array_search($product_string,array_column($combinations,'product',));
			if($key){
				//echo 'found ocurrence: '.$key."\n";
				//print_r($combinations[$key]);
				//echo "\n";
				//echo 'product:'.$combinations[$key][0]."\n";
				//echo $combinations[$key][1]."\n";
				// If it already exists dont add it but increase the count
				$combinations[$key]['count']++;
				//print_r($combinations[$key]);
			}else{
				$combinations[]=[ 'product'=>$product_string,'count'=>1];
			}
			/*'make'=>$product->getMake(),
					'model'=>$product->getModel(),
					'colour'=>$product->getColour(),
					'capacity'=>$product->getCapacity(),
					'network'=>$product->getNetwork(),
					'grade'=>$product->getGrade(),
					'condition'=>$product->getCondition(),*/


		}
		//print_r($counts);
		//print_r($products);
		//print_r($combinations);
		//$combinations= array_merge($counts,$products);
		//print_r($combinations);
		//print_r($products);
		fclose($source_file);
		// Once all the file has been read now print the combination array in unique combination file
		$combination_file_path = $this->validateFilePath($this->combination_file_path);
		$this->outputCombination($combinations,$combination_file_path);
	}

	public function validateFilePath($file_path){
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
	public function getPositionsFromHeader($file_headers){
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
		print_r($file_headers);

		$positions_array=[];
		foreach($file_headers as $key=> $value){
			$new_key=array_search($value,$model_headers);
			$positions_array[$new_key]=$key;
		}
		//print_r($positions_array);
		return $positions_array;
	}

	public function processFileLine($line,$file_extension){
		switch ($file_extension){
			case 'csv':
				return explode(',',trim(str_replace('"', '',$line)));
			case 'tsv':
				return explode(' ',trim(str_replace('"', '',$line)));
			case 'json':
				break;
			case 'xml':
				break;
			default:
				break;
		}
	}

	public function validatePaths($src_file_path,$combination_file_path){

		//echo $src_file_path;
		//echo $combination_file_path;
		// validate correct extension filename
		// validate if file exists/
		// validate if each path exists
		// validate if file extension is authorized
		// validate is field path is readable
		// validate if combination file path exists and is writable if not create
	}
}