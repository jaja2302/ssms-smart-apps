<?php

namespace App\Imports;

use App\Models\Estate;
use App\Services\DataServiceImportRealisasi;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class TenagaKerjaSheetImport implements ToCollection
{
    /**
     * @param Collection $collection
     */

    protected $dataService;
    protected $month;

    public function __construct(DataServiceImportRealisasi $dataService, $month)
    {
        $this->dataService = $dataService;
        $this->month = $month;
    }

    public function collection(Collection $collection)
    {

        $id_hk_karyawan = 7;
        $afdelingToCheck = ['OA', 'OB', 'OC', 'OD', 'OE', 'OF', 'OG', 'OH', 'OI', 'OJ', 'OK', 'OL'];

        $month = $this->month;
        $dataRaw = [];
        $inc = 1;
        $id_afd_atau_est = 2;
        $tempListEstate = [];
        $listAllEstate = Estate::select('id', 'nama', 'est', 'wil')->where(DB::raw('LOWER(nama)'), 'NOT LIKE', '%mill%')->pluck('est')->toArray();

        foreach ($collection as $key => $row) {
            foreach ($row as $key => $value) {
                if (in_array($value, $afdelingToCheck)) {
                    $dataRaw[$inc][$value] = $row[$id_hk_karyawan];
                }
                if (in_array($row[$id_afd_atau_est], $listAllEstate)) {
                    $dataRaw[$inc][$row[$id_afd_atau_est]] = $row[$id_hk_karyawan];
                    $tempListEstate[] = $row[$id_afd_atau_est];
                }
            }

            $inc++;
        }

        foreach ($dataRaw as $outerKey => $innerArray) {
            foreach ($innerArray as $innerKey => $innerValue) {
                if (is_numeric($innerKey)) {
                    unset($dataRaw[$outerKey][$innerKey]);
                }
            }
        }

        $listEstateSheet = array_unique($tempListEstate);
        $listEstateSheet = array_values($listEstateSheet);

        $dataNew = [];
        $inc = 1;
        foreach ($dataRaw as $key => $value) {
            $temp[] = $value;

            foreach ($value as $key2 => $value2) {
                if (in_array($key2, $listAllEstate)) {
                    $dataNew[$inc][$key2] = $temp;
                    $temp = [];
                }
            }
            $inc++;
        }



        $dataNew = $this->normalizeData($dataNew);



        $startDate = (new DateTime($month . '-01'))->format('Y-m-01');
        $endDate = (new DateTime($month . '-01'))->format('Y-m-t');

        $groupedData = $this->groupByPatternAndDates($dataNew, $listEstateSheet, $startDate, $endDate);


        $finalData = [];



        foreach ($groupedData as $key => $value) {
            foreach ($value as $key2 => $value2) {
                foreach ($value2 as $key3 => $value3) {

                    foreach ($value3 as $key4 => $value4) {
                        $finalData[$key][$key2][$key4] = $value4;
                    }
                }
            }
        }

        $this->dataService->setData($finalData);
    }

    function normalizeData(array $data): array
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                foreach ($value as $k => &$v) {
                    if (is_array($v)) {
                        $v = $this->normalizeData($v);
                    } elseif (is_string($v) && strpos($v, '=SUM') === 0) {
                        // Extract the range and calculate the sum
                        preg_match_all('/\b([A-Z]+)(\d+)\b/', $v, $matches);
                        if (!empty($matches[0])) {
                            $range = $matches[0];
                            $sum = 0;
                            foreach ($range as $cell) {
                                preg_match('/([A-Z]+)(\d+)/', $cell, $cellMatches);
                                $column = $cellMatches[1];
                                $row = $cellMatches[2];
                                // Example: Assuming data is structured as $data[$row][$column]
                                if (isset($data[$row][$column])) {
                                    $sum += (float) $data[$row][$column];
                                }
                            }
                            $v = $sum;
                        }
                    }
                }
            }
        }
        return $data;
    }


    function groupByPatternAndDates(array $data, array $pattern, string $startDate, string $endDate): array
    {
        $groupedData = [];
        $currentDate = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $patternLength = count($pattern);
        $dataKeys = array_keys($data);
        $dataLength = count($dataKeys);
        $dataIndex = 0;

        while ($currentDate->lessThanOrEqualTo($end)) {
            $groupKey = $currentDate->format('Y-m-d');
            $groupedData[$groupKey] = [];

            foreach ($pattern as $patternKey) {
                if ($dataIndex < $dataLength) {
                    $currentKey = $dataKeys[$dataIndex];
                    if (isset($data[$currentKey][$patternKey])) {
                        $groupedData[$groupKey][$patternKey] = $data[$currentKey][$patternKey];
                    } else {
                        $groupedData[$groupKey][$patternKey] = [];
                    }
                    $dataIndex++;
                }
            }


            $currentDate->addDay();
        }

        return $groupedData;
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
