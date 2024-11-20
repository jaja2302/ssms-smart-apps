<?php

namespace App\Imports;

use App\Services\DataServiceImportRealisasi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class KahadiranPemanenImport implements ToCollection
{
    /**
     * @param Collection $collection
     */

    protected $month;
    protected $sheetName;
    protected $finaldataProdAfdInti;

    public function __construct(DataServiceImportRealisasi $finaldataProdAfdInti, $month, $sheetName)
    {
        $this->finaldataProdAfdInti = $finaldataProdAfdInti;
        $this->month = $month;
        $this->sheetName = $sheetName;
    }

    public function collection(Collection $collection)
    {
        //
        $dataSheet = $this->finaldataProdAfdInti->getData();

        $this->replaceFormulaValues($dataSheet, $collection);

        DB::connection('mysql2')->transaction(function () use ($dataSheet) {
            $insertRecords = [];

            foreach ($dataSheet as $date => $estates) {
                foreach ($estates as $estateName => $afdelings) {
                    foreach ($afdelings as $afdelingName => $data) {
                        $record = [
                            'tanggal_realisasi' => $date . ' 00:00:00',
                            'est' => $estateName,
                            'afd' => $afdelingName,
                            'ha_panen' => $data['HA PANEN'],
                            'pokok' => $data['POKOK'],
                            'janjang' => $data['JANJANG'],
                            'tonase' => $data['TONASE'],
                            'restan_hi' => $data['RESTAN HI'],
                            'total_tonase' => $data['TOTAL TONASE'],
                            'bjr' => $data['BJR'],
                            'hk' => $data['HK'],
                            'akp' => $data['AKP']
                        ];

                        $existingRecord = DB::connection('mysql2')->table('realisasi_taksasi')
                            ->where('tanggal_realisasi', $record['tanggal_realisasi'])
                            ->where('est', $record['est'])
                            ->where('afd', $record['afd'])
                            ->first();

                        if (!$existingRecord) {
                            $insertRecords[] = $record;
                        } else {
                            DB::connection('mysql2')->table('realisasi_taksasi')
                                ->where('id', $existingRecord->id)
                                ->update($record);
                        }
                    }
                }
            }



            if (!empty($insertRecords)) {
                foreach (array_chunk($insertRecords, 50) as $chunk) {
                    DB::connection('mysql2')->table('realisasi_taksasi')->insert($chunk);
                }
            }
        });
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
