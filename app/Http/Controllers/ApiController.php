<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

class ApiController extends Controller
{


    public function exportPdfTaksasi($est, $date,  $web = null)
    {
        $estate_input = $est;
        $tgl = $date;

        $queryEstate = DB::connection('mysql2')->table('estate')
            ->select('estate.*')
            ->where('est', $estate_input)
            ->first();
        $queryDataNew = DB::connection('mysql2')->table('taksasi')
            ->select('taksasi.*')
            ->whereDate('taksasi.waktu_upload', $tgl)
            ->where('lokasi_kerja', $estate_input)
            ->orderBy('taksasi.afdeling', 'asc')
            // ->groupBy('taksasi.afdeling')
            ->get();

        $queryDataNew = $queryDataNew->groupBy(['afdeling', 'blok']);
        $queryDataNew = json_decode($queryDataNew, true);

        $Taksasi = array();



        $sum_all_bjr_blok = [];
        $sum_all_sph_blok = [];
        $sum_all_output_blok = 0;
        foreach ($queryDataNew as $key => $value) {
            foreach ($value as $key1 => $value1) {

                $bjr = 0;
                $jumlahpk = 0;
                $sum_janjang = 0;
                $sum_pokok = 0;
                $pemanen = 0;
                $br_kiri = '';
                $br_kanan = '';
                $nama_ancak = '';
                $rotasi = 0;
                $merge_baris = '';

                $total_keb_pemanen_kg_per_hk = 0;
                $total_keb_pemanen_ha_per_hk = 0;
                $processedBlokYgSama = [];
                $luas = 0;
                $pokok_produktif = 0;
                $pokok_janjang = 0;
                $output = 0;
                $outputBlokYgSama = 0;
                $inc = 0;
                $blockData = []; // To store total output and count for each block
                foreach ($value1 as $key2 => $value2) {
                    $blok = $value2['blok'];
                    $currentOutput = $value2['output'];
                    // Track block data
                    if (!isset($blockData[$blok])) {
                        $blockData[$blok] = [
                            'totalOutput' => 0,
                            'count' => 0
                        ];
                    }
                    $blockData[$blok]['totalOutput'] += $currentOutput;
                    $blockData[$blok]['count'] += 1;

                    if (!in_array($value2['blok'], $processedBlokYgSama)) {
                        $luas += $value2['luas'];
                        $processedBlokYgSama[] = $value2['blok'];
                        $output += $value2['output'];
                        $pokok_produktif += $value2['pokok_produktif'];
                        $bjr = $value2['bjr_sensus'] != 0 ? $value2['bjr_sensus'] : $value2['bjr'];
                        $sum_all_bjr_blok[$value2['blok']] = $bjr;
                        $sum_all_sph_blok[$value2['blok']] = $value2['sph'];
                        $outputBlokYgSama++;
                    }

                    $sum_janjang += $value2['jumlah_janjang'];
                    $sum_pokok += $value2['jumlah_pokok'];
                    // $pokok_produktif = ceil(($sum_pokok / $value2['sph'] / $value2['luas']) * 100);
                    $pemanen += $value2['pemanen'];
                    $merge_baris .= '(' . $value2['br_kiri'] . ',' . $value2['br_kanan'] . '), ';
                    $br_kiri .= $value2['br_kiri'];
                    $br_kanan .= $value2['br_kanan'];
                    $nama_ancak .= $value2['ancak'] . ', ';
                    $rotasi += $value2['rotasi'];
                    $inc++;
                }

                $finalOutput = 0;
                foreach ($blockData as $blok => $data) {
                    if ($data['count'] > 1) {
                        // Average output for blocks that occur more than once
                        $averageOutput = $data['totalOutput'] / $data['count'];
                        $finalOutput += $averageOutput;
                    } else {
                        // Use total output for unique blocks
                        $finalOutput += $data['totalOutput'];
                    }
                }

                $finalOutput = round($finalOutput / $outputBlokYgSama, 2);

                $sum_all_output_blok += $finalOutput;


                $merge_baris = rtrim($merge_baris, ", ");
                $rotasi = rtrim($rotasi, ", ");
                $elements = explode(", ", $merge_baris);
                $uniqueElements = array_unique($elements);
                $merge_baris = implode(", ", $uniqueElements);

                $nama_ancak_values = array_unique(array_filter(explode(', ', rtrim($nama_ancak, ', '))));
                $unique_nama_ancak = implode(', ', $nama_ancak_values);

                $akp = round(($sum_janjang / $sum_pokok) * 100, 2);
                $jjg_taksasi = ceil(($akp * $luas * $value2['sph']) / 100);
                $tak = round(($akp * $luas * $bjr * $value2['sph']) / 100, 1);

                if ($luas > 4.5) {
                    if ($output != 0) {
                        $total_keb_pemanen_ha_per_hk = round($tak / $output, 2);
                    } else {
                        $total_keb_pemanen_ha_per_hk = 0; // or any default value you prefer
                    }
                } else {
                    $total_keb_pemanen_ha_per_hk = 1;
                }

                // $sum_sph = round($sum_sph / $inc, 2);
                $Taksasi[$key][$key1]['luas'] = $luas;
                $Taksasi[$key][$key1]['bjr'] = $value2['bjr'];
                $Taksasi[$key][$key1]['br_kiri'] = $merge_baris;
                $Taksasi[$key][$key1]['pokok_produktif'] = $pokok_produktif;
                $Taksasi[$key][$key1]['pokok_janjang'] = $sum_janjang;

                $Taksasi[$key][$key1]['jjg_taksasi'] = $jjg_taksasi;
                $Taksasi[$key][$key1]['nama_ancak'] = $unique_nama_ancak;
                $Taksasi[$key][$key1]['sph'] = $value2['sph'];
                $Taksasi[$key][$key1]['jumlah_path'] = count($value1);
                $Taksasi[$key][$key1]['jumlah_pokok'] = $sum_pokok;
                $Taksasi[$key][$key1]['jumlah_janjang'] = $sum_janjang;
                $Taksasi[$key][$key1]['pemanen'] = $pemanen;
                $Taksasi[$key][$key1]['keb_pemanen_ha_per_hk'] = ceil($total_keb_pemanen_ha_per_hk);
                $Taksasi[$key][$key1]['keb_pemanen_kg_per_hk'] = 0;
                $Taksasi[$key][$key1]['akp'] = $akp;
                $Taksasi[$key][$key1]['output'] = $finalOutput;
                $Taksasi[$key][$key1]['taksasi'] = $tak;
                $Taksasi[$key][$key1]['interval_panen'] = ceil($rotasi / $inc);
                $Taksasi[$key][$key1]['ritase'] = round(($tak / 6500), 2);
            }
        }

        $totalSumBJRBlok = array_sum($sum_all_bjr_blok);
        $totalSumSPHBlok = array_sum($sum_all_sph_blok);

        $takafd = array();

        $incTotalBlok = 0;
        foreach ($Taksasi as $key => $value) {
            $luas = 0;
            $sum_sph = 0;
            $jumlah_path = 0;
            $sum_bjr = 0;
            $jumlah_janjang = 0;
            $jumlah_pokok = 0;
            $pemanen = 0;
            $tak = 0;
            $total_keb_pemanen_kg_per_hk = 0;
            $total_keb_pemanen_ha_per_hk = 0;
            $pokok_produktif = 0;
            $pokok_janjang = 0;
            $rotasi = 0;
            $inc = 0;
            $output = 0;
            foreach ($value as $key1 => $value1) {

                $luas += $value1['luas'];
                $pokok_janjang += $value1['pokok_janjang'];
                $sum_sph += $value1['sph'];
                $jumlah_path += $value1['jumlah_path'];
                $sum_bjr += $value1['bjr'];
                $jumlah_pokok += $value1['jumlah_pokok'];
                $jumlah_janjang += $value1['jumlah_janjang'];
                $pemanen += $value1['pemanen'];
                $output += $value1['output'];

                $rotasi += $value1['interval_panen'];
                $pokok_produktif += $value1['pokok_produktif'];
                $inc++;
                $incTotalBlok++;
            }




            $akp = round(($jumlah_janjang / $jumlah_pokok) * 100, 2);

            $sum_sph = round($sum_sph / count($value), 2);
            // $pokok_produktif = ceil(($jumlah_pokok / $sum_sph / $luas) * 100);
            $final_bjr = round($sum_bjr / count($value), 2);
            $tak = round(($akp * $luas * $final_bjr * $sum_sph) / 100, 1);

            if ($luas > 4.5) {
                if ($output != 0) {
                    $output = round($output / $inc, 2);
                    $total_keb_pemanen_ha_per_hk = round($luas / $output, 2);
                } else {
                    $total_keb_pemanen_ha_per_hk = 0; // or any default value you prefer
                }
            } else {
                $total_keb_pemanen_ha_per_hk = 1;
            }

            if ($output != 0) {
                $total_keb_pemanen_kg_per_hk = round($tak / $output, 2);
            }

            $jjg_taksasi = ceil(($akp * $luas * $sum_sph) / 100);

            $takafd[$key]['luas'] = $luas;
            $takafd[$key]['jumlah_path'] = $jumlah_path;
            $takafd[$key]['sph'] = $sum_sph;
            $takafd[$key]['bjr'] = $final_bjr;
            $takafd[$key]['pokok_produktif'] = $pokok_produktif;
            $takafd[$key]['pokok_janjang'] = $pokok_janjang;
            $takafd[$key]['jumlah_pokok'] = $jumlah_pokok;
            $takafd[$key]['jjg_taksasi'] = $jjg_taksasi;
            $takafd[$key]['jumlah_janjang'] = $jumlah_janjang;
            $takafd[$key]['akp'] = $akp;
            $takafd[$key]['taksasi'] = $tak;
            $takafd[$key]['output'] = $output;
            $takafd[$key]['keb_pemanen_ha_per_hk'] = $total_keb_pemanen_ha_per_hk;
            $takafd[$key]['keb_pemanen_kg_per_hk'] = $total_keb_pemanen_kg_per_hk;
            $takafd[$key]['interval_panen'] = ceil($rotasi / $inc);
            $takafd[$key]['ritase'] = round($tak / 6500, 2);
            $takafd[$key]['pemanenx'] = $pemanen;
        }

        $takest = [];
        $luas = 0;
        $sum_sph = 0;
        $jumlah_path = 0;
        $sum_bjr = 0;
        $jumlah_janjang = 0;
        $jumlah_pokok = 0;
        $pemanen = 0;
        $pokok_produktif = 0;
        $pokok_janjang = 0;
        $tak = 0;
        $keb_pemanen_kg_per_hk_afd = 0;
        $keb_pemanen_ha_per_hk_afd = 0;
        $rotasi = 0;
        $inc = 0;
        foreach ($takafd as $key => $value) {
            $luas += $value['luas'];
            $sum_sph += $value['sph'];
            $pokok_produktif += $value['pokok_produktif'];
            $pokok_janjang += $value['pokok_janjang'];
            $jumlah_path += $value['jumlah_path'];
            $sum_bjr += $value['bjr'];
            $jumlah_pokok += $value['jumlah_pokok'];
            $jumlah_janjang += $value['jumlah_janjang'];
            $pemanen += $value['pemanenx'];
            $rotasi += $value['interval_panen'];
            $inc++;
        }

        if ($jumlah_pokok != 0) {
            $akp = round(($jumlah_janjang / $jumlah_pokok) * 100, 2);
        } else {
            // Handle the case where $jumlah_pokok is zero
            $akp = null; // or any other default value or action you prefer
            // You could also log an error, throw an exception, or handle this case in some other way
        }

        if (count($takafd) != 0) {
            $sum_sph = round($totalSumSPHBlok / $incTotalBlok, 2);
        } else {
            // Handle the case where count($takafd) is zero
            $sum_sph = null; // or any other default value or action you prefer
            // You could also log an error, throw an exception, or handle this case in some other way
        }
        if (count($takafd) != 0) {
            $sum_bjr = round($totalSumBJRBlok / $incTotalBlok, 2);
        } else {
            // Handle the case where count($takafd) is zero
            $sum_bjr = null; // or any other default value or action you prefer
            // You could also log an error, throw an exception, or handle this case in some other way
        }


        $tak = round(($akp * $luas * $sum_bjr * $sum_sph) / 100, 1);
        $jjg_taksasi = ceil(($akp * $luas  * $sum_sph) / 100);
        $output = $incTotalBlok != 0 ?  round($sum_all_output_blok / $incTotalBlok, 2) : 0;
        if ($luas > 4.5) {
            if ($output != 0) {
                $keb_pemanen_ha_per_hk_afd = round($luas / $output, 2);
            } else {
                $keb_pemanen_ha_per_hk_afd = 0; // or any default value you prefer
            }
        } else {
            $keb_pemanen_ha_per_hk_afd = 1;
        }

        if ($output != 0) {
            $keb_pemanen_kg_per_hk_afd = round($tak / $output, 2);
        }

        $takest['luas'] = $luas;
        $takest['jumlah_path'] = $jumlah_path;
        $takest['path'] = count($takafd);
        $takest['sph'] = $sum_sph;
        $takest['bjr'] = $sum_bjr;
        $takest['pokok_produktif'] = $pokok_produktif;
        $takest['pokok_janjang'] = $pokok_janjang;
        $takest['jumlah_pokok'] = $jumlah_pokok;
        $takest['jumlah_janjang'] = $jumlah_janjang;
        $takest['akp'] = $akp;
        $takest['taksasi'] = $tak;
        $takest['jjg_taksasi'] = $jjg_taksasi;
        $takest['keb_pemanen_ha_per_hk'] =  $keb_pemanen_ha_per_hk_afd;
        $takest['keb_pemanen_kg_per_hk'] =  $keb_pemanen_kg_per_hk_afd;

        if ($inc != 0) {
            $takest['interval_panen'] = ceil($rotasi / $inc);
        } else {
            $takest['interval_panen'] = $rotasi;
        }
        $takest['ritase'] = round($tak / 6500, 2);
        $takest['pemanenx'] = $pemanen;




        $besok = Carbon::parse($tgl)->addDays()->format('Y-m-d');

        $besokFormatted = strtoupper($this->tanggal_indo($besok, true));

        $todayFormatted = strtoupper($this->tanggal_indo($tgl, true));

        switch ($queryEstate->wil) {
            case 1:
                $wil = 'I';
                break;
            case 2:
                $wil = 'II';
                break;
            case 3:
                $wil = 'III';
                break;

            default:
                # code...
                break;
        }
        $rom = DB::connection('mysql2')->table('estate')
            ->select('estate.*')
            ->where('est', $estate_input)
            ->pluck('wil');

        function convertToRoman2($number)
        {
            $map = [
                'M' => 1000,
                'CM' => 900,
                'D' => 500,
                'CD' => 400,
                'C' => 100,
                'XC' => 90,
                'L' => 50,
                'XL' => 40,
                'X' => 10,
                'IX' => 9,
                'V' => 5,
                'IV' => 4,
                'I' => 1,
            ];

            $result = '';
            foreach ($map as $roman => $value) {
                $matches = intval($number / $value);
                $result .= str_repeat($roman, $matches);
                $number %= $value;
            }

            return $result;
        }
        $romanNumeral = convertToRoman2($rom[0]);
        $pathImage = storage_path('app/public/maps/maps_' . $estate_input . '_' . $tgl . '.jpg');
        // dd($pathImage);
        $pdf = pdf::loadview('taksasi.cetak', [
            'est' => $estate_input,
            'tgl' => $tgl,
            'namaEstate' => strtoupper($queryEstate->nama),
            'wil' => $romanNumeral,
            'today' => $todayFormatted,
            'besok' => $besokFormatted,
            'new_tak' => $Taksasi,
            'afd_tak' => $takafd,
            'takest' => $takest,
            'image' => $pathImage
        ]);
        $pdf->set_paper('A3', 'landscape');

        $filename = 'Taksasi-' . $estate_input . '-' . $tgl . '.pdf';

        if (!is_null($web)) {
            return $pdf->stream($filename);
        } else {
            $pdfContent = $pdf->output();
            $base64Pdf = base64_encode($pdfContent);
            return response()->json([
                'base64_pdf' => $base64Pdf
            ]);
        }
    }


