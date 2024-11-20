<?php

namespace App\Imports;

use App\Services\DataServiceImportRealisasi;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ProdAfdIntiImport implements ToCollection
{
    protected $finaldataTaksasiRealisasiGM;
    protected $month;
    protected $sheetName;
    protected $finaldataProdAfdInti;

    public function __construct(DataServiceImportRealisasi $finaldataTaksasiRealisasiGM, $month, $sheetName, DataServiceImportRealisasi $finaldataProdAfdInti)
    {
        $this->finaldataTaksasiRealisasiGM = $finaldataTaksasiRealisasiGM;
        $this->month = $month;
        $this->sheetName = $sheetName;
        $this->finaldataProdAfdInti = $finaldataProdAfdInti;
    }

    public function collection(Collection $collection)
    {
        // Retrieve data from the service
        $dataSheet = $this->finaldataTaksasiRealisasiGM->getData();


        // Traverse the $dataSheet and replace cell formulas with actual values dynamically based on sheetName
        $this->replaceFormulaValues($dataSheet, $collection);

        //karena value cell total tonase, janjang, pokok dan restan hi kemudian update value 
        $this->updateComputedKeys($dataSheet);

        $this->finaldataProdAfdInti->setData($dataSheet);
    }

    private function updateComputedKeys(array &$dataSheet)
    {
        foreach ($dataSheet as &$dateArray) {
            foreach ($dateArray as &$regionArray) {
                foreach ($regionArray as &$blockArray) {
                    if (is_array($blockArray)) {
                        // Calculate TOTAL TONASE (TONASE + RESTAN HI)
                        $tonase = $blockArray['TONASE'] ?? 0;
                        $restanHI = $blockArray['RESTAN HI'] ?? 0;
                        $blockArray['TOTAL TONASE'] = $tonase + $restanHI;

                        // Calculate BJR (TOTAL TONASE / JANJANG) only if JANJANG is not zero
                        if (isset($blockArray['TOTAL TONASE'], $blockArray['JANJANG']) && $blockArray['JANJANG'] > 0) {
                            $totalTonase = $blockArray['TOTAL TONASE'] ?? 0;
                            $janjang = $blockArray['JANJANG'] ?? 1; // Default to 1 to avoid division by zero
                            $blockArray['BJR'] = round($totalTonase / $janjang, 2);
                        } else {
                            $blockArray['BJR'] = null; // Set BJR to null if JANJANG is zero or not set
                        }

                        // Calculate AKP ((JANJANG / POKOK) * 100) only if POKOK is not zero
                        if (isset($blockArray['JANJANG'], $blockArray['POKOK']) && $blockArray['POKOK'] > 0) {
                            $janjang = $blockArray['JANJANG'] ?? 0;
                            $pokok = $blockArray['POKOK'] ?? 1; // Default to 1 to avoid division by zero
                            $blockArray['AKP'] = round(($janjang / $pokok) * 100, 2);
                        } else {
                            $blockArray['AKP'] = null; // Set AKP to null if POKOK is zero or not set
                        }
                    }
                }
            }
        }
    }


    private function replaceFormulaValues(array &$dataSheet, Collection $collection)
    {
        // Use the sheet name dynamically in the regular expression
        $pattern = "/'{$this->sheetName}'!\w+(\d+)/";

        array_walk_recursive($dataSheet, function (&$value) use ($collection, $pattern) {
            // Check if the value is a cell reference matching the current sheet name
            if (is_string($value) && preg_match($pattern, $value, $matches)) {
                // Extract the cell reference (e.g., 'M37')
                $cellReference = str_replace("'{$this->sheetName}'!", '', $value);
                $cellValue = $this->getCellValue($cellReference, $collection);

                // Replace the formula with the actual value from the collection
                $value = $cellValue;
            }
        });
    }

    private function getCellValue($cellReference, Collection $collection)
    {
        // Extract the column letter (M) and the row number (37) from the reference
        preg_match("/([A-Z]+)(\d+)/", $cellReference, $matches);

        if (count($matches) === 3) {
            $column = $matches[1];
            $row = (int)$matches[2];

            // Convert column letter to index (e.g., M -> 12)
            $columnIndex = $this->columnLetterToIndex($column);

            // Adjust row to 0-indexed (row 37 => index 36)
            $rowIndex = $row - 1;

            // Return the cell value if it exists
            return isset($collection[$rowIndex][$columnIndex]) ? $collection[$rowIndex][$columnIndex] : null;
        }

        return null;
    }

    private function columnLetterToIndex($columnLetter)
    {
        $columnIndex = 0;
        $length = strlen($columnLetter);

        for ($i = 0; $i < $length; $i++) {
            $columnIndex = $columnIndex * 26 + (ord($columnLetter[$i]) - ord('A'));
        }

        return $columnIndex; // No need to subtract 1 for 0-based index since $columnIndex starts from 0
    }
}
