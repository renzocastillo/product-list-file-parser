<?php

namespace Tests\Unit;

use App\Models\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase {

	/**
	 * @throws \Exception
	 * @covers \App\Models\Product;
	 */
	public function test__construct() {
		$make = 'Nokia';
		$model = '3310';
		$product = new Product($make, $model);
		$this->assertSame($make, $product->make);
		$this->assertSame($model, $product->model);
	}
}
