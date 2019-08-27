<?php
declare(strict_types=1);

namespace App\Classes;


class CsvParser
{
	public function parseFile($file_path)
	{
		$handle = $this->openFile($file_path);
		$result = [];
		while (($data = fgetcsv($handle, 0)) !== FALSE) {
			$result[] = $data[0];
		}
		fclose($handle);

		return $result;
	}

	private function openFile($file_path)
	{
		$handle = fopen($file_path, "r");

		return $handle;
	}
}