<?php

namespace App\Imports;

use App\Models\Afdeling;
use App\Models\Estate;
use App\Services\DataServiceImportRealisasi;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class RealisasiImport implements ToCollection
{
    /**
     * @param Collection $collection
     */
    protected $dataService;
    protected $finaldataTaksasiRealisasiGM;
    protected $month;
    protected $finaldataProdAfdInti;

    public function __construct(DataServiceImportRealisasi $dataService, $month, $finaldataTaksasiRealisasiGM, DataServiceImportRealisasi $finaldataProdAfdInti)
    {
        $this->dataService = $dataService;
        $this->month = $month;
        $this->finaldataTaksasiRealisasiGM = $finaldataTaksasiRealisasiGM;
        $this->finaldataProdAfdInti = $finaldataProdAfdInti;
    }

    /**
     * Get the next date and increment by one day.
     *
     * @return string
     */

    public function collection(Collection $collection)
    {
        $dataFromTenagaKerjaSheet = $this->dataService->getData();



        $listAllEstate = Estate::select('id', 'nama', 'est', 'wil')->where(DB::raw('LOWER(nama)'), 'NOT LIKE', '%mill%')->get()->toArray();

        $month = $this->month;
        $filteredRowsEstate = [];
        $findWil = False;
        $findBulan = False;
        $dataRaw = [];
        $nama_wilayah_rom = '';
        $nama_wilayah = '';
        $tanggal_row = '';
        $bulan_now = '';

        $indexStartRealisasi = '';
        $indexLastRealisasiColumn  = '';
        $indexAfdeling = '';
        $arrKeyRealisasi = [];
        $tempEstateCheckedName = '';
        $increment_date = 0;
        $test = [];

        $listEstateSheet = [];
        $inc = 1;
        foreach ($collection as $key => $row) {

            if ($findWil == false) {
                foreach ($row as $value) {
                    if (strtolower($value) === 'wilayah iii') {
                        $nama_wilayah = $this->namaWilayahRow($value);
                        break;
                    }
                }

                if ($nama_wilayah != '') {
                    foreach ($listAllEstate as $estateKey => $estateRow) {
                        if (isset($estateRow['wil']) && $estateRow['wil'] == $nama_wilayah) {
                            // Set status to check if it has been read or not
                            $filteredRowsEstate[$estateRow['est']] = false;
                        }
                    }

                    if ($filteredRowsEstate != []) {
                        $findWil = true; // Stop branching this in each row iteration
                    }
                }
            }

            foreach ($row as $subKey => $value) {


                if ($value === 'REALISASI') {
                    $indexStartRealisasi = $subKey;
                }

                if ($value === "AFDELING") {
                    $indexAfdeling = $subKey;
                }

                if ($value === 'HA PANEN') {
                    for ($i = $indexStartRealisasi; $i < $indexStartRealisasi + 9; $i++) {
                        $arrKeyRealisasi[$i] = $row[$i];
                    }
                }
            }

            if ($filteredRowsEstate != [] && $indexStartRealisasi != '' && $arrKeyRealisasi != []) {

                foreach ($row as $subKey => $value) {
                    // Check if $value is a valid key type before using it in array_key_exists
                    if (is_string($value) || is_int($value)) {


                        if (array_key_exists($value, $filteredRowsEstate)) {
                            $filteredRowsEstate[$value] = true;
                            $tempEstateCheckedName = $value;
                            $tanggal_row = $month . '-' . $this->formatNumberTanggal($increment_date);
                            $inc++;
                            $increment_date++;
                        }
                    }
                }

                if ($tempEstateCheckedName != '') {


                    $listEstateSheet[] = $tempEstateCheckedName;
                    foreach ($arrKeyRealisasi as $key => $title) {
                        // if (is_numeric($row[$key])) {


                        $dataRaw[$inc][$tempEstateCheckedName][$row[$indexAfdeling]][$title] = $row[$key];


                        // $dataRaw[$inc][$tempEstateCheckedName][$row[$indexAfdeling]][$title] = null;

                    }
                }
            }
        }



        if ($dataRaw != []) {
            $dataRaw = $this->removeEmptyKeys($dataRaw);

            foreach ($dataRaw as $key => $value) {
                foreach ($value as $key2 => $value2) {
                    foreach ($value2 as $key3 => $value3) {

                        $tonase = is_numeric($value3['TONASE']) ? $value3['TONASE'] : 0;
                        $restanHi = is_numeric($value3['RESTAN HI']) ? $value3['RESTAN HI'] : 0;
                        $totalTonase = $tonase + $restanHi;

                        $dataRaw[$key][$key2][$key3]['TOTAL TONASE'] = $totalTonase;
                        if (isset($value3['JANJANG']) && $value3['JANJANG'] != 0 && $totalTonase != 0) {
                            $dataRaw[$key][$key2][$key3]['BJR'] = round($totalTonase / $value3['JANJANG'], 2);
                        } else {
                            $dataRaw[$key][$key2][$key3]['BJR'] = null; // or some default value
                        }

                        if (isset($value3['POKOK']) && $value3['POKOK'] != 0 && $totalTonase != 0) {
                            $dataRaw[$key][$key2][$key3]['AKP'] = round(($value3['JANJANG'] / $value3['POKOK']) * 100, 2);
                        } else {
                            $dataRaw[$key][$key2][$key3]['AKP'] = null; // or some default value
                        }
                    }
                }
            }

            $listEstateSheet = array_unique($listEstateSheet);
            $listEstateSheet = array_values($listEstateSheet);
            $startDate = (new DateTime($month . '-01'))->format('Y-m-01');
            $endDate = (new DateTime($month . '-01'))->format('Y-m-t');
            $groupedDataFinal = $this->groupByPatternAndDates($dataRaw, $listEstateSheet, $startDate, $endDate);

            foreach ($groupedDataFinal as $key => $value) {
                foreach ($value as $key2 => $value2) {
                    foreach ($value2 as $key3 => $value3) {
                        if (isset($dataFromTenagaKerjaSheet[$key][$key2][$key3])) {
                            $groupedDataFinal[$key][$key2][$key3]['HK'] = $dataFromTenagaKerjaSheet[$key][$key2][$key3];
                        }
                    }
                }
            }


            // dd($groupedDataFinal['2024-09-11']);
            // DB::connection('mysql2')->transaction(function () use ($groupedDataFinal) {
            //     $insertRecords = [];

            //     foreach ($groupedDataFinal as $date => $estates) {
            //         foreach ($estates as $estateName => $afdelings) {
            //             foreach ($afdelings as $afdelingName => $data) {
            //                 $record = [
            //                     'tanggal_realisasi' => $date . ' 00:00:00',
            //                     'est' => $estateName,
            //                     'afd' => $afdelingName,
            //                     'ha_panen' => $data['HA PANEN'],
            //                     'pokok' => $data['POKOK'],
            //                     'janjang' => $data['JANJANG'],
            //                     'tonase' => $data['TONASE'],
            //                     'restan_hi' => $data['RESTAN HI'],
            //                     'total_tonase' => $data['TOTAL TONASE'],
            //                     'bjr' => $data['BJR'],
            //                     'hk' => $data['HK'],
            //                     'akp' => $data['AKP']
            //                 ];

            //                 $existingRecord = DB::connection('mysql2')->table('realisasi_taksasi')
            //                     ->where('tanggal_realisasi', $record['tanggal_realisasi'])
            //                     ->where('est', $record['est'])
            //                     ->where('afd', $record['afd'])
            //                     ->first();

            //                 if (!$existingRecord) {
            //                     $insertRecords[] = $record;
            //                 } else {
            //                     DB::connection('mysql2')->table('realisasi_taksasi')
            //                         ->where('id', $existingRecord->id)
            //                         ->update($record);
            //                 }
            //             }
            //         }
            //     }



            // if (!empty($insertRecords)) {
            //     foreach (array_chunk($insertRecords, 50) as $chunk) {
            //         DB::connection('mysql2')->table('realisasi_taksasi')->insert($chunk);
            //     }
            // }
            // });

            $this->finaldataTaksasiRealisasiGM->setData($groupedDataFinal);
        }
    }


    function groupByPatternAndDates(array $data, array $pattern, string $startDate, string $endDate): array
    {
        $groupedData = [];
        $currentDate = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $patternLength = count($pattern);
        $dataLength = count($data);
        $dataIndex = 0;

        while ($currentDate->lessThanOrEqualTo($end)) {
            $groupKey = $currentDate->format('Y-m-d');
            $groupedData[$groupKey] = [];

            foreach ($pattern as $patternKey) {
                if ($dataIndex < $dataLength) {
                    // Assign directly without creating a nested array
                    $groupedData[$groupKey][$patternKey] = $data[array_keys($data)[$dataIndex]][$patternKey];
                    $dataIndex++;
                } else {
                    break 2; // Exit both loops if data is exhausted
                }
            }

            // Move to the next date
            $currentDate->addDay();
        }

        return $groupedData;
    }


    function removeEmptyKeys(array $array): array
    {
        foreach ($array as $key => &$value) {
            if ($key === "") {
                unset($array[$key]);
            } elseif (is_array($value)) {
                $value = $this->removeEmptyKeys($value);
            }
        }
        return $array;
    }

    private function formatNumberTanggal(int $number)
    {

        return $number < 10 ? '0' . $number : $number;
    }


    private function namaWilayahRow($value)
    {

        switch ($value) {
            case 'WILAYAH I':
                return '1';
            case 'WILAYAH II':
                return '2';
            case 'WILAYAH III':
                return '3';
            case 'WILAYAH IV':
                return '4';
            case 'WILAYAH V':
                return '5';
            case 'WILAYAH VI':
                return '6';
            case 'WILAYAH VII':
                return '7';
            case 'WILAYAH VIII':
                return '8';
            case 'WILAYAH IX':
                return '9';
            default:
                return 'Unknown WILAYAH';
        }
    }
}
