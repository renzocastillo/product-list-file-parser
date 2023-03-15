<?php

namespace Tests\Unit;

use App\Controllers\ParserController;
use PHPUnit\Framework\TestCase;

class ParserControllerTest extends TestCase {
	/**
	 * @throws \Exception
	 * @covers \App\Controllers\ParserController
	 */
	public function testValidateFilePath() {
		$path = $this->randomPathGenerator();
		$parser= new ParserController();
		$this->assertSame($path,$parser->validateFilePath($path));
	}

	public function randomPathGenerator(): string {
		$file_name= substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5);
		$file_ext_arr = ['csv','tsv','json','xml'];
		$file_ext = $file_ext_arr[array_rand($file_ext_arr )];
		return "$file_name.$file_ext";
	}
}
