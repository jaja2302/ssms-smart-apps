<?php

namespace App\Http\Controllers;

use App\Imports\RealisasiTaksasiImport;
use App\Models\Afdeling;
use App\Models\Estate;
use App\Models\Regional;
use App\Models\Wilayah;
use App\Services\DataServiceImportRealisasi;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as FacadesExcel;

class DashboardNewController extends Controller
{


    protected $dataService;
    protected $finaldataTaksasiRealisasiGM;
    protected $finaldataProdAfdInti;

    public function __construct(DataServiceImportRealisasi $dataService, DataServiceImportRealisasi $finaldataTaksasiRealisasiGM, DataServiceImportRealisasi $finaldataProdAfdInti)
    {
        $this->dataService = $dataService;
        $this->finaldataTaksasiRealisasiGM = $finaldataTaksasiRealisasiGM;
        $this->finaldataProdAfdInti = $finaldataProdAfdInti;
    }

    public function getAllDataRegionalWilayah(Request $request)
    {
        $tglData = $request->get('tgl_request');
        $idReg = $request->get('id_reg');



        $specificReg = 'Regional I';
        $specificWil = 'Wilayah 3';
        $specificEst = 'NBE';

        $reg_all = Regional::all()->where('nama', '=', $specificReg)->where('nama', '!=', 'Regional V')->toArray();


        $queryListWil = Wilayah::select('nama')
            // ->where('regional', $reg_all[$idReg]['id'])
            ->where('nama', $specificWil)
            ->pluck('nama');
        // dd($queryListWil);

        $queryListWilId = Wilayah::select('id')
            // ->where('regional', $reg_all[$idReg]['id'])
            ->where("nama", $specificWil)
            ->pluck('id');
        // dd($queryListWilId);

        $queryListEstate = Estate::whereIn('wil', $queryListWilId)
            ->select('estate.*', 'wil.nama as nama_wilayah')
            ->join('wil', 'estate.wil', 'wil.id')
            ->where(function ($query) {
                $query->where(DB::raw('LOWER(estate.nama)'), 'NOT LIKE', '%mill%')
                    ->where(DB::raw('LOWER(estate.est)'), 'NOT LIKE', '%plasma%')
                    ->where(DB::raw('LOWER(estate.est)'), 'NOT LIKE', '%cws1%')
                    ->where(DB::raw('LOWER(estate.est)'), 'NOT LIKE', '%cws2%')
                    ->where(DB::raw('LOWER(estate.est)'), 'NOT LIKE', '%cws3%')
                    ->where(DB::raw('LOWER(estate.est)'), 'NOT LIKE', '%reg%')
                    ->where(DB::raw('LOWER(estate.est)'), 'NOT LIKE', '%srs%')
                    ->where(DB::raw('LOWER(estate.est)'), 'NOT LIKE', '%sr%')
                    ->where(DB::raw('LOWER(estate.est)'), 'NOT LIKE', '%tc%');
            })
            // ->pluck('est', 'nama')
            ->where('estate.est', $specificEst)
            ->get()
            ->toArray();




        $first = $tglData . ' 00:00:00';
        $last = $tglData . ' 23:59:59';
        $firstData = new Carbon($first);
        $lastData = new Carbon($last);

        $queryByDateRegional = DB::connection('mysql2')->table('taksasi')
            ->select('taksasi.*', 'estate.wil', 'wil.regional', "reg.nama as nama_regional")
            ->join('estate', 'taksasi.lokasi_kerja', '=', 'estate.est')
            ->join('wil', 'estate.wil', '=', 'wil.id')
            ->join('reg', 'wil.regional', '=', 'reg.id')
            // ->whereBetween('taksasi.waktu_upload', [$firstData, $lastData])
            ->whereDate('taksasi.waktu_upload', $tglData)
            ->where('reg.nama', '=', $specificReg)
            ->where('taksasi.lokasi_kerja', $specificEst)
            ->where('reg.nama', '!=', 'Regional V')
            // ->where('reg.nama', $reg_all[$idReg]['nama'])
            ->get()
            ->groupBy('nama_regional');


        $queryByDateWilayah = DB::connection('mysql2')->table('taksasi')
            ->select('taksasi.*',  'wil.nama as nama_wilayah')
            ->join('estate', 'taksasi.lokasi_kerja', '=', 'estate.est')
            ->join('wil', 'estate.wil', '=', 'wil.id')
            ->where('taksasi.lokasi_kerja', $specificEst)
            // ->whereBetween('taksasi.waktu_upload', [$firstData, $lastData])
            ->whereDate('taksasi.waktu_upload', $tglData)
            ->whereIn('wil.nama', $queryListWil)
            ->get()
            ->groupBy('nama_wilayah');




        $estValues = collect($queryListEstate)->pluck('est', 'nama');

        $queryByDateEstate = DB::connection('mysql2')->table('taksasi')
            ->select('taksasi.*',  'wil.nama as nama_wilayah')
            ->join('estate', 'taksasi.lokasi_kerja', '=', 'estate.est')
            ->join('wil', 'estate.wil', '=', 'wil.id')
            // ->whereBetween('taksasi.waktu_upload', [$firstData, $lastData])
            ->whereDate('taksasi.waktu_upload', $tglData)
            ->whereIn('taksasi.lokasi_kerja', $estValues)

            ->get()
            ->groupBy('lokasi_kerja');


        $dataFinalRegional = [];
        foreach ($reg_all as $key => $value) {
            $dataFinalRegional[$value['nama']] = [
                'regional' => $value['nama'],
                'luas' => "-",
                'jumlahBlok' => "-",
                'ritase' => "-",
                'akp' => "-",
                'taksasi' => "-",
                'keb_pemanen' => "-"
            ];
        }


        $dataFinalWilayah = [];
        foreach ($queryListWil as $key => $value) {
            // Initialize with default values
            $dataFinalWilayah[$value] = [
                'wilayah' => $value,
                'luas' => "-",
                'jumlahBlok' => "-",
                'ritase' => "-",
                'akp' => "-",
                'taksasi' => "-",
                'keb_pemanen' => "-"
            ];
        }

        $dataFinalEstate = [];
        foreach ($queryListEstate as $key => $value) {
            $dataFinalEstate[$value['est']] = [
                'estate' => $value['est'],
                'nama_wil' => $value['nama_wilayah'],
                'luas' => "-",
                'jumlahBlok' => "-",
                'ritase' => "-",
                'akp' => "-",
                'taksasi' => "-",
                'keb_pemanen' => "-"
            ];
        }

        foreach ($queryByDateRegional as $key => $value) {
            $jumlahBlok = 0;
            $luasTotal = 0;
            $jum_pokok = 0;
            $jum_janjang = 0;
            $jum_sph = 0;
            $jum_bjr = 0;
            $pemanen = 0;
            $output = 0;
            $inc = 0;
            $seenBlocks = []; // Store unique blocks
            $jumBlokSama = 0;
            $blockData = []; // To store total output and count for each block
            foreach ($value as $key2 => $value2) {
                $blok = $value2->blok;
                $currentOutput = $value2->output;

                // Track block data
                if (!isset($blockData[$blok])) {
                    $blockData[$blok] = [
                        'totalOutput' => 0,
                        'count' => 0
                    ];
                }
                $blockData[$blok]['totalOutput'] += $currentOutput;
                $blockData[$blok]['count'] += 1;
                if (!in_array($value2->blok, $seenBlocks)) {
                    $luasTotal += $value2->luas;
                    $jum_sph += $value2->sph;
                    $jum_bjr +=  $value2->bjr_sensus != 0 ? $value2->bjr_sensus : $value2->bjr;
                    $seenBlocks[] = $value2->blok; // Mark block as seen
                    $jumBlokSama++;
                }

                $jum_pokok += $value2->jumlah_pokok;
                $jum_janjang += $value2->jumlah_janjang;
                $pemanen += $value2->pemanen;
                $jumlahBlok += 1;
                $output += $value2->output;
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

            $output = round($finalOutput / $jumBlokSama, 2);

            $rerata_sph = round($jum_sph / $jumBlokSama, 2);
            $rerata_bjr = round($jum_bjr / $jumBlokSama, 2);
            $akp = round(($jum_janjang / $jum_pokok) * 100, 2);
            $tak =  round(($akp * $luasTotal * $rerata_bjr * $rerata_sph) / 100, 2);

            $keb_pemanen_kg_per_hk = $output ? round($tak / $output, 2) : 0;

            $dataFinalRegional[$key]['luas'] = $this->custom_number_format(round($luasTotal, 2));
            $dataFinalRegional[$key]['jumlahBlok'] = $this->custom_number_format($jumlahBlok);
            $dataFinalRegional[$key]['ritase'] = $this->custom_number_format(round($tak / 6500, 2));
            $dataFinalRegional[$key]['akp'] = $this->custom_number_format($akp);
            $dataFinalRegional[$key]['taksasi'] = $this->custom_number_format($tak);
            $dataFinalRegional[$key]['keb_pemanen'] = $this->custom_number_format($keb_pemanen_kg_per_hk);
        }


        foreach ($queryByDateWilayah as $key => $value) {
            $jumlahBlok = 0;
            $luasTotal = 0;
            $jum_pokok = 0;
            $jum_janjang = 0;
            $jum_sph = 0;
            $jum_bjr = 0;
            $pemanen = 0;
            $output = 0;
            $inc = 0;
            $seenBlocks = []; // Store unique blocks
            $jumBlokSama = 0;
            $blockData = []; // To store total output and count for each block
            foreach ($value as $key2 => $value2) {
                $blok = $value2->blok;
                $currentOutput = $value2->output;

                // Track block data
                if (!isset($blockData[$blok])) {
                    $blockData[$blok] = [
                        'totalOutput' => 0,
                        'count' => 0
                    ];
                }
                $blockData[$blok]['totalOutput'] += $currentOutput;
                $blockData[$blok]['count'] += 1;
                if (!in_array($value2->blok, $seenBlocks)) {
                    $luasTotal += $value2->luas;
                    $jum_sph += $value2->sph;
                    $jum_bjr +=  $value2->bjr_sensus != 0 ? $value2->bjr_sensus : $value2->bjr;
                    $seenBlocks[] = $value2->blok; // Mark block as seen
                    $jumBlokSama++;
                }

                $jum_pokok += $value2->jumlah_pokok;
                $jum_janjang += $value2->jumlah_janjang;
                $pemanen += $value2->pemanen;
                $output += $value2->output;
                $jumlahBlok += 1;
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

            $output = round($finalOutput / $jumBlokSama, 2);

            $rerata_sph = round($jum_sph / $jumBlokSama, 2);
            $rerata_bjr = round($jum_bjr / $jumBlokSama, 2);
            $akp = round(($jum_janjang / $jum_pokok) * 100, 2);
            $tak =  round(($akp * $luasTotal * $rerata_bjr * $rerata_sph) / 100, 1);


            $keb_pemanen_kg_per_hk = $output ? round($tak / $output, 2) : 0;

            $dataFinalWilayah[$key]['luas'] = $this->custom_number_format(round($luasTotal, 2));
            $dataFinalWilayah[$key]['jumlahBlok'] = $this->custom_number_format($jumlahBlok);
            $dataFinalWilayah[$key]['ritase'] = $this->custom_number_format(round($tak / 6500, 2));
            $dataFinalWilayah[$key]['akp'] = $this->custom_number_format($akp);
            $dataFinalWilayah[$key]['taksasi'] = $this->custom_number_format($tak);
            $dataFinalWilayah[$key]['keb_pemanen'] = $this->custom_number_format($keb_pemanen_kg_per_hk);
        }

        foreach ($queryByDateEstate as $key => $value) {
            $jumlahBlok = 0;
            $luasTotal = 0;
            $jum_pokok = 0;
            $jum_janjang = 0;
            $jum_sph = 0;
            $jum_bjr = 0;
            $pemanen = 0;
            $output = 0;
            $inc = 0;

            // Arrays to track blocks and their outputs
            $blockData = []; // To store total output and count for each block
            $seenBlocks = []; // To store unique blocks (for existing logic)
            $jumBlokSama = 0;

            foreach ($value as $key2 => $value2) {
                $blok = $value2->blok;
                $currentOutput = $value2->output;

                // Track block data
                if (!isset($blockData[$blok])) {
                    $blockData[$blok] = [
                        'totalOutput' => 0,
                        'count' => 0
                    ];
                }
                $blockData[$blok]['totalOutput'] += $currentOutput;
                $blockData[$blok]['count'] += 1;

                // Existing logic for unique blocks
                if (!in_array($blok, $seenBlocks)) {
                    $luasTotal += $value2->luas;
                    $jum_sph += $value2->sph;
                    $jum_bjr += $value2->bjr_sensus != 0 ? $value2->bjr_sensus : $value2->bjr;
                    $seenBlocks[] = $blok;
                    $jumBlokSama++;
                }

                // Accumulate other values
                $jum_pokok += $value2->jumlah_pokok;
                $jum_janjang += $value2->jumlah_janjang;
                $pemanen += $value2->pemanen;
                $jumlahBlok += 1;
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

            $output = round($finalOutput / $jumBlokSama, 2);

            $rerata_sph = round($jum_sph / $jumBlokSama, 2);
            $rerata_bjr = round($jum_bjr / $jumBlokSama, 2);
            $akp = round(($jum_janjang / $jum_pokok) * 100, 2);
            $tak =  round(($akp * $luasTotal * $rerata_bjr * $rerata_sph) / 100, 1);

            $keb_pemanen_kg_per_hk = $output ? round($tak / $output, 2) : 0;

            $dataFinalEstate[$key]['luas'] = $this->custom_number_format(round($luasTotal, 2));
            $dataFinalEstate[$key]['jumlahBlok'] = $jumlahBlok;
            $dataFinalEstate[$key]['ritase'] = $this->custom_number_format(round($tak / 6500, 2));
            $dataFinalEstate[$key]['akp'] = $this->custom_number_format($akp);
            $dataFinalEstate[$key]['taksasi'] =  $this->custom_number_format($tak);
            $dataFinalEstate[$key]['total_bjr'] =  $this->custom_number_format($jum_bjr);
            $dataFinalEstate[$key]['keb_pemanen'] = $keb_pemanen_kg_per_hk;
        }


        $result['data_reg'] = $dataFinalRegional;
        $result['data_wil'] = $dataFinalWilayah;
        $result['data_est'] = $dataFinalEstate;

        echo json_encode($result);
        exit;
    }


    public function getAllDataEstate(Request $request)
    {
        $tglData = $request->get('tgl_request');
        $estate = $request->get('estate_request');

        $first = $tglData . ' 00:00:00';
        $last = $tglData . ' 23:59:59';
        $firstData = new Carbon($first);
        $lastData = new Carbon($last);

        $queryListAfdeling = Estate::where('est', $estate)
            ->join('afdeling', 'estate.id', '=', 'afdeling.estate')
            ->pluck('afdeling.nama')->toArray();

        $queryByDateEstate = DB::connection('mysql2')->table('taksasi')
            ->select('taksasi.*', 'estate.wil')
            ->join('estate', 'taksasi.lokasi_kerja', '=', 'estate.est')
            ->whereBetween('taksasi.waktu_upload', [$firstData, $lastData])
            ->where('taksasi.lokasi_kerja', $estate)
            ->get()
            ->groupBy('afdeling');

        $dataFinalEstate = [];

        foreach ($queryListAfdeling as $key => $nama) {
            // Initialize with default values
            $dataFinalEstate[$nama] = [
                'afdeling' => $nama,
                'luas' => "-",
                'jumlahBlok' => "-",
                'ritase' => "-",
                'akp' => "-",
                'taksasi' => "-",
                'keb_pemanen' => "-"
            ];
        }

        foreach ($queryByDateEstate as $key => $value) {
            $jumlahBlok = 0;
            $luasTotal = 0;
            $jum_pokok = 0;
            $jum_janjang = 0;
            $jum_sph = 0;
            $jum_bjr = 0;
            $pemanen = 0;
            $processedBlokYgSama = [];
            $total_keb_pemanen_ha_per_hk = 0;
            $jumlahBlokSama = 0;

            $blockData = []; // To store total output and count for each block
            foreach ($value as $key2 => $value2) {
                $blok = $value2->blok;
                $currentOutput = $value2->output;
                // Track block data
                if (!isset($blockData[$blok])) {
                    $blockData[$blok] = [
                        'totalOutput' => 0,
                        'count' => 0
                    ];
                }
                $blockData[$blok]['totalOutput'] += $currentOutput;
                $blockData[$blok]['count'] += 1;

                if (!in_array($value2->blok, $processedBlokYgSama)) {
                    $luasTotal += $value2->luas;
                    $processedBlokYgSama[] = $value2->blok;
                    $jum_bjr += $value2->bjr_sensus != 0 ? $value2->bjr_sensus : $value2->bjr;
                    $jum_sph += $value2->sph;
                    $jumlahBlokSama++;
                }
                // $jum_bjr += $value2->bjr_sensus != 0 ? $value2->bjr_sensus : $value2->bjr;
                $jum_pokok += $value2->jumlah_pokok;
                $jum_janjang += $value2->jumlah_janjang;
                $pemanen += $value2->pemanen;
                $jumlahBlok += 1;
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

            $finalOutput = round($finalOutput / $jumlahBlokSama, 2);



            $rerata_sph = round($jum_sph / $jumlahBlokSama, 2);
            $rerata_bjr = round($jum_bjr / $jumlahBlokSama, 2);
            $akp = round(($jum_janjang / $jum_pokok) * 100, 2);
            $tak =  round(($akp * $luasTotal * $rerata_bjr * $rerata_sph) / 100, 2);


            if ($luasTotal > 4.5) {
                if ($finalOutput != 0) {
                    $total_keb_pemanen_ha_per_hk = round($tak / $finalOutput, 2);
                } else {
                    $total_keb_pemanen_ha_per_hk = 0; // or any default value you prefer
                }
            } else {
                $total_keb_pemanen_ha_per_hk = 1;
            }

            $dataFinalEstate[$key]['luas'] =  $this->custom_number_format(round($luasTotal, 2));
            $dataFinalEstate[$key]['jumlahBlok'] =  $this->custom_number_format($jumlahBlok);
            $dataFinalEstate[$key]['ritase'] =  $this->custom_number_format(round($tak / 6500, 2));
            $dataFinalEstate[$key]['akp'] =  $this->custom_number_format($akp);
            $dataFinalEstate[$key]['taksasi'] =  $this->custom_number_format($tak);
            $dataFinalEstate[$key]['keb_pemanen'] =  $this->custom_number_format($total_keb_pemanen_ha_per_hk);
        }



        $result['data_estate'] = $dataFinalEstate;

        echo json_encode($result);
        exit;
    }



    public function getDataRealisasiTaksasi(Request $request)
    {

        $date_request = $request->get('date_request');
        $id_reg = $request->get('id_reg');

        $startDateString = $date_request . ' 00:00:00';
        $endDateString = $date_request . ' 23:59:59';
        $yearMonth = substr($date_request, 0, 7); // '2024-06'
        $firstDayOfMonth = $yearMonth . '-01'; // '2024-06-01'
        $startDateSHI = new DateTime($firstDayOfMonth);
        $endDateSHI = new DateTime($date_request);


        $id_reg = $request->get('id_reg');

        $reg_all = Regional::where('nama', '!=', 'Regional V')->get()->toArray();

        $id_reg = $reg_all[$id_reg]['id'];

        $name_reg = Regional::where('id', $id_reg)->first()->nama;

        $wil_id = Wilayah::where('regional', $id_reg)->where('nama', 'Wilayah 3')->pluck('id')->toArray();

        $query = Afdeling::select('afdeling.nama as nama_afdeling', 'estate.est as nama_est', 'wil.id as nama_wil')
            ->join('estate', 'afdeling.estate', 'estate.id')
            ->join('wil', 'estate.wil', 'wil.id')->where(function ($query) {
                $query->where(DB::raw('LOWER(estate.nama)'), 'NOT LIKE', '%mill%')
                    ->where(DB::raw('LOWER(estate.est)'), 'NOT LIKE', '%plasma%')
                    ->where(DB::raw('LOWER(estate.est)'), 'NOT LIKE', '%cws1%')
                    ->where(DB::raw('LOWER(estate.est)'), 'NOT LIKE', '%cws2%')
                    ->where(DB::raw('LOWER(estate.est)'), 'NOT LIKE', '%cws3%')
                    ->where(DB::raw('LOWER(estate.est)'), 'NOT LIKE', '%reg%')
                    ->where(DB::raw('LOWER(estate.est)'), 'NOT LIKE', '%srs%')
                    ->where(DB::raw('LOWER(estate.est)'), 'NOT LIKE', '%sr%')
                    ->where(DB::raw('LOWER(estate.est)'), 'NOT LIKE', '%tc%');
            })
            ->whereIn('estate.wil', $wil_id)
            ->where('estate.est', 'NBE')
            ->get()
            ->groupBy('nama_est')
            ->toArray();

        $resultEstWithAfd = [];

        $listEstPerWil = Estate::select('estate.*', 'wil.nama as nama_wilayah')
            ->join('wil', 'estate.wil', 'wil.id')
            ->whereIn('estate.wil', $wil_id)
            ->get()->groupBy("nama_wilayah")->toArray();

        $listEstate = [];
        foreach ($query as $nama_est => $afdelingArray) {
            foreach ($afdelingArray as $index => $afdeling) {
                $resultEstWithAfd[$nama_est][$index] = $afdeling['nama_afdeling'];
            }

            $listEstate[] = $nama_est;
        }

        $dataRawRealisasiSHI = DB::connection('mysql2')->table('realisasi_taksasi')
            ->select('*')
            ->whereBetween('tanggal_realisasi', [$firstDayOfMonth, $endDateString])
            ->whereIn('est', $listEstate)
            ->get()
            ->groupBy('tanggal_realisasi')
            ->map(function ($itemsByTanggal) {
                return $itemsByTanggal->groupBy('est')->map(function ($itemsByEst) {
                    return $itemsByEst->groupBy('afd');
                });
            })
            ->toArray();

        $queryDataRealisasi = DB::connection('mysql2')->table('realisasi_taksasi')
            ->select('*')
            ->where('tanggal_realisasi', [$startDateString, $endDateString])
            ->whereIn('est', $listEstate)
            ->get()
            ->toArray();

        $dataRawRealisasi = [];
        foreach ($queryDataRealisasi as $item) {
            $key = $item->est . '-' . $item->afd;
            $dataRawRealisasi[$key] = $item;
        }

        $queryDataTaksasi = DB::connection('mysql2')->table('taksasi')
            ->select('*')
            ->whereBetween('waktu_upload', [$startDateString, $endDateString])
            ->whereIn('lokasi_kerja', $listEstate)
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->waktu_upload)->format('Y-m-d');
            })
            ->map(function ($dayGroup) {
                return $dayGroup->groupBy('lokasi_kerja')
                    ->map(function ($lokasiGroup) {
                        // Sort afdeling keys alphabetically
                        $sortedAfdelingGroups = $lokasiGroup->groupBy('afdeling')
                            ->sortBy(function ($items, $key) {
                                return $key; // Sort alphabetically by afdeling key
                            });

                        return $sortedAfdelingGroups->map(function ($afdelingGroup) {
                            return $afdelingGroup->groupBy('blok');
                        });
                    });
            })
            ->toArray();


        $queryDataTaksasiSHI = DB::connection('mysql2')->table('taksasi')
            ->select('*')
            ->whereBetween('waktu_upload', [$firstDayOfMonth, $endDateString])
            ->whereIn('lokasi_kerja', $listEstate)
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->waktu_upload)->format('Y-m-d');
            })
            ->map(function ($dayGroup) {
                return $dayGroup->groupBy('lokasi_kerja')
                    ->map(function ($lokasiGroup) {
                        // Sort afdeling keys alphabetically
                        $sortedAfdelingGroups = $lokasiGroup->groupBy('afdeling')
                            ->sortBy(function ($items, $key) {
                                return $key; // Sort alphabetically by afdeling key
                            });

                        return $sortedAfdelingGroups->map(function ($afdelingGroup) {
                            return $afdelingGroup->groupBy('blok');
                        });
                    });
            })
            ->toArray();

        $dataRawTaksasi = [];

        $dataRawTaksasiSHI = [];

        if (count($queryDataTaksasi) > 0) {
            foreach ($queryDataTaksasi as $key => $value) {
                foreach ($value as $key2 => $value2) {
                    foreach ($value2 as $key3 => $blokArr) {
                        $sum_luas = 0;
                        $sum_sph = 0;
                        $sum_bjr = 0;
                        $sum_janjang = 0;
                        $sum_pokok = 0;
                        $keb_pemanen = 0;

                        $inc = 0;
                        foreach ($blokArr as $key4 => $value3) {
                            if (count($value3) > 1) {
                                $sum_luas += $value3[0]->luas;
                                $sum_bjr += $value3[0]->bjr;
                                $sum_sph += $value3[0]->sph;
                                $inc++;
                            } else {
                                $sum_luas += $value3[0]->luas;
                                $sum_bjr += $value3[0]->bjr;
                                $sum_sph += $value3[0]->sph;
                                $inc++;
                            }

                            foreach ($value3 as $key5 => $value4) {
                                $sum_janjang += $value4->jumlah_janjang;
                                $sum_pokok += $value4->jumlah_pokok;
                                $keb_pemanen += $value4->pemanen;
                            }
                        }



                        $final_sph = round($sum_sph / $inc);
                        $final_bjr = round($sum_bjr / $inc);
                        $akp = round($sum_janjang / $sum_pokok, 2) * 100;
                        $tak = round(($akp * $sum_luas * $final_sph * $final_bjr) / 100, 2);
                        $formatted_tak = number_format($tak, 2, ',', '.');

                        $dataRawTaksasi[$key][$key2][$key3]['ha_panen_taksasi'] = $sum_luas;
                        $dataRawTaksasi[$key][$key2][$key3]['luas'] = $sum_luas;
                        $dataRawTaksasi[$key][$key2][$key3]['bjr'] = $final_bjr;
                        $dataRawTaksasi[$key][$key2][$key3]['sph'] = $final_sph;
                        $dataRawTaksasi[$key][$key2][$key3]['janjang'] = $sum_janjang;
                        $dataRawTaksasi[$key][$key2][$key3]['pokok'] = $sum_pokok;
                        $dataRawTaksasi[$key][$key2][$key3]['akp_taksasi'] = $akp;
                        $dataRawTaksasi[$key][$key2][$key3]['tonase_taksasi'] = $tak;
                        $dataRawTaksasi[$key][$key2][$key3]['keb_hk_taksasi'] = $keb_pemanen;
                    }
                }
            }
        }

        if (count($queryDataTaksasiSHI) > 0) {
            foreach ($queryDataTaksasiSHI as $key => $value) {
                foreach ($value as $key2 => $value2) {
                    foreach ($value2 as $key3 => $blokArr) {
                        $sum_luas = 0;
                        $sum_sph = 0;
                        $sum_bjr = 0;
                        $sum_janjang = 0;
                        $sum_pokok = 0;
                        $keb_pemanen = 0;


                        $inc = 0;
                        foreach ($blokArr as $key4 => $value3) {
                            if (count($value3) > 1) {
                                $sum_luas += $value3[0]->luas;
                                $sum_bjr += $value3[0]->bjr;
                                $sum_sph += $value3[0]->sph;
                                $inc++;
                            } else {
                                $sum_luas += $value3[0]->luas;
                                $sum_bjr += $value3[0]->bjr;
                                $sum_sph += $value3[0]->sph;
                                $inc++;
                            }

                            foreach ($value3 as $key5 => $value4) {
                                $sum_janjang += $value4->jumlah_janjang;
                                $sum_pokok += $value4->jumlah_pokok;
                                $keb_pemanen += $value4->pemanen;
                            }
                        }

                        $final_sph = 0;
                        $final_bjr = 0;

                        $final_sph = round($sum_sph / $inc, 2);
                        $final_bjr = round($sum_bjr / $inc, 2);

                        $akp = round($sum_janjang / $sum_pokok, 2) * 100;
                        $tak = round(($akp * $sum_luas * $final_sph * $final_bjr) / 100, 2);
                        $formatted_tak = number_format($tak, 2, ',', '.');

                        $dataRawTaksasiSHI[$key][$key2][$key3]['ha_panen_taksasi'] = $sum_luas;
                        $dataRawTaksasiSHI[$key][$key2][$key3]['luas'] = $sum_luas;
                        $dataRawTaksasiSHI[$key][$key2][$key3]['bjr'] = $final_bjr;
                        $dataRawTaksasiSHI[$key][$key2][$key3]['sph'] = $final_sph;
                        $dataRawTaksasiSHI[$key][$key2][$key3]['janjang'] = $sum_janjang;
                        $dataRawTaksasiSHI[$key][$key2][$key3]['pokok'] = $sum_pokok;
                        $dataRawTaksasiSHI[$key][$key2][$key3]['akp_taksasi'] = $akp;
                        $dataRawTaksasiSHI[$key][$key2][$key3]['tonase_taksasi'] = $tak;
                        $dataRawTaksasiSHI[$key][$key2][$key3]['keb_hk_taksasi'] = $keb_pemanen;

                        // if ($key2 == "NBE" && $key == '2024-06-04') {
                        //     dd($dataRawTaksasiSHI, $value2, $final_bjr, $sum_bjr, $inc);
                        // }
                    }
                }
            }
        }


        $dataEst = [];

        // while ($startDate <= $endDate) {
        // $dateString = $startDate->format('Y-m-d');


        // dd($dataRawTaksasiSHI);
        $dateString = $date_request;
        $dataFinal = $this->hiRealisasi($startDateSHI, $endDateSHI, $name_reg, $dateString, $listEstPerWil, $resultEstWithAfd, $dataRawTaksasi, $dataRawTaksasiSHI, $dataRawRealisasi, $dataRawRealisasiSHI);


        // dd($dataFinal);
        echo json_encode($dataFinal);
    }



    private function hiRealisasi($startDateSHI, $endDateSHI, $name_reg, $dateString, $listEstPerWil, $resultEstWithAfd, $dataRawTaksasi, $dataRawTaksasiSHI, $dataRawRealisasi, $dataRawRealisasiSHI)
    {


        $hitungSHItaksasiDataExist = [];
        $hitungSHIrealisasiDataExist = [];
        foreach ($resultEstWithAfd as $est => $estates) {

            $tempHarianPokok = 0;
            $tempHarianJanjang = 0;
            $tempHarianSPH = 0;
            $tempHarianBjr = 0;
            $akpHarian = 0;
            $tempHkHarian = 0;
            $tempHarianLuas = 0;
            $counter = 0;
            $takHarian  = 0;
            $tanggal = $startDateSHI->format('Y-m-d'); //tanggal default
            foreach ($dataRawTaksasiSHI as $tanggal => $value) {
                $tempPokokAfd = 0;
                $tempJanjangAfd = 0;
                $sumSphAfd = 0;
                $sumBjrAfd = 0;
                $sumHK = 0;
                $sumLuas = 0;
                if (array_key_exists($est, $value)) {
                    $inc = 0;
                    foreach ($value[$est] as $key2 => $value2) {
                        $tempPokokAfd += $value2['pokok'];
                        $tempJanjangAfd += $value2['janjang'];
                        $sumSphAfd += $value2['sph'];
                        $sumBjrAfd += $value2['bjr'];
                        $sumHK += $value2['keb_hk_taksasi'];
                        $sumLuas += $value2['luas'];
                        $inc++;
                    }
                    $tempSph = ($sumSphAfd != 0) ? round($sumSphAfd / $inc, 2) : '-';
                    $tempBjr = ($sumBjrAfd != 0) ? round($sumBjrAfd / $inc, 2) : '-';

                    if ($counter == 0) {
                        $tempHarianSPH =  $tempSph;
                        $tempHarianBjr = $tempBjr;
                    } else {
                        $tempHarianSPH = round(($tempHarianSPH + $tempSph) / 2, 2);
                        $tempHarianBjr = round(($tempHarianBjr + $tempBjr) / 2, 2);
                    }


                    $tempHarianLuas += $sumLuas;
                    $tempHkHarian += $sumHK;
                    $tempHarianPokok += $tempPokokAfd;
                    $tempHarianJanjang += $tempJanjangAfd;
                    $akpHarian = ($tempHarianPokok != 0 && $tempHarianJanjang != 0) ? round(($tempHarianJanjang / $tempHarianPokok) * 100, 2) : '-';
                    $takHarian = round(($tempHarianLuas * $tempHarianBjr * $tempHarianSPH * $akpHarian) / 100, 2);
                }
                $counter++;
            }

            $hitungSHItaksasiDataExist[$tanggal][$est] = [
                'ha_panen_taksasi' => $takHarian,
                'luas' => $tempHarianLuas,
                'pokok' => $tempHarianPokok,
                'janjang' => $tempHarianJanjang,
                'akp_taksasi' => $akpHarian,
                'bjr' => $tempHarianBjr,
                'sph' => $tempHarianSPH,
                'keb_hk_taksasi' => $tempHkHarian,
            ];



            $tempHarianPokok = 0;
            $tempHarianJanjang = 0;
            $tempHarianSPH = 0;
            $tempHarianBjr = 0;
            $akpHarian = 0;
            $tempHkHarian = 0;
            $tempHarianLuas = 0;
            $counter = 0;
            $takHarian  = 0;
            $tanggal = $startDateSHI->format('Y-m-d'); //tanggal default


            $tempHarianTonase = 0;
            $tempHarianRestanHi = 0;
            $tempHarianTotalTonase = 0;


            foreach ($dataRawRealisasiSHI as $tanggal => $value) {
                $tanggal = (new DateTime($tanggal))->format('Y-m-d');

                $sumPokokAfd = 0;
                $sumJanjangAfd = 0;
                $sumSphAfd = 0;
                $sumBjrAfd = 0;
                $sumHK = 0;
                $sumLuas = 0;
                $sumTonase = 0;
                $sumRestanHi = 0;
                if (array_key_exists($est, $value)) {
                    $inc = 0;
                    foreach ($value[$est] as $key2 => $value2) {
                        foreach ($value2 as $key3 => $value3) {
                            $sumPokokAfd += $value3->pokok;
                            $sumJanjangAfd += $value3->janjang;
                            $sumBjrAfd += $value3->bjr;
                            $sumHK += $value3->hk;
                            $sumLuas += $value3->ha_panen;
                            $sumTonase += $value3->tonase;
                            $sumRestanHi += $value3->restan_hi;
                        }
                        $inc++;
                    }


                    $tempHarianTotalTonase += $sumTonase + $sumRestanHi;

                    if ($counter == 0) {
                        $tempHarianBjr =   ($sumTonase != 0 && $sumJanjangAfd != 0) ? round(($sumTonase + $sumRestanHi) / $sumJanjangAfd) : '-';
                    } else {
                        $tempHarianBjr =    ($tempHarianTotalTonase != 0 && $tempHarianJanjang != 0) ? round($tempHarianTotalTonase / $tempHarianJanjang, 2) : '-';
                    }

                    // if ($est == "SYE" && $tanggal == "2024-06-01 00:00:00") {
                    //     dd($value, $sumTonase, $sumRestanHi, $sumJanjangAfd, $tempHarianBjr, $sumLuas);
                    // }
                    $tempHarianLuas += $sumLuas;
                    $tempHkHarian += $sumHK;
                    $tempHarianTonase += $sumTonase;
                    $tempHarianRestanHi += $sumRestanHi;
                    $tempHarianPokok += $sumPokokAfd;
                    $tempHarianJanjang += $sumJanjangAfd;
                    $akpHarian = ($tempHarianPokok != 0 && $tempHarianJanjang != 0) ? round(($tempHarianJanjang / $tempHarianPokok) * 100, 2) : '-';
                }



                $counter++;
            }


            $hitungSHIrealisasiDataExist[$tanggal][$est] = [
                'ha_panen' => round($tempHarianLuas, 2),
                'pokok' => $tempHarianPokok,
                'janjang' => $tempHarianJanjang,
                'akp' => $akpHarian,
                'bjr' => $tempHarianBjr,
                'tonase' => $tempHarianTonase,
                'restan_hi' => $tempHarianRestanHi,
                'total_tonase' => $tempHarianTotalTonase,
                'hk' => $tempHkHarian,
            ];

            // dd($dataRawRealisasiSHI, $hitungSHIrealisasiDataExist, $tempHarianTotalTonase);
        }

        $SHITaksasiRaw = 0;
        // while ($startDateSHI <= $endDateSHI) {
        //     $dateString = $startDateSHI->format('Y-m-d');
        //     $startDateSHI->modify('+1 day');
        // }


        foreach ($resultEstWithAfd as $nama_est => $afdelings) {
            $inc = 0;
            $ha_panen_est_taksasi = 0;
            $ha_panen_est_realisasi = 0;
            $tonase_est_taksasi = 0;
            $tonase_est_realisasi = 0;
            $sum_janjang_est_taksasi = 0;
            $sum_janjang_est_realisasi = 0;
            $sum_pokok_est_taksasi = 0;
            $sum_pokok_est_realisasi = 0;
            $keb_hk_est_taksasi = 0;
            $keb_hk_est_realisasi = 0;
            $total_tonase_est_taksasi = 0;
            $tonase_est_realisasi = 0;
            $restan_hi_realisasi = 0;
            $sum_bjr_est = 0;
            $sum_sph_est = 0;
            $inc_for_bjr_sph = 0;
            $restan_kemarin_taksasi = 0;
            foreach ($afdelings as $afdeling) {
                $key = $nama_est . '-' . $afdeling;

                if (isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['bjr'])) {
                    $sum_bjr_est += $dataRawTaksasi[$dateString][$nama_est][$afdeling]['bjr'];
                    $inc_for_bjr_sph++;
                }

                if (isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['sph'])) {
                    $sum_sph_est += $dataRawTaksasi[$dateString][$nama_est][$afdeling]['sph'];
                    $inc_for_bjr_sph++;
                }

                $ha_panen_est_taksasi += isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['ha_panen_taksasi']) ? $dataRawTaksasi[$dateString][$nama_est][$afdeling]['ha_panen_taksasi'] : 0;
                $keb_hk_est_taksasi += isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['keb_hk_taksasi']) ? $dataRawTaksasi[$dateString][$nama_est][$afdeling]['keb_hk_taksasi'] : 0;
                $sum_janjang_est_taksasi += isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['janjang']) ? $dataRawTaksasi[$dateString][$nama_est][$afdeling]['janjang'] : 0;
                $sum_pokok_est_taksasi += isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['pokok']) ? $dataRawTaksasi[$dateString][$nama_est][$afdeling]['pokok'] : 0;
                $ha_panen_taksasi = isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['ha_panen_taksasi']) ? number_format($dataRawTaksasi[$dateString][$nama_est][$afdeling]['ha_panen_taksasi'], 2, '.', '') : '-';
                $akp_taksasi = isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['akp_taksasi']) ? number_format($dataRawTaksasi[$dateString][$nama_est][$afdeling]['akp_taksasi'], 2, '.', '') : '-';
                $tonase_taksasi_str = isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['tonase_taksasi']) ? $dataRawTaksasi[$dateString][$nama_est][$afdeling]['tonase_taksasi'] : '-';
                $tonase_taksasi = $tonase_taksasi_str !== '-' ? number_format((float) str_replace(',', '', $tonase_taksasi_str), 2, '.', '') : '-';
                $keb_hk_taksasi = isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['keb_hk_taksasi']) ? number_format($dataRawTaksasi[$dateString][$nama_est][$afdeling]['keb_hk_taksasi'], 2, '.', '') : '-';
                $restan_kemarin_taksasi += isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['restan_kemarin']) ? number_format($dataRawTaksasi[$dateString][$nama_est][$afdeling]['restan_kemarin'], 2, '.', '') : 0;
                $temp_restan_kemarin_taksasi = isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['restan_kemarin']) ? number_format($dataRawTaksasi[$dateString][$nama_est][$afdeling]['restan_kemarin'], 2, '.', '') : 0;
                $total_tonase_per_afd_taksasi = $ha_panen_est_taksasi + $temp_restan_kemarin_taksasi;


                if (isset($dataRawRealisasi[$key])) {
                    $item = $dataRawRealisasi[$key];

                    //get all nilai realisasi
                    $ha_panen_est_realisasi += isset($item->ha_panen) ? number_format($item->ha_panen, 2) : 0;
                    $keb_hk_est_realisasi += isset($item->hk) ? $item->hk : 0;;
                    $sum_janjang_est_realisasi += isset($item->janjang) ? $item->janjang : 0;
                    $sum_pokok_est_realisasi += isset($item->pokok) ? $item->pokok : 0;
                    $tonase_est_realisasi += isset($item->total_tonase) ? $item->total_tonase : 0;
                    $restan_hi_realisasi += isset($item->restan_hi) ? $item->restan_hi : 0;
                    $ha_panen_taksasi_value = $ha_panen_taksasi !== '-' ? (float) str_replace(',', '', $ha_panen_taksasi) : 0;
                    $ha_panen_realisasi_value = isset($item->ha_panen) ? $item->ha_panen : 0;
                    $tonase_realisasi_value = isset($item->tonase) ? $item->tonase : 0;
                    $akp_realisasi_value = isset($item->akp) ? $item->akp : 0;
                    $keb_hk_realisasi_value = isset($item->hk) ? $item->hk : 0;

                    //hitung varian
                    $ha_panen_varian = $ha_panen_taksasi_value !== 0 ? number_format(($ha_panen_realisasi_value / $ha_panen_taksasi_value) * 100, 2) : '-';
                    $tonase_varian = $tonase_taksasi !== '-' && $tonase_taksasi !== 0 ? number_format(($tonase_realisasi_value / $tonase_taksasi) * 100, 2) : '-';
                    $akp_varian = $akp_taksasi !== '-' && $akp_taksasi !== 0 ? number_format(($akp_realisasi_value / $akp_taksasi) * 100, 2) : '-';
                    $keb_hk_varian = ($keb_hk_taksasi !== '-' && $keb_hk_taksasi != 0 && $keb_hk_realisasi_value != 0)
                        ? number_format(($keb_hk_realisasi_value / $keb_hk_taksasi) * 100, 2)
                        : '-';
                    $pokok_varian = isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['pokok']) ? number_format(($item->pokok / $dataRawTaksasi[$dateString][$nama_est][$afdeling]['pokok']) * 100, 2) : '-';
                    $janjang_varian = isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['janjang']) ? number_format(($item->janjang / $dataRawTaksasi[$dateString][$nama_est][$afdeling]['janjang']) * 100, 2) : '-';
                    $bjr_varian = isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['bjr']) ? number_format(($item->bjr / $dataRawTaksasi[$dateString][$nama_est][$afdeling]['bjr']) * 100, 2) : '-';

                    $dataRaw[$nama_est][] = [
                        'Tanggal' => $dateString,
                        'AFD' => $afdeling,
                        'ha_panen_taksasi' => $ha_panen_taksasi,
                        'ha_panen_realisasi' => isset($item->ha_panen) ? number_format($item->ha_panen, 2) : '-',
                        'ha_panen_varian' => $ha_panen_varian,
                        'pokok_taksasi' => isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['pokok']) ? $dataRawTaksasi[$dateString][$nama_est][$afdeling]['pokok'] : '-',
                        'pokok_realisasi' => isset($item->pokok) ? $item->pokok : '-',
                        'pokok_varian' => $pokok_varian,
                        'janjang_taksasi' =>  isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['janjang']) ? $dataRawTaksasi[$dateString][$nama_est][$afdeling]['janjang'] : '-',
                        'janjang_realisasi' => isset($item->janjang) ? $item->janjang : '-',
                        'janjang_varian' => $janjang_varian,
                        'bjr_taksasi' => isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['bjr']) ? $dataRawTaksasi[$dateString][$nama_est][$afdeling]['bjr'] : '-',
                        'bjr_realisasi' => isset($item->bjr) ? $item->bjr : '-',
                        'bjr_varian' => $bjr_varian,
                        'restan_kemarin' => isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['restan_kemarin']) ? $dataRawTaksasi[$dateString][$nama_est][$afdeling]['restan_kemarin'] : '-',
                        'restan_hi' => isset($item->restan_hi) ? $item->restan_hi : '-',
                        'total_tonase_taksasi' => $total_tonase_per_afd_taksasi,
                        'total_tonase_realisasi' => isset($item->total_tonase) ? $item->total_tonase : '-',
                        'akp_taksasi' => $akp_taksasi,
                        'akp_realisasi' => isset($item->akp) ? number_format($item->akp, 2) : '-',
                        'akp_varian' => $akp_varian, // Replace with actual calculation logic for akp_varian
                        'taksasi_tonase' => $tonase_taksasi,
                        'taksasi_realisasi' => isset($item->tonase) ? $item->tonase : '-',
                        'taksasi_varian' => $tonase_varian,
                        'keb_hk_taksasi' => $keb_hk_taksasi,
                        'keb_hk_realisasi' => isset($item->hk) ? number_format($item->hk, 2) : '-',
                        'keb_hk_varian' => $keb_hk_varian, // Replace with actual calculation logic for keb_hk_varian
                    ];
                } else {
                    $dataRaw[$nama_est][] = [
                        'Tanggal' => $dateString,
                        'AFD' => $afdeling,
                        'ha_panen_taksasi' => $ha_panen_taksasi,
                        'ha_panen_realisasi' => '-',
                        'ha_panen_varian' => '-',
                        'pokok_taksasi' => isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['pokok']) ? $dataRawTaksasi[$dateString][$nama_est][$afdeling]['pokok'] : '-',
                        'pokok_realisasi' => '-',
                        'pokok_varian' => '-',
                        'janjang_taksasi' => isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['janjang']) ? $dataRawTaksasi[$dateString][$nama_est][$afdeling]['janjang'] : '-',
                        'janjang_realisasi' => '-',
                        'janjang_varian' => '-',
                        'bjr_taksasi' => isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['bjr']) ? $dataRawTaksasi[$dateString][$nama_est][$afdeling]['bjr'] : '-',
                        'bjr_realisasi' =>  '-',
                        'bjr_varian' => '-',
                        'restan_kemarin' => isset($dataRawTaksasi[$dateString][$nama_est][$afdeling]['restan_kemarin']) ? $dataRawTaksasi[$dateString][$nama_est][$afdeling]['restan_kemarin'] : '-',
                        'restan_hi' =>  '-',
                        'total_tonase_taksasi' => $total_tonase_per_afd_taksasi,
                        'total_tonase_realisasi' => '-',
                        'akp_taksasi' => $akp_taksasi,
                        'akp_realisasi' => '-',
                        'akp_varian' => '',
                        'taksasi_tonase' => $tonase_taksasi,
                        'taksasi_realisasi' => '-',
                        'taksasi_varian' => '',
                        'keb_hk_taksasi' => $keb_hk_taksasi,
                        'keb_hk_realisasi' => '-',
                        'keb_hk_varian' => '-',
                    ];
                }
                $inc++;
            }



            if ($inc == count($afdelings)) {

                $ha_panen_est_varian = ($ha_panen_est_taksasi != 0 && $ha_panen_est_realisasi != 0) ? round($ha_panen_est_realisasi / $ha_panen_est_taksasi * 100, 2) : '-';
                $keb_hk_est_varian = ($keb_hk_est_taksasi != 0 && $keb_hk_est_realisasi != 0) ? round($keb_hk_est_realisasi / $keb_hk_est_taksasi * 100, 2) : '-';
                $akp_taksasi_est = ($sum_janjang_est_taksasi != 0 && $sum_pokok_est_taksasi != 0) ? round($sum_janjang_est_taksasi / $sum_pokok_est_taksasi, 2) * 100 : '-';
                $akp_realisasi_est = ($sum_janjang_est_realisasi != 0 && $sum_pokok_est_realisasi != 0) ? round($sum_janjang_est_realisasi / $sum_pokok_est_realisasi, 2) * 100 : '-';
                $akp_est_varian = ((float)$akp_taksasi_est != 0 && (float)$akp_realisasi_est != 0) ? round($akp_realisasi_est / $akp_taksasi_est * 100, 2) : '-';
                $total_tonase_est_realisasi = $tonase_est_realisasi + $restan_hi_realisasi;
                $bjr_est_realisasi = ($total_tonase_est_realisasi != 0 && $sum_janjang_est_realisasi != 0)  ? round($total_tonase_est_realisasi / $sum_janjang_est_realisasi, 2) : '-';
                $bjr_est_taksasi = ($sum_bjr_est != 0 && $inc_for_bjr_sph != 0) ? round($sum_bjr_est / $inc_for_bjr_sph, 2) : 0;
                $sph_est_taksasi = ($sum_sph_est != 0 && $inc_for_bjr_sph != 0) ? round($sum_sph_est / $inc_for_bjr_sph, 2) : 0;

                $bjr_varian = ((float)$bjr_est_realisasi != 0 && $bjr_est_taksasi != 0) ? round(($bjr_est_realisasi / $bjr_est_taksasi) * 100, 2) : '-';
                $janjang_est_varian = ($sum_janjang_est_realisasi != 0 && $sum_janjang_est_taksasi != 0) ?  round(($sum_janjang_est_realisasi / $sum_janjang_est_taksasi) * 100, 2) : '-';
                $pokok_est_varian = ($sum_pokok_est_realisasi != 0 && $sum_pokok_est_taksasi != 0) ? round(($sum_pokok_est_realisasi / $sum_pokok_est_taksasi) * 100, 2) : '-';

                $tonase_est_taksasi = round(((float)$akp_taksasi_est * (float)$ha_panen_est_taksasi * (float) $sph_est_taksasi * (float)$bjr_est_taksasi) / 100, 2);
                $taksasi_est_varian = ($tonase_est_realisasi != 0 && $tonase_est_taksasi != 0) ? round(($tonase_est_realisasi / $tonase_est_taksasi) * 100, 2) : '-';
                $total_restan_kemarin_taksasi = $tonase_est_taksasi + $restan_kemarin_taksasi;

                $dataEst[] = [
                    'Tanggal' => $dateString,
                    'key' => $nama_est,
                    'sph_total' => $sum_sph_est,
                    'bjr_total' => $sum_bjr_est,
                    'sph_taksasi_shi' => isset($hitungSHItaksasiDataExist[$dateString][$nama_est]) ? $hitungSHItaksasiDataExist[$dateString][$nama_est]['sph'] : '-',
                    'ha_panen_taksasi_shi' => isset($hitungSHItaksasiDataExist[$dateString][$nama_est]) ? $hitungSHItaksasiDataExist[$dateString][$nama_est]['luas'] : '-',
                    'ha_panen_realisasi_shi' => isset($hitungSHIrealisasiDataExist[$dateString][$nama_est]) ? $hitungSHIrealisasiDataExist[$dateString][$nama_est]['ha_panen'] : '-',
                    'ha_panen_taksasi' => number_format($ha_panen_est_taksasi, 2),
                    'ha_panen_realisasi' => number_format($ha_panen_est_realisasi, 2),
                    'ha_panen_varian' => $ha_panen_est_varian,
                    'pokok_taksasi_shi' => isset($hitungSHItaksasiDataExist[$dateString][$nama_est]) ? $hitungSHItaksasiDataExist[$dateString][$nama_est]['pokok'] : '-',
                    'pokok_realisasi_shi' => isset($hitungSHIrealisasiDataExist[$dateString][$nama_est]) ? $hitungSHIrealisasiDataExist[$dateString][$nama_est]['pokok'] : '-',
                    'pokok_taksasi' => $sum_pokok_est_taksasi,
                    'pokok_realisasi' => $sum_pokok_est_realisasi,
                    'pokok_varian' => $pokok_est_varian,
                    'janjang_taksasi_shi' => isset($hitungSHItaksasiDataExist[$dateString][$nama_est]) ? $hitungSHItaksasiDataExist[$dateString][$nama_est]['janjang'] : '-',
                    'janjang_realisasi_shi' => isset($hitungSHIrealisasiDataExist[$dateString][$nama_est]) ? $hitungSHIrealisasiDataExist[$dateString][$nama_est]['janjang'] : '-',
                    'janjang_taksasi' => $sum_janjang_est_taksasi,
                    'janjang_realisasi' => $sum_janjang_est_realisasi,
                    'janjang_varian' => $janjang_est_varian,
                    'bjr_taksasi_shi' => isset($hitungSHItaksasiDataExist[$dateString][$nama_est]) ? $hitungSHItaksasiDataExist[$dateString][$nama_est]['bjr'] : '-',
                    'bjr_realisasi_shi' => isset($hitungSHIrealisasiDataExist[$dateString][$nama_est]) ? $hitungSHIrealisasiDataExist[$dateString][$nama_est]['bjr'] : '-',
                    'bjr_taksasi' => $bjr_est_taksasi,
                    'bjr_realisasi' =>  $bjr_est_realisasi,
                    'bjr_varian' => $bjr_varian,
                    'restan_kemarin' => $restan_kemarin_taksasi,
                    'restan_hi' =>  $restan_hi_realisasi,
                    'restan_hi_realisasi_shi' => isset($hitungSHIrealisasiDataExist[$dateString][$nama_est]) ? $hitungSHIrealisasiDataExist[$dateString][$nama_est]['restan_hi'] : '-',
                    'total_tonase_taksasi' => $total_restan_kemarin_taksasi,
                    'total_tonase_realisasi' => $total_tonase_est_realisasi,
                    'total_tonase_realisasi_shi' => isset($hitungSHIrealisasiDataExist[$dateString][$nama_est]) ? $hitungSHIrealisasiDataExist[$dateString][$nama_est]['total_tonase'] : '-',
                    'akp_taksasi_shi' => isset($hitungSHItaksasiDataExist[$dateString][$nama_est]) ? $hitungSHItaksasiDataExist[$dateString][$nama_est]['akp_taksasi'] : '-',
                    'akp_realisasi_shi' => isset($hitungSHIrealisasiDataExist[$dateString][$nama_est]) ? ceil($hitungSHIrealisasiDataExist[$dateString][$nama_est]['akp']) : '-',
                    'akp_taksasi' => number_format((float)$akp_taksasi_est, 2),
                    'akp_realisasi' => number_format((float)$akp_realisasi_est, 2),
                    'akp_varian' => $akp_est_varian,
                    'tonase_taksasi_shi' => isset($hitungSHItaksasiDataExist[$dateString][$nama_est]) ? $hitungSHItaksasiDataExist[$dateString][$nama_est]['ha_panen_taksasi'] : '-',
                    'tonase_realisasi_shi' => isset($hitungSHIrealisasiDataExist[$dateString][$nama_est]) ? $hitungSHIrealisasiDataExist[$dateString][$nama_est]['tonase'] : '-',
                    'taksasi_tonase' => $tonase_est_taksasi,
                    'taksasi_realisasi' => $tonase_est_realisasi,
                    'taksasi_varian' => $taksasi_est_varian,
                    'keb_hk_taksasi_shi' => isset($hitungSHItaksasiDataExist[$dateString][$nama_est]) ? $hitungSHItaksasiDataExist[$dateString][$nama_est]['keb_hk_taksasi'] : '-',
                    'keb_hk_realisasi_shi' => isset($hitungSHIrealisasiDataExist[$dateString][$nama_est]) ? $hitungSHIrealisasiDataExist[$dateString][$nama_est]['hk'] : '-',
                    'keb_hk_taksasi' => $keb_hk_est_taksasi,
                    'keb_hk_realisasi' => $keb_hk_est_realisasi,
                    'keb_hk_varian' => $keb_hk_est_varian,
                ];
            }
        }
        // $startDate->modify('+1 day');
        // }



        $dataWil = [];
        $listWil = [];
        $sum_est = 0;
        $sum_bjr_realisasi_est = 0;
        foreach ($listEstPerWil as $key => $value) {
            $listWil[] = $key;
            $sum_jjg = 0;
            $sum_pkk = 0;
            $ha_panen = 0;
            $sph = 0;
            $bjr = 0;
            $keb_hk_wil_tak = 0;
            $keb_hk_wil_realisasi = 0;
            $sum_pkk_tak_shi = 0;
            $sum_jjg_tak_shi = 0;
            $keb_hk_wil_tak_shi = 0;
            $keb_hk_wil_realisasi_shi = 0;
            $ha_panen_taksasi_est_shi = 0;
            $bjr_taksasi_est_shi = 0;
            $sph_taksasi_est_shi = 0;
            $ha_panen_realisasi_wil_shi = 0;
            $pokok_realisasi_wil_shi = 0;
            $janjang_realisasi_wil_shi = 0;
            $tonase_realisasi_wil_shi = 0;
            $ha_panen_realisasi_wil = 0;
            $pokok_realisasi_wil = 0;
            $janjang_realisasi_wil = 0;
            $tonase_realisasi_wil = 0;
            $inc_sph_bjr = 0;
            $restan_hi_realisasi_wil = 0;
            $restan_kemarin_wil = 0;
            $total_tonase_realisasi_shi = 0;
            $restan_hi_realisasi_est_shi = 0;

            $bjr_realisasi_est_shi = 0;
            $inc = 0;
            foreach ($value as $key2 => $value2) {
                $nama_est = $value2['est'];

                $match_est = False;
                $id_key = '';
                foreach ($dataEst as $key3 => $value3) {
                    if ($nama_est == $value3["key"]) {
                        $match_est = True;
                        $id_key = $key3;
                        break;
                    }
                }

                if ($match_est) {
                    $item = $dataEst[$id_key];

                    if ($item['sph_total'] != 0 && $item['sph_total'] != '-') {
                        $sph += $item['sph_total'];
                        $inc_sph_bjr++;
                    }

                    if ($item['bjr_total'] != 0 && $item['bjr_total'] != '-') {
                        $bjr += $item['bjr_total'];
                        $inc_sph_bjr++;
                    }

                    $sum_pkk += $item['pokok_taksasi'];
                    $sum_pkk_tak_shi += (int)$item['pokok_taksasi_shi'];
                    $sum_jjg += $item['janjang_taksasi'];
                    $sum_jjg_tak_shi += (int)$item['janjang_taksasi_shi'];
                    $ha_panen += $item['ha_panen_taksasi'];
                    $ha_panen_taksasi_est_shi += (float)$item['ha_panen_taksasi_shi'];
                    $ha_panen_realisasi_wil += $item['ha_panen_realisasi'];
                    $ha_panen_realisasi_wil_shi += (float)$item['ha_panen_realisasi_shi'];
                    $bjr_taksasi_est_shi += (float)$item['bjr_taksasi_shi'];
                    $bjr_realisasi_est_shi += $item['bjr_realisasi_shi'];
                    $sum_bjr_realisasi_est += $item['bjr_realisasi_shi'];
                    $sph_taksasi_est_shi += (float)$item['sph_taksasi_shi'];
                    $keb_hk_wil_tak += $item['keb_hk_taksasi'];
                    $keb_hk_wil_tak_shi += (int)$item['keb_hk_taksasi_shi'];
                    $keb_hk_wil_realisasi_shi += (int)$item['keb_hk_realisasi_shi'];
                    $pokok_realisasi_wil += $item['pokok_realisasi'];
                    $pokok_realisasi_wil_shi += $item['pokok_realisasi_shi'];
                    $janjang_realisasi_wil += $item['janjang_realisasi'];
                    $janjang_realisasi_wil_shi += $item['janjang_realisasi_shi'];
                    $tonase_realisasi_wil += $item['taksasi_realisasi'];
                    $tonase_realisasi_wil_shi += (float)$item['tonase_realisasi_shi'];
                    $total_tonase_realisasi_shi += (float)$item['total_tonase_realisasi_shi'];
                    $keb_hk_wil_realisasi += $item['keb_hk_realisasi'];
                    $restan_hi_realisasi_wil += $item['restan_hi'];
                    $restan_kemarin_wil += $item['restan_kemarin'];
                    $restan_hi_realisasi_est_shi += (float)$item['restan_hi_realisasi_shi'];
                    $inc++;
                    $sum_est++;
                }
            }


            $ha_panen_taksasi_shi_wil = ($ha_panen_taksasi_est_shi != 0) ? round($ha_panen_taksasi_est_shi / $inc, 2) : '-';

            $bjr_taksasi_shi_wil = ($bjr_taksasi_est_shi != 0) ? round($bjr_taksasi_est_shi / $inc, 2) : '-';
            $akp_taksasi_shi_wil = ($sum_jjg_tak_shi != 0 && $sum_pkk_tak_shi != 0) ? round(($sum_jjg_tak_shi / $sum_pkk_tak_shi) * 100, 2) : '-';
            $sph_taksasi_shi_wil = ($sph_taksasi_est_shi != 0) ? round($sph_taksasi_est_shi / $inc, 2) : '-';
            $temp_taksasi_shi_wil = round(((float)$akp_taksasi_shi_wil * (float)$bjr_taksasi_shi_wil * (float)$ha_panen_taksasi_shi_wil * (float)$sph_taksasi_shi_wil) / 100, 2);

            $akp_wil = ($sum_pkk != 0 && $sum_jjg != 0) ? round($sum_jjg / $sum_pkk, 2) : '-';
            $sph_wil = ($sph != 0 && $sph != '-') ? round($sph / $inc_sph_bjr, 2) : '-';
            $bjr_wil = ($bjr != 0 && $bjr != '-') ? round($bjr / $inc_sph_bjr, 2) : '-';
            $temp = (float)$akp_wil * (float)$sph_wil * (float)$bjr_wil * (float)$ha_panen;
            $tonase_taksasi_wil = $temp != 0 ? round($temp / 100, 2) : '-';
            $akp_realisasi_wil = ($janjang_realisasi_wil != 0 && $pokok_realisasi_wil != 0) ? round($janjang_realisasi_wil / $pokok_realisasi_wil * 100, 2) : '-';

            $total_tonase_wil_realisasi = $tonase_realisasi_wil + $restan_hi_realisasi_wil;
            $bjr_wil_realisasi = ($total_tonase_wil_realisasi != 0 && $janjang_realisasi_wil != 0)
                ? round($total_tonase_wil_realisasi / $janjang_realisasi_wil, 2)
                : '-';


            $ha_panen_wil_varian = ($ha_panen_realisasi_wil != 0 && $ha_panen != 0) ? round($ha_panen_realisasi_wil / $ha_panen * 100, 2) : '-';
            $pokok_wil_varian = ($sum_pkk != 0 && $pokok_realisasi_wil != 0) ? round($pokok_realisasi_wil / $sum_pkk * 100, 2) : '-';
            $janjang_wil_varian = ($janjang_realisasi_wil != 0 && $sum_jjg != 0) ? round($janjang_realisasi_wil / $sum_jjg * 100, 2) : '-';
            $akp_wil_varian = ((float)$akp_realisasi_wil != 0 && (float)$akp_wil != 0) ? round((float)$akp_realisasi_wil / (float)$akp_wil * 100, 2) : '-';
            $tonase_wil_varian =  ($tonase_taksasi_wil != 0 && $tonase_realisasi_wil != 0) ? round($tonase_realisasi_wil / $tonase_taksasi_wil * 100, 2) : '-';
            $keb_hk_wil_varian =  ($keb_hk_wil_tak != 0 && $keb_hk_wil_realisasi != 0) ? round($keb_hk_wil_realisasi / $keb_hk_wil_tak * 100, 2) : '-';
            $bjr_wil_varian =  ((float)$bjr_wil_realisasi != 0 && (float)$bjr_wil != 0) ? round((float)$bjr_wil_realisasi / (float)$bjr_wil * 100, 2) : '-';

            $total_tonase_taksasi_wil = (float)$tonase_taksasi_wil + $restan_kemarin_wil;
            $akp_realisasi_wil_shi = ($janjang_realisasi_wil_shi != 0 && $pokok_realisasi_wil_shi != 0) ?   round($janjang_realisasi_wil_shi / $pokok_realisasi_wil_shi, 2) * 100 : '-';
            $total_tonase_realisasi_shi = $tonase_realisasi_wil_shi + $restan_hi_realisasi_est_shi;

            $bjr_realisasi_wil_shi = $bjr_realisasi_est_shi != 0 && $inc != 0 ? round($bjr_realisasi_est_shi / $inc, 2) : '-';

            $dataWil[] = [
                'Tanggal' => $dateString,
                'key' => $key,
                'sph_total' => $sph,
                'bjr_total' => $bjr,
                'sph_taksasi_shi' => $sph_taksasi_est_shi,
                'ha_panen_taksasi_shi' => round((float)$ha_panen_taksasi_shi_wil, 2),
                'ha_panen_realisasi_shi' => round((float)$ha_panen_realisasi_wil_shi, 2),
                'ha_panen_taksasi' => $ha_panen,
                'ha_panen_realisasi' => $ha_panen_realisasi_wil,
                'ha_panen_varian' => $ha_panen_wil_varian,
                'pokok_taksasi_shi' => $sum_pkk_tak_shi,
                'pokok_realisasi_shi' => $pokok_realisasi_wil_shi,
                'pokok_taksasi' => $sum_pkk,
                'pokok_realisasi' => $pokok_realisasi_wil,
                'pokok_varian' => $pokok_wil_varian,
                'janjang_taksasi_shi' => $sum_jjg_tak_shi,
                'janjang_realisasi_shi' => $janjang_realisasi_wil_shi,
                'janjang_taksasi' => $sum_jjg,
                'janjang_realisasi' => $janjang_realisasi_wil,
                'janjang_varian' => $janjang_wil_varian,
                'bjr_taksasi_shi' => $bjr_taksasi_shi_wil,
                'bjr_realisasi_shi' => $bjr_realisasi_wil_shi,
                'bjr_taksasi' => $bjr_wil,
                'bjr_realisasi' =>  $bjr_wil_realisasi,
                'bjr_varian' => $bjr_wil_varian,
                'restan_kemarin' => $restan_kemarin_wil,
                'restan_hi' => $restan_hi_realisasi_wil,
                'restan_hi_realisasi_shi' => $restan_hi_realisasi_est_shi,
                'total_tonase_taksasi' => $total_tonase_taksasi_wil,
                'total_tonase_realisasi' => $total_tonase_wil_realisasi,
                'total_tonase_realisasi_shi' => $total_tonase_realisasi_shi,
                'akp_taksasi_shi' => $akp_taksasi_shi_wil,
                'akp_realisasi_shi' => $akp_realisasi_wil_shi,
                'akp_taksasi' => $akp_wil,
                'akp_realisasi' => $akp_realisasi_wil,
                'akp_varian' => $akp_wil_varian,
                'tonase_taksasi_shi' => $temp_taksasi_shi_wil,
                'tonase_realisasi_shi' => $tonase_realisasi_wil_shi,
                'taksasi_tonase' => $tonase_taksasi_wil,
                'taksasi_realisasi' => $tonase_realisasi_wil,
                'taksasi_varian' => $tonase_wil_varian,
                'keb_hk_taksasi_shi' => $keb_hk_wil_tak_shi,
                'keb_hk_realisasi_shi' => $keb_hk_wil_realisasi_shi,
                'keb_hk_taksasi' => $keb_hk_wil_tak,
                'keb_hk_realisasi' => $keb_hk_wil_realisasi,
                'keb_hk_varian' => $keb_hk_wil_varian,
            ];
        }




        $dataReg = [];


        $sum_jjg = 0;
        $sum_pkk = 0;
        $ha_panen = 0;
        $sph = 0;
        $bjr = 0;
        $keb_hk_reg_tak = 0;
        $keb_hk_reg_realisasi = 0;
        $ha_panen_realisasi_reg = 0;
        $pokok_realisasi_reg = 0;
        $janjang_realisasi_reg = 0;
        $tonase_realisasi_reg = 0;
        $inc_sph_bjr = 0;
        $restan_hi_realisasi_reg = 0;
        $restan_kemarin_reg = 0;
        $sum_pkk_tak_shi = 0;
        $sum_jjg_tak_shi = 0;
        $keb_hk_wil_tak_shi = 0;
        $keb_hk_wil_realisasi_shi = 0;
        $ha_panen_taksasi_wil_shi = 0;
        $bjr_taksasi_wil_shi = 0;
        $sph_taksasi_wil_shi = 0;
        $ha_panen_realisasi_wil_shi = 0;
        $pokok_realisasi_wil_shi = 0;
        $janjang_realisasi_wil_shi = 0;
        $tonase_realisasi_wil_shi = 0;
        $restan_hi_wil_realisasi_shi = 0;
        $inc = 0;
        foreach ($dataWil as $key => $value) {
            if ($value['sph_total'] != 0 && $value['sph_total'] != '-') {
                $sph += $value['sph_total'];
                $inc_sph_bjr++;
            }

            if ($value['bjr_total'] != 0 && $value['bjr_total'] != '-') {
                $bjr += $value['bjr_total'];
                $inc_sph_bjr++;
            }

            $sum_pkk += $value['pokok_taksasi'];
            $sum_pkk_tak_shi += (int)$value['pokok_taksasi_shi'];
            $sum_jjg_tak_shi += (int)$value['janjang_taksasi_shi'];
            $ha_panen_taksasi_wil_shi += $value['ha_panen_taksasi_shi'];
            $ha_panen_realisasi_wil_shi += $value['ha_panen_realisasi_shi'];
            $bjr_taksasi_wil_shi +=  (float)$value['bjr_taksasi_shi'];
            $sph_taksasi_wil_shi += $value['sph_taksasi_shi'];
            $keb_hk_wil_tak_shi += (int)$value['keb_hk_taksasi_shi'];
            $keb_hk_wil_realisasi_shi += $value['keb_hk_realisasi_shi'];
            $pokok_realisasi_wil_shi += $value['pokok_realisasi_shi'];
            $janjang_realisasi_wil_shi += $value['janjang_realisasi_shi'];
            $tonase_realisasi_wil_shi += $value['tonase_realisasi_shi'];
            $sum_jjg += $value['janjang_taksasi'];
            $ha_panen += $value['ha_panen_taksasi'];
            $ha_panen_realisasi_reg += $value['ha_panen_realisasi'];
            $keb_hk_reg_tak += $value['keb_hk_taksasi'];
            $pokok_realisasi_reg += $value['pokok_realisasi'];
            $janjang_realisasi_reg += $value['janjang_realisasi'];
            $tonase_realisasi_reg += $value['taksasi_realisasi'];
            $keb_hk_reg_realisasi += $value['keb_hk_realisasi'];
            $restan_hi_realisasi_reg += $value['restan_hi'];
            $restan_hi_wil_realisasi_shi += $value['restan_hi_realisasi_shi'];
            $restan_kemarin_reg += $value['restan_kemarin'];
            $inc++;
        }


        $ha_panen_taksasi_shi_reg = ($ha_panen_taksasi_wil_shi != 0) ? round($ha_panen_taksasi_wil_shi / $inc, 2) : '-';
        $bjr_taksasi_shi_reg = ($bjr_taksasi_wil_shi != 0) ? round($bjr_taksasi_wil_shi / $inc, 2) : '-';
        $akp_taksasi_shi_reg = ($sum_jjg_tak_shi != 0 && $sum_pkk_tak_shi != 0) ? round(($sum_jjg_tak_shi / $sum_pkk_tak_shi) * 100, 2) : '-';
        $sph_taksasi_shi_reg = ($sph_taksasi_wil_shi != 0) ? round($sph_taksasi_wil_shi / $inc, 2) : '-';
        $temp_taksasi_shi_reg = round(((float)$akp_taksasi_shi_reg * (float)$bjr_taksasi_shi_reg * (float)$ha_panen_taksasi_shi_reg * (float)$sph_taksasi_shi_reg) / 100, 2);

        $akp_reg = ($sum_pkk != 0 && $sum_jjg != 0) ? round($sum_jjg / $sum_pkk, 2) : '-';
        $sph_reg = ($sph != 0 && $sph != '-') ? round($sph / $inc_sph_bjr, 2) : '-';
        $bjr_reg = ($bjr != 0 && $bjr != '-') ? round($bjr / $inc_sph_bjr, 2) : '-';
        $temp = (float)$akp_reg * (float)$sph_reg * (float)$bjr_reg * (float)$ha_panen;
        $tonase_taksasi_reg = $temp != 0 ? round($temp / 100, 2) : '-';
        $akp_realisasi_reg = ($janjang_realisasi_reg != 0 && $pokok_realisasi_reg != 0) ? round($janjang_realisasi_reg / $pokok_realisasi_reg * 100, 2) : '-';

        $total_tonase_reg_realisasi = $tonase_realisasi_reg + $restan_hi_realisasi_reg;
        $bjr_reg_realisasi = ($total_tonase_est_realisasi != 0 && $sum_janjang_est_realisasi != 0)  ? round($total_tonase_reg_realisasi / $janjang_realisasi_reg, 2) : '-';

        $ha_panen_reg_varian = ($ha_panen_realisasi_reg != 0 && $ha_panen != 0) ? round($ha_panen_realisasi_reg / $ha_panen * 100, 2) : '-';
        $pokok_reg_varian = ($sum_pkk != 0 && $pokok_realisasi_reg != 0) ? round($pokok_realisasi_reg / $sum_pkk * 100, 2) : '-';
        $janjang_reg_varian = ($janjang_realisasi_reg != 0 && $sum_jjg != 0) ? round($janjang_realisasi_reg / $sum_jjg * 100, 2) : '-';
        $akp_reg_varian = ((float)$akp_realisasi_reg != 0 && (float)$akp_reg != 0) ? round((float)$akp_realisasi_reg / (float)$akp_reg * 100, 2) : '-';
        $tonase_reg_varian =  ($tonase_taksasi_reg != 0 && $tonase_realisasi_reg != 0) ? round($tonase_realisasi_reg / $tonase_taksasi_reg * 100, 2) : '-';
        $keb_hk_reg_varian =  ($keb_hk_reg_tak != 0 && $keb_hk_reg_realisasi != 0) ? round($keb_hk_reg_realisasi / $keb_hk_reg_tak * 100, 2) : '-';
        $bjr_reg_varian =  ((float)$bjr_reg_realisasi != 0 && (float)$bjr_reg != 0) ? round((float)$bjr_reg_realisasi / (float)$bjr_reg * 100, 2) : '-';

        $total_tonase_taksasi_reg = (float)$tonase_taksasi_reg + $restan_kemarin_reg;
        $akp_realisasi_reg_shi = ($janjang_realisasi_wil_shi != 0 && $pokok_realisasi_wil_shi != 0) ?   round($janjang_realisasi_wil_shi / $pokok_realisasi_wil_shi, 2) * 100 : '-';
        $total_tonase_realisasi_wil_shi = $tonase_realisasi_wil_shi + $restan_hi_wil_realisasi_shi;
        $bjr_realisasi_reg_shi = ($sum_bjr_realisasi_est != 0 && $sum_est != 0) ?  round($sum_bjr_realisasi_est / $sum_est, 2) : '-';

        $dataReg[] = [
            'Tanggal' => $dateString,
            'key' => $name_reg,
            'ha_panen_taksasi_shi' => round((float)$ha_panen_taksasi_wil_shi, 2),
            'ha_panen_realisasi_shi' => round((float)$ha_panen_realisasi_wil_shi, 2),
            'ha_panen_taksasi' => $ha_panen,
            'ha_panen_realisasi' => $ha_panen_realisasi_reg,
            'ha_panen_varian' => $ha_panen_reg_varian,
            'pokok_taksasi_shi' => $sum_pkk_tak_shi,
            'pokok_realisasi_shi' => $pokok_realisasi_wil_shi,
            'pokok_taksasi' => $sum_pkk,
            'pokok_realisasi' => $pokok_realisasi_reg,
            'pokok_varian' => $pokok_reg_varian,
            'janjang_taksasi_shi' => $sum_jjg_tak_shi,
            'janjang_realisasi_shi' => $janjang_realisasi_wil_shi,
            'janjang_taksasi' => $sum_jjg,
            'janjang_realisasi' => $janjang_realisasi_reg,
            'janjang_varian' => $janjang_reg_varian,
            'bjr_taksasi_shi' => $bjr_taksasi_shi_reg,
            'bjr_realisasi_shi' => $bjr_realisasi_reg_shi,
            'bjr_taksasi' => $bjr_reg,
            'bjr_realisasi' =>  $bjr_reg_realisasi,
            'bjr_varian' => $bjr_reg_varian,
            'restan_kemarin' => $restan_kemarin_reg,
            'restan_hi' => $restan_hi_realisasi_reg,
            'total_tonase_taksasi' => $temp_taksasi_shi_wil,
            'total_tonase_realisasi' => $total_tonase_reg_realisasi,
            'akp_taksasi_shi' => $akp_taksasi_shi_reg,
            'akp_realisasi_shi' => $akp_realisasi_reg_shi,
            'akp_taksasi' => $akp_reg,
            'akp_realisasi' => $akp_realisasi_reg,
            'akp_varian' => $akp_reg_varian,
            'tonase_taksasi_shi' => $temp_taksasi_shi_reg,
            'tonase_realisasi_shi' => $tonase_realisasi_wil_shi,
            'total_tonase_realisasi_shi' => $total_tonase_realisasi_wil_shi,
            'taksasi_tonase' => $tonase_taksasi_reg,
            'taksasi_realisasi' => $tonase_realisasi_reg,
            'taksasi_varian' => $tonase_reg_varian,
            'keb_hk_taksasi_shi' => $keb_hk_wil_tak_shi,
            'keb_hk_realisasi_shi' => $keb_hk_wil_realisasi_shi,
            'keb_hk_taksasi' => $keb_hk_reg_tak,
            'keb_hk_realisasi' => $keb_hk_reg_realisasi,
            'keb_hk_varian' => $keb_hk_reg_varian,
        ];


        foreach ($dataReg as &$record) {
            foreach ($record as $key => $value) {
                if (is_numeric($value)) {
                    $record[$key] = $this->custom_number_format($value);
                }
            }
        }
        foreach ($dataWil as &$subArray) {
            foreach ($subArray as $key => $value) {
                if (is_numeric($value)) {
                    $subArray[$key] = $this->custom_number_format($value);
                }
            }
        }
        foreach ($dataEst as &$subArray) {
            foreach ($subArray as $key => $value) {
                if (is_numeric($value)) {
                    $subArray[$key] = $this->custom_number_format($value);
                }
            }
        }

        $finalData = [
            'dataEst' => $dataEst,
            'dataWil' => $dataWil,
            'dataReg' => $dataReg,
        ];

        return $finalData;
    }

    public function importExcelRealisasiTaksasi(Request $request)
    {
        try {
            $validated = $request->validate([
                'file' => 'required|mimes:csv,xls,xlsx|max:20480', // max size in kilobytes (20MB)
            ]);

            $month = $request->input('month');
            $file = $request->file('file');

            // Process the Excel file
            $import = new RealisasiTaksasiImport($this->dataService, $month, $this->finaldataTaksasiRealisasiGM, $this->finaldataProdAfdInti);
            FacadesExcel::import($import, $file); // Specify the sheet here

            // Flash success message
            Session::flash('success', 'Data Realisasi Excel Berhasil Diimport!');
            return redirect()->back();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Flash error message
            $errorMessage = implode(' ', $e->validator->getMessageBag()->all());
            Session::flash('errors', $errorMessage);
            return redirect()->back()->withInput();
        } catch (\Exception $e) {
            // Flash general error message
            Session::flash('errors', 'An error occurred while processing the file. ' . $e);
            return redirect()->back()->withInput();
        }
    }

    function custom_number_format($number)
    {
        // Separate the integer part and the decimal part
        $parts = explode('.', str_replace(',', '.', strval($number)));

        // Format the integer part with a period as the thousands separator
        $integer_part = number_format($parts[0], 0, ',', '.');

        // Check if there is a decimal part
        if (isset($parts[1])) {
            // Combine the formatted integer part with the original decimal part
            $formatted_number = $integer_part . ',' . $parts[1];
        } else {
            // No decimal part, just return the formatted integer part
            $formatted_number = $integer_part;
        }

        return $formatted_number;
    }
}