    public function generateMaps($est, $date)
    {
        $namaEstate = $est;
        $datetime = $date;
        $startDate = $datetime;
        $endDate = date('Y-m-d', strtotime($datetime . ' +1 day'));

        // Get taksasi data
        $taksasiData = DB::connection('mysql2')->table('taksasi')
            ->whereBetween('waktu_upload', [$startDate, $endDate])
            ->where('lokasi_kerja', $namaEstate)
            ->orderBy('afdeling')
            ->orderBy('blok')
            ->get();

        // Process arrData
        $arr = [];
        $inc = 0;
        foreach ($taksasiData as $value) {
            $est = DB::connection('mysql2')->table('estate')
                ->where('nama', $value->lokasi_kerja)
                ->first()->est ?? $value->lokasi_kerja;

            // Process coordinates and build arr array
            // [Your existing coordinate processing logic here]
        }

        $arr = [];
        $inc = 0;
        foreach ($taksasiData as $value) {
            $est = DB::connection('mysql2')->table('estate')
                ->where('nama', $value->lokasi_kerja)
                ->first()->est ?? $value->lokasi_kerja;

            $check = explode(';', $value->br_kiri);

            if (count($check) != 1) {
                $lat_awal_exp = explode(';', $value->lat_awal);
                $lon_awal_exp = explode(';', $value->lon_awal);
                $lat_akhir_exp = explode(';', $value->lat_akhir);
                $lon_akhir_exp = explode(';', $value->lon_akhir);
                $name_exp = explode(';', $value->name);

                for ($i = 0; $i < count($check); $i++) {
                    if (array_key_exists($i, $name_exp)) {
                        $arr[$inc]['name'] = $name_exp[$i];
                    } else {
                        $arr[$inc]['name'] = $value->name;
                    }

                    $arr[$inc]['afdeling'] = $value->afdeling;
                    $arr[$inc]['lokasi_kerja'] = $est;
                    $arr[$inc]['blok'] = $value->blok;

                    if (!empty($lat_awal_exp[$i]) && !empty($lon_awal_exp[$i]) && !empty($lat_akhir_exp[$i]) && !empty($lon_akhir_exp[$i])) {
                        $arr[$inc]['plot'] = '[' . $lon_awal_exp[$i] . ',' . $lat_awal_exp[$i] . '],[' . $lon_akhir_exp[$i] . ',' . $lat_akhir_exp[$i] . ']';
                        $arr[$inc]['plotAwal'] = '[' . $lat_awal_exp[$i] . ',' . $lon_awal_exp[$i] . ']';
                        $arr[$inc]['plotAkhir'] = '[' . $lat_akhir_exp[$i] . ',' . $lon_akhir_exp[$i] . ']';
                        $arr[$inc]['latins'] = $lat_akhir_exp[$i] . ',' . $lon_akhir_exp[$i];
                        $inc++;
                    }
                }
            } else {
                $arr[$inc]['name'] = $value->name;
                $arr[$inc]['blok'] = $value->blok;

                if (!empty($value->lat_awal) && !empty($value->lon_awal)) {
                    if (strpos($value->lat_awal, ';') !== false && strpos($value->lon_awal, ';') !== false) {
                        $lat_awals = explode(';', $value->lat_awal);
                        $lon_awals = explode(';', $value->lon_awal);
                        $lat_akhirs = explode(';', $value->lat_akhir);
                        $lon_akhirs = explode(';', $value->lon_akhir);

                        if (count($lat_awals) === count($lon_awals)) {
                            $plotPairs = [];
                            for ($i = 0; $i < count($lat_awals); $i++) {
                                $plotPairs[] = '[' . $lon_awals[$i] . ',' . $lat_awals[$i] . ']';
                            }

                            $arr[$inc]['plot'] = implode(',', $plotPairs);
                            $arr[$inc]['plotAwal'] = '[' . $lat_awals[0] . ',' . $lon_awals[0] . ']';
                            $arr[$inc]['plotAkhir'] = '[' . $lat_awals[count($lat_awals) - 1] . ',' . $lon_awals[count($lon_awals) - 1] . ']';
                            $arr[$inc]['afdeling'] = $value->afdeling;
                            $arr[$inc]['latins'] = '[' . $lat_awals[count($lat_awals) - 1] . ',' . $lon_awals[count($lon_awals) - 1] . ']';
                            $arr[$inc]['lokasi_kerja'] = $est;
                            $inc++;
                        } else {
                            $arr[$inc]['plot'] = 'Invalid data; mismatched lat_awal and lon_awal coordinates';
                            $arr[$inc]['plotAwal'] = 'Invalid data';
                            $arr[$inc]['plotAkhir'] = 'Invalid data';
                            $arr[$inc]['afdeling'] = 'Invalid data';
                            $arr[$inc]['latins'] = 'Invalid data';
                            $arr[$inc]['lokasi_kerja'] = 'Invalid data';
                            $inc++;
                        }
                    } else {
                        $arr[$inc]['plot'] = '[' . $value->lon_awal . ',' . $value->lat_awal . '],[' . $value->lon_akhir . ',' . $value->lat_akhir . ']';
                        $arr[$inc]['plotAwal'] = '[' . $value->lat_awal . ',' . $value->lon_awal . ']';
                        $arr[$inc]['plotAkhir'] = '[' . $value->lat_akhir . ',' . $value->lon_akhir . ']';
                        $arr[$inc]['afdeling'] = $value->afdeling;
                        $arr[$inc]['latins'] = $value->lat_akhir . ',' . $value->lon_akhir;
                        $arr[$inc]['lokasi_kerja'] = $est;
                        $inc++;
                    }
                }
            }
        }

        // Build list arrays
        $list_afd = [];
        $list_estate = [];
        foreach ($arr as $key => $value) {
            if (!in_array($value['lokasi_kerja'], $list_estate)) {
                $list_estate[] = $value['lokasi_kerja'];
            }
            if (!in_array($value['afdeling'], $list_afd)) {
                $list_afd[] = $value['afdeling'];
            }
        }


        // Build userTaksasi
        $user = [];
        foreach ($list_afd as $key2 => $data) {
            foreach ($arr as $key => $value) {
                if ($data == $value['afdeling']) {
                    $user[$data][] = $value['name'];
                }
            }
        }

        $userTaksasi = [];
        foreach ($user as $key => $value) {
            $userTaksasi[$key] = array_values(array_unique($value));
        }


        // Build estate_plot
        $estate_plot = [];
        foreach ($list_estate as $key => $value) {
            $estatePlotData = DB::connection('mysql2')
                ->table('estate_plot')
                ->join('estate', 'estate_plot.est', '=', 'estate.est')
                ->where('estate_plot.est', $value)
                ->get();

            $plot = '';
            foreach ($estatePlotData as $val3) {
                $plot .= '[' . $val3->lon . ',' . $val3->lat . '],';
                $estate = $val3->nama;
                $estate_plot[$value]['number'][] = '[' . $val3->lat . ',' . $val3->lon . ']';
            }
            $estate_plot[$value]['est'] = $estate . ' Estate';
            $estate_plot[$value]['plot'] = rtrim($plot, ',');
        }


        // Build blokLatLn using your existing logic
        // Get estate and afdeling data
        $blokPerEstate = [];
        $arrAfd = [];
        $listIdAfd = [];

        foreach ($list_estate as $key => $val) {
            $estateData = DB::connection('mysql2')->table('estate as e')
                ->join('afdeling as a', 'e.id', '=', 'a.estate')
                ->where('e.est', $val)
                ->get();

            foreach ($estateData as $value) {
                $arrAfd[$val][$value->nama] = $value->id;
                $listIdAfd[] = $value->id;
            }

            foreach ($arrAfd[$val] as $key2 => $data) {
                $blokData = DB::connection('mysql2')->table('blok')
                    ->where('afdeling', $data)
                    ->get();

                foreach ($blokData as $val3) {
                    $blokPerEstate[$val][$key2][] = $val3->nama;
                }
            }
        }

        // Build arrData and list_blok
        $arrData = [];
        $list_blok = [];
        foreach ($list_estate as $key => $value) {
            foreach ($arr as $key2 => $val) {
                if ($val['lokasi_kerja'] == $value) {
                    $arrData[$value][] = $val;
                    $list_blok[$value][] = $val['blok'];
                }
            }
        }

        // Get markers data
        $data = DB::connection('mysql2')->table('taksasi')
            ->whereDate('waktu_upload', $datetime)
            ->where('lokasi_kerja', $namaEstate)
            ->orderBy('waktu_upload', 'DESC')
            ->get();

        $markers = [];
        foreach ($data as $row) {
            $lat = floatval($row->lat_awal);
            $lon = floatval($row->lon_awal);
            $markers[] = [$lat, $lon];
        }
        $markers = array_values($markers);

        // Get polygon data
        $dataafd = DB::connection('mysql2')->table('blok')
            ->select('blok.nama', 'blok.lat', 'blok.lon', 'blok.id')
            ->join('afdeling', 'afdeling.id', '=', 'blok.afdeling')
            ->join('estate', 'estate.id', '=', 'afdeling.estate')
            ->where('estate.est', $namaEstate)
            ->get();

        $polygons = [];
        $listBlok = [];
        foreach ($dataafd as $value2) {
            $nama = $value2->nama;
            $latln = $value2->lat . ',' . $value2->lon;

            if (!isset($polygons[$nama])) {
                $polygons[$nama] = $latln;
                $listBlok[] = $nama;
            } else {
                $polygons[$nama] .= '$' . $latln;
            }
        }

        $polygons = array_values($polygons);
        $finalResultBlok = [];

        foreach ($polygons as $key => $polygon) {
            foreach ($markers as $marker) {
                if ($this->isPointInPolygon($marker, $polygon)) {
                    $finalResultBlok[] = $listBlok[$key];
                }
            }
        }

        $finalResultBlok = array_unique($finalResultBlok);

        // Get latin data
        $datalatin = DB::connection('mysql2')->table('blok')
            ->select('blok.nama', 'blok.lat', 'blok.lon', 'blok.id', 'afdeling.nama as namaafd')
            ->join('afdeling', 'afdeling.id', '=', 'blok.afdeling')
            ->join('estate', 'estate.id', '=', 'afdeling.estate')
            ->where('estate.est', $namaEstate)
            ->whereIn('blok.nama', $finalResultBlok)
            ->get();

        // Group data by nama
        $groupedData = [];
        foreach ($datalatin as $item) {
            $nama = $item->nama;
            if (!isset($groupedData[$nama])) {
                $groupedData[$nama] = [];
            }
            $groupedData[$nama][] = $item;
        }

        // Build blokLatLnEw
        $blokLatLnEw = [];
        $inc = 0;
        foreach ($groupedData as $key => $value) {
            $latln = '';
            $latln2 = '';
            foreach ($value as $value2) {
                $latln .= $value2->lat . ',' . $value2->lon . '$';
                $latln2 .= '[' . $value2->lon . ',' . $value2->lat . '],';
            }

            $blokLatLnEw[$inc] = [
                'blok' => $key,
                'afd' => $value2->namaafd,
                'estate' => $namaEstate,
                'latln' => rtrim($latln, '$'),
                'latinnew' => rtrim($latln2, ',')
            ];
            $inc++;
        }

        $blokLatLn = [];
        foreach ($blokLatLnEw as $value) {
            $blokLatLn[$namaEstate][] = [
                'blok' => $value['blok'],
                'estate' => $namaEstate,
                'afdeling' => $value['afd'],
                'latln' => $value['latinnew']
            ];
        }


        return view('taksasi.generateMaps', [
            'arrData' => $arrData,
            'estate_plot' => $estate_plot,
            'userTaksasi' => $userTaksasi,
            'blokLatLn' => $blokLatLn,
            'datetime' => $datetime
        ]);
    }

