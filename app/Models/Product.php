<?php

namespace App\Models;

use Exception;

/**
 * Product Model
 */
class Product {
	/**
	 * @var string Brand name
	 */
	public string $make;
	/**
	 * @var string Model name
	 */
	public string $model;
	/**
	 * @var string Colour name
	 */
	public string $colour;
	/**
	 * @var string GB Spec name
	 */
	public string $capacity;
	/**
	 * @var string Network name
	 */
	public string $network;
	/**
	 * @var string Grade name
	 */
	public string $grade;
	/**
	 * @var string Condition name
	 */
	public string $condition;

	/**
	 * @param $make string Brand name
	 * @param $model string  Model name
	 *
	 * @throws Exception
	 */
	public function __construct( string $make, string $model){
		if(!empty($make) && !empty($model) ){
			$this->make  = $make;
			$this->model = $model;
		}else{
			throw new Exception("Error: Can't create new Product because make and model parameters can't be empty strings \n");
		}
	}

	/**
	 * @return string
	 */
	public function getMake(): string {
		return $this->make;
	}

	/**
	 * @param string $make
	 *
	 * @return Product
	 */
	public function setMake( string $make ): Product {
		$this->make = $make;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getModel(): string {
		return $this->model;
	}

	/**
	 * @param string $model
	 *
	 * @return Product
	 */
	public function setModel( string $model ): Product {
		$this->model = $model;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getColour(): string {
		return $this->colour;
	}

	/**
	 * @param string $colour
	 *
	 * @return Product
	 */
	public function setColour( string $colour ): Product {
		$this->colour = $colour;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCapacity(): string {
		return $this->capacity;
	}

	/**
	 * @param string $capacity
	 *
	 * @return Product
	 */
	public function setCapacity( string $capacity ): Product {
		$this->capacity = $capacity;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getNetwork(): string {
		return $this->network;
	}

	/**
	 * @param string $network
	 *
	 * @return Product
	 */
	public function setNetwork( string $network ): Product {
		$this->network = $network;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getGrade(): string {
		return $this->grade;
	}

	/**
	 * @param string $grade
	 *
	 * @return Product
	 */
	public function setGrade( string $grade ): Product {
		$this->grade = $grade;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCondition(): string {
		return $this->condition;
	}

	/**
	 * @param string $condition
	 *
	 * @return Product
	 */
	public function setCondition( string $condition ): Product {
		$this->condition = $condition;

		return $this;
	}

}