<?php

namespace Csv\Library;

class Csv
{
	public $filePath, $tempFile;
	public function __construct($filePath)
	{
		$this->filePath = $filePath;
		$this->tempFile = FCPATH . "CSV";
		if (!is_dir(($this->tempFile))) {
			mkdir(($this->tempFile), 0755, true);
			chmod(($this->tempFile), 0755);
		}
		$this->tempFile .= "/temp.csv";
	}

	public function createRecord($data)
	{
		if (!$this->filePath) {
			return "File name is required";
		}
		$file = $this->filePath;
		// Check if the file exists
		if (!file_exists($file)) {
			$handle = fopen($file, "w");
		} else {
			$handle = fopen($file, 'a'); // Open the file in append mode
		}

		fputcsv($handle, $data); // Write the data as a CSV row

		fclose($handle); // Close the file
	}

	public function readRecords()
	{
		if (!$this->filePath) {
			return "File name is required";
		}
		$file = $this->filePath;
		$handle = fopen($file, 'r'); // Open the file in read mode

		$records = [];
		while (($data = fgetcsv($handle)) !== false) {
			$records[] = $data; // Add each row to the records array
		}

		fclose($handle); // Close the file

		return $records;
	}

	public function updateRecord($data, $index)
	{
		if (!$this->filePath) {
			return "File name is required";
		}
		$file = $this->filePath;
		$tempFile = $this->tempFile;

		$handle = fopen($file, 'r'); // Open the original file in read mode
		$tempHandle = fopen($tempFile, 'w'); // Create a temporary file in write mode

		$count = 0;
		while (($rowData = fgetcsv($handle)) !== false) {
			if ($count == $index) {
				fputcsv($tempHandle, $data); // Write the updated data to the temporary file
			} else {
				fputcsv($tempHandle, $rowData); // Write the original row to the temporary file
			}
			$count++;
		}

		fclose($handle); // Close the original file
		fclose($tempHandle); // Close the temporary file

		rename($tempFile, $file); // Replace the original file with the temporary file
	}

	// Function to batch update CSV rows
	function updateRecordBatch($updates)
	{
		// Read the CSV into an array
		$data = array_map('str_getcsv', file($this->filePath));

		// Perform batch updates in memory
		foreach ($updates as $index => $update) {
			if (isset($data[$index])) {
				$data[$index] = $update; // Update the row
			}
		}

		// Write the updated data back to the CSV file
		$handle = fopen($this->filePath, 'w');
		foreach ($data as $row) {
			fputcsv($handle, $row);
		}
		fclose($handle);
	}

	// Function to update a specific row and cell in a CSV file
	function updateCellRecord($value, $rowIndex, $cellIndex)
	{
		// Read the CSV file into an array
		$rows = array_map('str_getcsv', file($this->filePath));

		// Update the value in the specified row and cell
		$rows[$rowIndex][$cellIndex] = $value;

		// Write the updated array back to the CSV file
		$file = fopen($this->filePath, 'w');
		foreach ($rows as $row) {
			fputcsv($file, $row);
		}
		fclose($file);
	}

	public function deleteRecord($index)
	{
		$file = $this->filePath;
		$tempFile = $this->tempFile;

		$handle = fopen($file, 'r'); // Open the original file in read mode
		$tempHandle = fopen($tempFile, 'w'); // Create a temporary file in write mode

		$count = 0;
		while (($rowData = fgetcsv($handle)) !== false) {
			if ($count != $index) {
				fputcsv($tempHandle, $rowData); // Write rows other than the one to be deleted to the temporary file
			}
			$count++;
		}

		fclose($handle); // Close the original file
		fclose($tempHandle); // Close the temporary file

		rename($tempFile, $file); // Replace the original file with the temporary file
	}
}