    private function isPointInPolygon($point, $polygon)
    {
        $x = $point[0];
        $y = $point[1];

        $vertices = array_map(function ($vertex) {
            return explode(',', $vertex);
        }, explode('$', $polygon));

        $numVertices = count($vertices);
        $isInside = false;

        for ($i = 0, $j = $numVertices - 1; $i < $numVertices; $j = $i++) {
            $xi = $vertices[$i][0];
            $yi = $vertices[$i][1];
            $xj = $vertices[$j][0];
            $yj = $vertices[$j][1];

            $intersect = (($yi > $y) != ($yj > $y)) && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $isInside = !$isInside;
            }
        }
        return $isInside;
    }
    private function tanggal_indo($tanggal, $cetak_hari = false)
    {
        $hari = array(
            1 =>    'Senin',
            'Selasa',
            'Rabu',
            'Kamis',
            'Jumat',
            'Sabtu',
            'Minggu'
        );

        $bulan = array(
            1 =>   'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        );
        $split       = explode('-', $tanggal);
        $tgl_indo = $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];

        if ($cetak_hari) {
            $num = date('N', strtotime($tanggal));
            return $hari[$num] . ', ' . $tgl_indo;
        }
        return $tgl_indo;
    }

    public function saveMapImage(Request $request)
    {
        try {
            $imageData = $request->input('imageData');
            $estate = $request->input('estate');
            $datetime = $request->input('datetime');

            // Remove the data URL prefix to get just the base64 data
            $base64Image = preg_replace('#^data:image/[^;]+;base64,#', '', $imageData);

            // Decode base64 data
            $imageData = base64_decode($base64Image);

            // Generate filename
            $filename = "maps_{$estate}_{$datetime}.jpg";

            // Save to storage (you can change 'public' to any other disk configured in config/filesystems.php)
            Storage::disk('public')->put("maps/{$filename}", $imageData);

            return response()->json([
                'success' => true,
                'message' => 'Image saved successfully',
                'path' => "storage/maps/{$filename}"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving image: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteMapImage($filename)
    {
        try {
            $filepath = "maps/{$filename}";

            if (Storage::disk('public')->exists($filepath)) {
                Storage::disk('public')->delete($filepath);
                return response()->json([
                    'success' => true,
                    'message' => 'Image deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Image not found'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting image: ' . $e->getMessage()
            ], 500);
        }
    }
}
