<?php

namespace App\Http\Controllers;

use App\Models\Afdeling;
use App\Models\Blok;
use App\Models\Estate;
use App\Models\Regional;
use App\Models\Wilayah;
use Carbon\Carbon;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        // $dateToday = new DateTime();
        $dateToday =  Carbon::now()->format('d M Y');
        $getDate = Carbon::parse($dateToday)->locale('id');
        $getDate->settings(['formatFunction' => 'translatedFormat']);

        $pil_reg = $request->has('id_reg') ? $request->get('id_reg') : 0;

        $reg_all = Regional::all()->pluck('nama');
        $reg_all = json_decode($reg_all);

        $reg = Regional::with("wilayah")->get();
        $reg_arr = array();

        foreach ($reg as $key => $value) {
            foreach ($value->wilayah as $key2 => $data) {
                $reg_arr[$key][$data->nama] =  Wilayah::with("estate")->find($data->id)->estate->pluck('nama', 'est');
            }
        }

        $est_wil_reg = array();
        foreach ($reg_arr as $key => $value) {
            foreach ($value as $key2 => $data) {
                foreach ($data as $key3 => $datas) {
                    $est_wil_reg[$key][$key3] = $datas;
                }
            }
        }

        $est_tak = 0;
        $est_pemanen = 0;
        $log_tak_est = '';
        $log_keb_pemanen_est = '';
        $est = '';
        $dateToday = Carbon::now()->format('Y-m-d');
        $tglData = $request->has('tgl') ? $request->input('tgl') : $defaultHari = $dateToday;

        $hariIni =  $tglData . ' 00:00:00';

        $newConvert = new Carbon($hariIni);

        $besok = $newConvert->addDays();
        $besok = $besok->format('Y-m-d') . ' 00:00:00';

        $color_chart = array(
            '#001E3C',
            '#AB221D',
            '#5CAF50',
            '#7CAF50',
            '#8CAF50',
            '#AB221D',
            '#AB221D',
            '#AB221D',
            '#AB221D',
            '#AB221D',
            '#AB221D',
            '#AB221D',
            '#AB221D',
            '#AB221D',
            '#282C34'
        );

        $afdeling = array('OA', 'OB', 'OC', 'OD', 'OF', 'OA');

        // $query = DB::connection('mysql2')->table('taksasi')
        //     ->select('taksasi.*')
        //     ->whereBetween('taksasi.waktu_upload', [$hariIni, $besok])
        //     ->orderBy('taksasi.afdeling', 'asc')
        //     ->get();

        // if ($query->first() != null) {
        //     $query = json_decode($query);


        //     foreach ($est_wil_reg[$pil_reg] as $key => $value) {
        //         $inc = 0;
        //         $sum_sph = 0;
        //         $sum_bjr = 0;
        //         $sum_path = 0;
        //         $sum_pokok = 0;
        //         $sum_janjang = 0;
        //         $sum_pemanen = 0;
        //         $sum_ritase = 0;
        //         $sum_luas = 0;
        //         foreach ($query as $data) {
        //             if ($data->lokasi_kerja == $key) {

        //                 $sum_luas += $data->luas;
        //                 $sum_sph += $data->sph;
        //                 $sum_bjr += $data->bjr;
        //                 $path_arr = explode(';', $data->br_kanan);
        //                 $jumlah_path = count($path_arr);
        //                 $sum_path += $jumlah_path;
        //                 $sum_pokok += $data->jumlah_pokok;
        //                 $sum_janjang += $data->jumlah_janjang;
        //                 $sum_pemanen += $data->pemanen;
        //             }
        //             $inc++;
        //         }
        //         // ceil($value['taksasi'] / 6500);
        //         $est_array[$key]['est'] = $key;
        //         $est_array[$key]['luas_total'] = $sum_luas;
        //         $sph = round($sum_sph / $inc, 2);
        //         $est_array[$key]['sph_total'] = $sph;
        //         $bjr = round($sum_bjr / $inc, 2);
        //         $est_array[$key]['bjr_total'] = $bjr;
        //         $est_array[$key]['path_total'] = $sum_path;
        //         $est_array[$key]['pokok_total'] = $sum_pokok;
        //         $est_array[$key]['janjang_total'] = $sum_janjang;

        //         if ($sum_janjang != 0 && $sum_pokok != 0) {
        //             $akp = round($sum_janjang / $sum_pokok, 2) * 100;
        //             $est_array[$key]['akp_total'] = $akp;
        //             $tak = ($akp * $sum_luas * $sph * $bjr) / 100;
        //             $est_array[$key]['taksasi_total'] =  number_format($tak, 2, ",", ".");
        //             $est_array[$key]['pemanen_total'] = $sum_pemanen;
        //             $est_array[$key]['ritase_total'] = ceil($tak / 6500);
        //         }
        //     }
        // }
        $pil_est = $request->has('id_reg') ? $request->get('id_reg') : 3;

        $reg_all = Regional::all()->pluck('nama');
        $reg_all = json_decode($reg_all);

        $reg = Regional::with("wilayah")->get();
        $reg_arr = array();
        foreach ($reg as $key => $value) {
            foreach ($value->wilayah as $key2 => $data) {
                $reg_arr[$key][$data->nama] =  Wilayah::with("estate")->find($data->id)->estate->pluck('nama', 'est');
            }
        }

        $est_wil_reg = array();
        foreach ($reg_arr as $key => $value) {
            foreach ($value as $key2 => $data) {
                foreach ($data as $key3 => $datas) {
                    $est_wil_reg[$key][$key3] = $datas;
                }
            }
        }

        // dd(Estate::with('afdeling')->find(3));
        // dd($est_wil_reg[$pil_reg]);

        // $id_est = Estate::where('est', $pil_est)->first()->id;
        $queryEst = Estate::with("afdeling")->find($pil_est);

        $list_afd_est = $queryEst->afdeling->pluck('nama');

        $afd_array = array();
        $keb_pem_array = array();

        $query = DB::connection('mysql2')->table('taksasi')
            ->select('taksasi.*')
            ->whereBetween('taksasi.waktu_upload', [$hariIni, $besok])
            ->where('lokasi_kerja', $queryEst->est)
            ->orderBy('taksasi.afdeling', 'asc')
            ->get();

        foreach ($list_afd_est as $key => $value) {
            $afd_array[$key]['afd'] = $value;
            $afd_array[$key]['taksasi'] = 0;
            $afd_array[$key]['kebutuhan_pemanen'] = 0;
        }

        $list_afd_est = json_decode($list_afd_est);
        if ($query->first() != null) {
            $query = json_decode($query);

            foreach ($list_afd_est as $key => $value) {
                $inc = 0;
                $sum_pemanen = 0;
                $tak = 0;
                $akp = 0;
                foreach ($query as $key2 => $data) {
                    if ($data->afdeling == $value) {
                        $sum_pemanen += $data->pemanen;
                        $akp = round($data->jumlah_janjang / $data->jumlah_pokok, 2) * 100;

                        $tak += ($akp * $data->luas * $data->sph * $data->bjr) / 100;
                    }
                    $inc++;
                }
                $afd_array[$key]['taksasi'] =  number_format($tak, 2, ",", ".");
                $afd_array[$key]['kebutuhan_pemanen'] = $sum_pemanen;

                $est_tak += $tak;
                $est_pemanen += $sum_pemanen;
            }
        }

        $est_tak =  number_format($est_tak, 2, ",", ".");
        $reg_all = Regional::where('nama', '!=', 'Regional V')->get()->pluck('nama');
        return view('homepage', [
            'date' => $getDate->format('l, j F Y'),
            'hour' =>  Carbon::now()->format('H:i:s'),
            'estTak' => $est_tak, // default RDE 
            'estPemanen' => $est_pemanen, // default RDE 
            'dateAfdeling' => $afd_array,
            'reg' => $reg_all,
        ]);
    }

    public function ds_taksasi()
    {
        $reg_all = Regional::all()->pluck('nama');
        $reg_all = json_decode($reg_all);

        return view('taksasi.estate', [
            'reg' => $reg_all,
        ]);
    }


    public function ds_taksasi_afdeling(Request $request)
    {

        $reg_all = Regional::all()->pluck('nama');
        $reg_all = json_decode($reg_all);

        // dd($reg_all);
        return view('taksasi.afdeling', [
            'reg' => $reg_all,
        ]);
    }

    public function getTakEst15Days(Request $request)
    {
        $pil_reg = $request->get('id_reg');
        $tglData = $request->get('tgl');
        // $to = !is_null($logMingguan) ?  $logMingguan->timestamp : Carbon::now()->format('Y-m-d');
        // dd($to);
        $formatted = new DateTime($tglData);

        // dd($formatted);
        $formatted = $formatted->format('Y-m-d');

        $to = $formatted . ' 23:59:59';

        $convert = new DateTime($to);

        $to = $convert->format('Y-m-d H:i:s');

        $dateParse = Carbon::parse($to)->subDays(14);
        $dateParse = $dateParse->format('Y-m-d');

        $dateParse = $dateParse . ' 00:00:00';
        $pastWeek = new DateTime($dateParse);
        $pastWeek = $pastWeek->format('Y-m-d H:i:s');


        $reg_all = Regional::all()->pluck('nama');
        $reg_all = json_decode($reg_all);

        $reg = Regional::with("wilayah")->get();
        $reg_arr = array();

        foreach ($reg as $key => $value) {
            foreach ($value->wilayah as $key2 => $data) {
                $reg_arr[$key][$data->nama] =  Wilayah::with("estate")->find($data->id)->estate->pluck('nama', 'est');
            }
        }

        $est_wil_reg = array();
        foreach ($reg_arr as $key => $value) {
            foreach ($value as $key2 => $data) {
                foreach ($data as $key3 => $datas) {
                    $est_wil_reg[$key][$key3] = $datas;
                }
            }
        }

        $query = DB::connection('mysql2')->table('taksasi')
            ->select('taksasi.*')
            ->whereBetween('taksasi.waktu_upload', [$pastWeek, $to])
            ->orderBy('taksasi.afdeling', 'asc')
            ->get();

        $data_per_hari = array();
        for ($i = 0; $i < 15; $i++) {
            $hari = Carbon::parse($pastWeek)->addDays($i);
            $convertHari = $hari->format('Y-m-d');
            $hari = Carbon::parse($hari)->locale('id');
            $hari->settings(['formatFunction' => 'translatedFormat']);
            $tgl = $hari->format('j F');
            $hari = $hari->format('l, j F');

            $data_per_hari[$i]['hari'] = $tgl;
            $data_per_hari[$i]['tanggal'] = $hari;

            $data_per_hari[$i]['taksasi'] = 0;
            $data_per_hari[$i]['kebutuhan_pemanen'] = 0;

            foreach ($est_wil_reg[$pil_reg] as $key => $value) {
                $sum_tak_est = 0;
                $sum_keb_pemanen_est = 0;
                $jumlah_record = 0;
                foreach ($query as $data) {
                    $waktu_upload = new DateTime($data->waktu_upload);
                    $waktu_upload = $waktu_upload->format('Y-m-d');
                    if ($convertHari == $waktu_upload) {
                        $sum_tak_est += $data->taksasi;
                        $sum_keb_pemanen_est += $data->pemanen;
                        $jumlah_record++;
                    }
                }
                $data_per_hari[$i]['countRecord'] = $jumlah_record;
                $data_per_hari[$i]['taksasi'] = $sum_tak_est;
                $data_per_hari[$i]['kebutuhan_pemanen'] = $sum_keb_pemanen_est;
            }
        }

        echo json_encode($data_per_hari);
        exit();
    }

    public function getNameEstate(Request $request)
    {


        $id_reg = $request->get('id_reg');
        $id_wil = $request->get('id_wil');

        $reg_all = Regional::get()->toArray();

        $reg = $reg_all[$id_reg];
        $specific_wil = 'Wilayah 3';
        $specific_est = 'NBE';
        $wil_list = Wilayah::
            // where('regional', $reg['id'])
            where('nama', $specific_wil)
            ->get()->pluck('id');


        $id_wil_pil = $wil_list[$id_wil];
        $est_wil_reg = Estate::where('wil', $id_wil_pil)
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
            ->where('est', $specific_est)
            ->get()->toArray();



        $output = '';
        $inc_est = 1;
        foreach ($est_wil_reg as $key => $val) {
            $output .= '<option value="' . $val['est'] . '">' . $val['nama'] . '</option>';
            $inc_est++;
        }
        echo $output;
    }

    public function getNameAfdeling(Request $request)
    {

        $id_estate = $request->get('id_estate');
        $date = $request->get('date');

        // $afdelingList = Afdeling::where('estate', $id_estate)->pluck('nama');

        $afdelingList = DB::connection('mysql2')->table('monitoring_pemupukan')
            ->select('*', 'estate.id as id_estate')
            ->join('estate', 'monitoring_pemupukan.estate', '=', 'estate.est')
            ->where('estate.id', $id_estate)
            ->whereMonth('monitoring_pemupukan.waktu_upload', Carbon::parse($date)->month)
            ->groupBy('monitoring_pemupukan.afdeling')
            ->get();

        $afdelingList = $afdelingList->pluck('afdeling');
        $output = '<option selected disabled>Pilih Afdeling</option>';
        $inc_est = 1;
        foreach ($afdelingList as $key => $val) {
            $output .= '<option value="' . $val . '">' . $val . '</option>';
            $inc_est++;
        }
        echo $output;
    }

    public function getListEstateTerpupuk(Request $request)
    {
        $date = $request->get('date');
        $query = DB::connection('mysql2')->table('monitoring_pemupukan')
            ->select('*', 'estate.id as id_estate')
            ->join('estate', 'monitoring_pemupukan.estate', '=', 'estate.est')
            // ->whereMonth('monitoring_pemupukan.waktu_upload', Carbon::parse($date)->month)
            ->where('monitoring_pemupukan.waktu_upload', 'like', '%' . $date . '%')
            // ->groupBy('monitoring_pemupukan.estate')
            ->pluck('monitoring_pemupukan.estate', 'id_estate');

        $output = '';
        $inc_est = 1;
        if ($query->first() != '') {
            foreach ($query as $key => $val) {
                $output .= '<option value="' . $key . '">' . $val . '</option>';
                $inc_est++;
            }
        }

        echo $output;
    }

    public function lastDataPemupukan(Request $request)
    {
        // if ($request->ajax()) {
        $date = $request->get('date');

        $monthFilter = Carbon::parse($date)->month;

        $query = DB::connection('mysql2')->table('monitoring_pemupukan')
            ->select('*', DB::raw("DATE_FORMAT(monitoring_pemupukan.waktu_upload, '%Y-%m-%d') as tgl"), 'estate.id as id_estate', 'pupuk.nama as nama_pupuk')
            ->join('estate', 'monitoring_pemupukan.estate', '=', 'estate.est')
            ->join('pupuk', 'monitoring_pemupukan.jenis_pupuk_id', '=', 'pupuk.id')
            ->where('waktu_upload', 'like', '%' . $date . '%')
            ->orderBy('monitoring_pemupukan.waktu_upload', 'desc')
            ->get();

        $listPupuk = array();
        foreach ($query as $key => $value) {
            $listPupuk[]  = $value->nama_pupuk;
        }

        foreach ($query as $item) {
            $hari = Carbon::parse($item->waktu_upload)->locale('id');
            $hari->settings(['formatFunction' => 'translatedFormat']);
            $item->tanggal = $hari->format('j F Y');
            $item->tgl_est =  $item->tanggal . ' ' . $item->estate . ' ' . $item->afdeling;
        }
        $query = $query->groupBy(function ($value2) {
            return $value2->tgl_est;
        });

        $dataRes = array();
        $inc = 0;
        foreach ($query as $key => $value) {
            $dataRes[$inc]['estate'] = $value[0]->estate;
            $dataRes[$inc]['afdeling'] = $value[0]->afdeling;
            $dataRes[$inc]['tanggal'] = $value[0]->tanggal;
            $dataRes[$inc]['waktu_upload'] = $value[0]->waktu_upload;
            $inc++;
        }

        // dd($dataRes);

        echo json_encode($dataRes);
    }

    public function getDataPemupukan(Request $request)
    {
        // if ($request->ajax()) {
        $afd = $request->get('afd');
        $id_est = $request->get('id_est');
        $date = $request->get('date');

        $monthFilter = Carbon::parse($date)->month;

        $query = DB::connection('mysql2')->table('monitoring_pemupukan')
            ->select('*', DB::raw("DATE_FORMAT(monitoring_pemupukan.waktu_upload, '%Y-%m-%d') as tgl"), 'estate.id as id_estate', 'pupuk.nama as nama_pupuk')
            ->join('estate', 'monitoring_pemupukan.estate', '=', 'estate.est')
            ->join('pupuk', 'monitoring_pemupukan.jenis_pupuk_id', '=', 'pupuk.id')
            ->where('estate.id', $id_est)
            ->where('monitoring_pemupukan.afdeling', $afd)
            ->where('waktu_upload', 'like', '%' . $date . '%')
            ->groupBy('tgl')
            ->orderBy('monitoring_pemupukan.waktu_upload', 'desc')
            ->get();

        $listPupuk = array();
        foreach ($query as $key => $value) {
            $listPupuk[]  = $value->nama_pupuk;
        }

        foreach ($query as $item) {
            $hari = Carbon::parse($item->waktu_upload)->locale('id');
            $hari->settings(['formatFunction' => 'translatedFormat']);
            $item->tanggal = $hari->format('j F Y');
            $item->tgl_est =  $item->tanggal . ' ' . $item->estate . ' ' . $item->afdeling;
        }

        $query = $query->groupBy(function ($value2) {
            return $value2->tgl_est;
        });

        $dataRes = array();
        $inc = 0;
        foreach ($query as $key => $value) {
            $dataRes[$inc]['estate'] = $value[0]->estate;
            $dataRes[$inc]['afdeling'] = $value[0]->afdeling;
            $dataRes[$inc]['tanggal'] = $value[0]->tanggal;
            $dataRes[$inc]['waktu_upload'] = $value[0]->waktu_upload;
            $inc++;
        }

        $namaEst = DB::connection('mysql2')->table('estate')
            ->select('*')
            ->where('id', $id_est)
            // ->whereMonth('waktu_upload', $monthFilter)
            ->first()->est;

        $queryRekom = DB::connection('mysql2')->table('rekom_pemupukan')
            ->select('*')
            ->where('est', $namaEst)
            ->where('afd', $afd)
            ->get();

        foreach ($listPupuk as $key => $value) {
        }

        // dd($query);

        echo json_encode($dataRes);
    }

    public function tableAndMaps(Request $request)
    {
        $id_est = $request->get('id_est');
        $est = $request->get('est');
        $tgl = $request->get('tgl');

        $tgl = Carbon::parse($tgl);
        $kemarin = $tgl->subDay()->format('Y-m-d') . ' 00:00:00';

        $newConvert = new Carbon($kemarin);

        $hariIni = $newConvert->addDays(2);

        $hariIni = ($hariIni->format('Y-m-d')) . ' 00:00:00';

        $queryData = DB::connection('mysql2')->table('taksasi')
            ->select('taksasi.*')
            ->whereBetween('taksasi.waktu_upload', [$kemarin, $hariIni])
            ->where('lokasi_kerja', $est)
            ->orderBy('taksasi.waktu_upload', 'desc')
            ->get();


        $queryData = json_decode($queryData, true);

        $list_blok = array();
        foreach ($queryData as $key => $value) {
            $list_blok[$est][] = $value['blok'];
        }

        $blokPerEstate = array();
        $estateQuery = Estate::with("afdeling")->where('est', $est)->get();
        foreach ($estateQuery as $key => $value) {
            $i = 0;
            foreach ($value->afdeling as $key2 => $data) {
                $blokPerEstate[$est][$data->nama] =  Afdeling::with("blok")->find($data->id)->blok->pluck('nama', 'id');
                $i++;
            }
        }

        $result_list_blok = array();
        foreach ($list_blok as $key => $value) {
            foreach ($value as $key2 => $data) {
                if (strlen($data) == 5) {
                    $result_list_blok[$key][$data] = substr($data, 0, -2);
                }
                if (strlen($data) == 6) {
                    $sliced = substr_replace($data, '', 1, 1);
                    $result_list_blok[$key][$data] = substr($sliced, 0, -2);
                }
            }
        }

        $result_list_all_blok = array();
        foreach ($blokPerEstate as $key2 => $value) {
            foreach ($value as $key3 => $afd) {
                foreach ($afd as $key4 => $data) {
                    if (strlen($data) == 4) {
                        $result_list_all_blok[$key2][] = substr_replace($data, '', 1, 1);
                    }
                }
            }
        }

        // //bandingkan list blok query dan list all blok dan get hanya blok yang cocok
        $result_blok = array();
        // foreach ($list_estate as $key => $value) {
        if (array_key_exists($est, $result_list_all_blok)) {
            $query = array_unique($result_list_all_blok[$est]);
            $result_blok[$est] = array_intersect($result_list_blok[$est], $query);
        }

        // dd($result_blok);

        $queryEstate = DB::connection('mysql2')->table('estate_plot')
            ->select('*')
            ->join('estate', 'estate_plot.est', '=', 'estate.est')
            ->where('estate.est', $est)
            ->get();

        // // //get lat lang dan key $result_blok atau semua list_blok

        $blokLatLn = array();

        foreach ($result_blok as $key => $value) {
            $inc = 0;
            foreach ($value as $key2 => $data) {
                $newData = substr_replace($data, '0', 1, 0);
                $query = '';
                $query = DB::connection('mysql2')->table('blok')
                    ->select('blok.*')
                    ->where('blok.nama', $newData)
                    ->get();

                $latln = '';
                foreach ($query as $key3 => $val) {

                    $latln .= '[' . $val->lon . ',' . $val->lat . '],';
                }

                $estate = DB::connection('mysql2')->table('estate')
                    ->select('estate.*')
                    ->where('estate.est', $est)
                    ->first();

                $nama_estate = $estate->nama;

                $blokLatLn[$key][$inc]['blok'] = $key2;
                $blokLatLn[$key][$inc]['estate'] = $nama_estate;
                $blokLatLn[$key][$inc]['latln'] = rtrim($latln, ',');
                $inc++;
            }
        }

        $estate_plot = array();
        $plot = '';
        $estate = '';

        foreach ($queryEstate as $key2 => $val) {
            $plot .= '[' . $val->lon . ',' . $val->lat . '],';
            $estate = $val->nama;
        }
        $estate_plot[$est]['est'] = $estate . ' Estate';
        $estate_plot[$est]['plot'] =  rtrim($plot, ',');

        echo json_encode($estate_plot);
    }

    public function plotEstate(Request $request)
    {
        $est = $request->get('est');

        $queryEstate = DB::connection('mysql2')->table('estate_plot')
            ->select('*')
            ->join('estate', 'estate_plot.est', '=', 'estate.est')
            ->where('estate.est', $est)
            ->get();

        $estate_plot = array();
        $plot = '';
        $estate = '';

        foreach ($queryEstate as $key2 => $val) {
            $plot .= '[' . $val->lon . ',' . $val->lat . '],';
            $estate = $val->nama;
        }
        $estate_plot['est'] = $estate . ' Estate';
        $estate_plot['plot'] =  rtrim($plot, ',');

        echo json_encode($estate_plot);
    }

    public function plotBlok(Request $request)
    {
        $estate_input = $request->get('est');

        $tgl = $request->get('tgl');


        // $tglData = Carbon::parse($tgl);

        // $kemarin = $tglData->subDay()->format('Y-m-d') . ' 00:00:00';

        // $newConvert = new Carbon($kemarin);

        // $hariIni = $newConvert->addDays(2);

        // $hariIni = ($hariIni->format('Y-m-d')) . ' 00:00:00';

        $queryData = DB::connection('mysql2')->table('taksasi')
            ->select('taksasi.*')
            ->whereDate('taksasi.waktu_upload', $tgl)
            ->where('lokasi_kerja', $estate_input)
            ->orderBy('taksasi.waktu_upload', 'desc')
            ->get();

        $markers = [];

        foreach ($queryData as $row) {
            $lat = floatval($row->lat_awal);
            $lon = floatval($row->lon_awal);
            $markers[] = [$lat, $lon];
        }
        $markers = array_values($markers);


        $queryData = json_decode($queryData, true);

        $list_blok = array();
        foreach ($queryData as $key => $value) {
            $list_blok[$estate_input][] = $value['blok'];
        }

        $blokPerEstate = array();
        $estateQuery = Estate::with("afdeling")->where('est', $estate_input)->get();
        $listIdAfd = array();

        $polygons = array();
        $listBlok = [];
        foreach ($estateQuery as $key => $value) {
            foreach ($value->afdeling as $key2 => $data) {
                foreach ($data as $value2) {
                    $data2 = Afdeling::with("blok")->find($data->id)->blok;
                    foreach ($data2 as $value2) {
                        $nama = $value2->nama;
                        $latln = $value2->lat . ',' . $value2->lon;

                        if (!isset($polygons[$nama])) {
                            $polygons[$nama] = $latln;
                            $listBlok[] = $nama;
                        } else {
                            $polygons[$nama] .= '$' . $latln;
                        }
                    }
                }
            }
        }

        $polygons = array_values($polygons);



        function isPointInPolygon($point, $polygon)
        {

            $x = $point[0];
            $y = $point[1];

            // dd($polygon);
            $vertices = array_map(function ($vertex) {
                return explode(',', $vertex);
            }, explode('$', $polygon));

            // dd($vertices);

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

        $finalResultBlok = [];

        // dd($markers);
        foreach ($polygons as $key => $polygon) {
            foreach ($markers as $index => $marker) {
                // dd($marker, $polygon);
                if (isPointInPolygon($marker, $polygon)) {

                    $finalResultBlok[] = $listBlok[$key];
                }
            }
        }
        $finalResultBlok = array_unique($finalResultBlok);


        // $result_list_blok = array();
        // foreach ($list_blok as $key => $value) {
        //     foreach ($value as $key2 => $data) {
        //         $parts = explode('-CBI', $data);

        //         if (count($parts) > 1) {
        //             $result_list_blok[$key][$data] = $parts[0];
        //         } elseif (strlen($data) == 5) {
        //             $result_list_blok[$key][$data] = substr($data, 0, -2);
        //         } elseif ($key == "SCE") {
        //             $s = substr_replace($data, '', 1, 1);

        //             $s = substr_replace($s, '0', 1, 0);

        //             $result = substr($s, 0, -2);

        //             if (substr($data, 0, 1) == 'H') {
        //                 $result_list_blok[$key][$data] = $result . 'a';
        //             } else {
        //                 $result_list_blok[$key][$data] = $result;
        //             }
        //         } elseif ($key == "RGE") {

        //             $result_list_blok[$key][$data] = substr($data, 0, -2);
        //         } elseif ($key == "SJE") {

        //             $result_list_blok[$key][$data] = substr($data, 0, -3);
        //         } elseif ($key == "LME1") {

        //             $result_list_blok[$key][$data] = substr($data, 0, -3);
        //         } elseif ($key == "LME2") {

        //             $result_list_blok[$key][$data] = substr($data, 0, -3);
        //         } elseif ($key == "TBE") {

        //             $result_list_blok[$key][$data] = substr($data, 0, -2);
        //         } elseif ($key == "KTE4") {

        //             $result_list_blok[$key][$data] = substr($data, 0, -3);
        //         } elseif ($key == "KNE") {

        //             $result_list_blok[$key][$data] = substr($data, 0, 4);
        //         } elseif ($key == "UPE") {

        //             $result_list_blok[$key][$data] = substr($data, 0, 4);
        //         } elseif (strlen($data) == 6) {
        //             if ($key == "KTE" || $key == "MKE" || $key == "PKE" || $key == "BSE" || $key == "BWE" || $key == "GDE" || $key == "BDE" || $key = "BHE") {
        //                 $result_list_blok[$key][$data] = substr($data, 0, -3);
        //             } else {
        //                 $sliced = substr_replace($data, '', 1, 1);
        //                 $result_list_blok[$key][$data] = substr($sliced, 0, -2);
        //             }
        //         } elseif (strlen($data) == 3) {
        //             $result_list_blok[$key][$data] = $data;
        //         } elseif (strpos($data, 'CBI') !== false) {
        //             // $result_list_blok[$key][$data] = substr($data, 0, -7);
        //             $result_list_blok[$key][$data] = substr($data, 0, 4);
        //         } elseif (strpos($data, 'SSMSCBI') !== false) {
        //             // $sliced = substr_replace($data, '', 1, 1);
        //             $result_list_blok[$key][$data] = substr($data, 0, 4);
        //             // $result_list_blok[$key][$data] = substr($sliced, 0, -10);
        //         }
        //     }
        // }

        // $data = [];
        // while ($row = $result->fetch_assoc()) {
        //     $data[] = $row;
        // }


        // $groupedResults = [];
        // foreach ($data as $item) {
        //     $nama = $item['nama'];
        //     if (!isset($groupedResults[$nama])) {
        //         $groupedResults[$nama] = [];
        //     }
        //     $groupedResults[$nama][] = $item;
        // }
        // print_r($groupedResults);

        // [blok] => N23012
        // [estate] => Badirih
        // [afdeling] => OB
        // [latln] => [114.235591,-3.041843],[114.232783,-3.041775],[114.232888,-3.032611],[114.235797,-3.03265],[114.235591,-3.041843]

        // $blokLatLnEw = array();
        // $inc = 0;
        // foreach ($groupedResults as $key => $value) {
        //     $latln = '';
        //     $latln2 = '';
        //     foreach ($value as $key3 => $value4) {

        //         $latln .= $value4['lat'] . ',' . $value4['lon'] . '$';
        //         $latln2 .= '[' . $value4['lon'] . ',' . $value4['lat'] . '],';
        //     }

        //     $blokLatLnEw[$inc]['blok'] = $key;
        //     $blokLatLnEw[$inc]['afd'] = $value4['afd_nama'];
        //     $blokLatLnEw[$inc]['estate'] = $namaEstate;
        //     $blokLatLnEw[$inc]['latln'] = rtrim($latln, '$');
        //     $blokLatLnEw[$inc]['latinnew'] = rtrim($latln2, ',');
        //     $inc++;
        // }
        // // print_r($blokLatLnEw);



        // $uniqueCombinations = [];
        // $blokLatLn = array();
        // foreach ($blokLatLnEw as $value) {
        //     foreach ($arr as $marker) {
        //         if (isPointInPolygon($marker['latins'], $value['latln'])) {
        //             // Create a unique key based on nama, estate, and latin
        //             $key = $value['blok'] . '_' . $est . '_' . $value['latln'];

        //             // $latln .= '[' . $val->lon . ',' . $val->lat . '],';

        //             // Check if the combination already exists
        //             if (!isset($uniqueCombinations[$key])) {
        //                 $uniqueCombinations[$key] = true; // Mark the combination as encountered
        //                 $blokLatLn[$namaEstate][] = [
        //                     'blok' => $marker['blok'],
        //                     'estate' => $est,
        //                     'afdeling' => $value['afd'],
        //                     'latln' => $value['latinnew']
        //                 ];
        //             }
        //         }
        //     }
        // }
        // dd($blokPerEstate);

        // $result_list_blok = array();
        // foreach ($list_blok as $key => $value) {
        //     foreach ($value as $key2 => $data) {
        //         if (strlen($data) == 5) {
        //             $result_list_blok[$key][$data] = substr($data, 0, -2);
        //         }
        //         if (strlen($data) == 6) {
        //             $sliced = substr_replace($data, '', 1, 1);
        //             $result_list_blok[$key][$data] = substr($sliced, 0, -2);
        //         }
        //     }
        // }

        // $result_list_all_blok = array();
        // foreach ($blokPerEstate as $key2 => $value) {
        //     foreach ($value as $key3 => $afd) {
        //         foreach ($afd as $key4 => $data) {
        //             if (strlen($data) == 4) {
        //                 $result_list_all_blok[$key2][] = substr_replace($data, '', 1, 1);
        //             }
        //         }
        //     }
        // }

        // // //bandingkan list blok query dan list all blok dan get hanya blok yang cocok
        // $result_blok = array();
        // if (array_key_exists($estate_input, $result_list_all_blok)) {
        //     $query = array_unique($result_list_all_blok[$estate_input]);
        //     $result_blok[$estate_input] = array_intersect($result_list_blok[$estate_input], $query);
        // }

        // // //get lat lang dan key $result_blok atau semua list_blok

        $blokLatLn = array();
        $inc = 0;
        foreach ($finalResultBlok as $key => $value) {


            $query = DB::connection('mysql2')->table('blok')
                ->select('blok.*', 'estate.est', 'afdeling.nama as nama_afdeling')
                ->join('afdeling', 'blok.afdeling', '=', 'afdeling.id')
                ->join('estate', 'afdeling.estate', '=', 'estate.id')
                ->where('estate.est', $estate_input)
                ->where('blok.nama', $value)
                ->get();

            $latln = '';

            foreach ($query as $key2 => $data) {
                $latln .= '[' . $data->lon . ',' . $data->lat . '],';
                $estate = DB::connection('mysql2')->table('estate')
                    ->select('estate.*')
                    ->where('estate.est', $estate_input)
                    ->first();

                $nama_estate = $estate->nama;


                // dd($latln);
                $blokLatLn[$inc]['blok'] = $data->nama;
                $blokLatLn[$inc]['estate'] = $nama_estate;
                $blokLatLn[$inc]['afdeling'] = $data->nama_afdeling;
                $blokLatLn[$inc]['latln'] = rtrim($latln, ',');
            }
            $inc++;
        }


        // dd($blokLatLn);
        echo json_encode($blokLatLn);
    }

    public function plotLineTaksasi(Request $request)
    {
        $estate_input = $request->get('est');
        $tgl = $request->get('tgl');


        // $tglData = Carbon::parse($tgl);

        // $kemarin = $tglData->subDay()->format('Y-m-d') . ' 00:00:00';

        // $newConvert = new Carbon($kemarin);

        // $hariIni = $newConvert->addDays(2);

        // $hariIni = ($hariIni->format('Y-m-d')) . ' 00:00:00';

        $queryData = DB::connection('mysql2')->table('taksasi')
            ->select('taksasi.lat_awal', 'taksasi.lon_awal', 'taksasi.lat_akhir', 'taksasi.lon_akhir')
            ->whereDate('taksasi.waktu_upload', $tgl)
            ->where('lokasi_kerja', $estate_input)
            ->orderBy('taksasi.waktu_upload', 'desc')
            ->get();

        $plotLine = array();
        foreach ($queryData as $key => $value) {
            if (str_contains($value->lat_awal, ';')) {
                $plot = '';
                $trimmed_coordinates = '';
                $splitted_lat_awal = explode(';', $value->lat_awal);
                $splitted_lon_awal = explode(';', $value->lon_awal);
                // $splitted_lat_akhir = explode(';', $value->lat_akhir);
                // $splitted_lon_akhir = explode(';', $value->lon_akhir);
                for ($i = 0; $i < count($splitted_lat_awal); $i++) {
                    $plot .=  '[' . $splitted_lon_awal[$i] . ',' . $splitted_lat_awal[$i] . '],';
                }
                $trimmed_coordinates = rtrim($plot, ',');
                $plotLine[] = $trimmed_coordinates;
            } else {
                $plotLine[] =  '[' . $value->lon_awal . ',' . $value->lat_awal . '],[' . $value->lon_akhir . ',' . $value->lat_akhir . ']';
            }
        }



        echo json_encode($plotLine);
    }

    public function plotMarkerMan(Request $request)
    {
        $estate_input = $request->get('est');
        $tgl = $request->get('tgl');

        // $tglData = Carbon::parse($tgl);

        // $kemarin = $tglData->subDay()->format('Y-m-d') . ' 00:00:00';

        // $newConvert = new Carbon($kemarin);

        // $hariIni = $newConvert->addDays(2);

        // $hariIni = ($hariIni->format('Y-m-d')) . ' 00:00:00';

        $queryData = DB::connection('mysql2')->table('taksasi')
            ->select('taksasi.*')
            ->whereDate('taksasi.waktu_upload', $tgl)
            ->where('lokasi_kerja', $estate_input)
            ->orderBy('taksasi.waktu_upload', 'desc')
            ->get();



        // dd($queryData, $estate_input, $tgl);
        $arr = array();
        $inc = 0;


        foreach ($queryData as $key => $value) {
            $check = explode(';', $value->lat_awal);

            if (count($check) != 1) {
                $lat_awals = explode(';', $value->lat_awal);
                $lon_awals = explode(';', $value->lon_awal);
                $plotPairs = [];
                for ($i = 0; $i < count($lat_awals); $i++) {
                    // Pair lat_awal with lon_awal
                    $plotPairs[] = '[' . $lon_awals[$i] . ',' . $lat_awals[$i] . ']';
                }

                // Construct plot from all paired coordinates
                $arr[$inc]['plot'] = implode(',', $plotPairs);

                // Assign plotAwal (first pair)
                $arr[$inc]['plotAwal'] = '[' . $lat_awals[0] . ',' . $lon_awals[0] . ']';
                $arr[$inc]['blok'] = $value->blok;

                // Assign plotAkhir (last pair)
                $arr[$inc]['plotAkhir'] = '[' . $lat_awals[count($lat_awals) - 1] . ',' . $lon_awals[count($lon_awals) - 1] . ']';

                // Assign other details
                $arr[$inc]['name'] = $value->name;
                $arr[$inc]['afdeling'] = $value->afdeling;
                // $arr[$inc]['latins'] = '[' . $lat_awals[count($lat_awals) - 1] . ',' . $lon_awals[count($lon_awals) - 1] . ']';
                $arr[$inc]['lokasi_kerja'] = $value->lokasi_kerja;
                $inc++;
                // $lat_awal_exp = explode(';', $value->lat_awal);
                // $lon_awal_exp = explode(';', $value->lon_awal);
                // $lat_akhir_exp = explode(';', $value->lat_akhir);
                // $lon_akhir_exp = explode(';', $value->lon_akhir);
                // $name_exp = explode(';', $value->name);
                // for ($i = 0; $i < count($check); $i++) {
                //     if (array_key_exists($i, $name_exp)) {
                //         $arr[$inc]['name'] = $name_exp[$i];
                //     } else {
                //         $arr[$inc]['name'] = $value->name;
                //     }
                //     $arr[$inc]['afdeling'] = $value->afdeling;
                //     $arr[$inc]['lokasi_kerja'] = $value->lokasi_kerja;
                //     $arr[$inc]['blok'] = $value->blok;
                //     $arr[$inc]['plot'] =  '[' . $lon_awal_exp[$i] . ',' . $lat_awal_exp[$i] . '],[' . $lon_akhir_exp[$i] . ',' . $lat_akhir_exp[$i] . ']';

                //     $arr[$inc]['plotAkhir'] =  '[' . $lat_akhir_exp[$i] . ',' . $lon_akhir_exp[$i] . ']';
                //     $inc++;
                // }
                // $arr[$inc]['plotAwal'] =  '[' . $lat_awal_exp[0] . ',' . $lon_awal_exp[0] . ']';
            } else {

                $arr[$inc]['name'] = $value->name;
                $arr[$inc]['plot'] =  '[' . $value->lon_awal . ',' . $value->lat_awal . '],[' . $value->lon_akhir . ',' . $value->lat_akhir . ']';
                $arr[$inc]['plotAwal'] =  '[' . $value->lat_awal . ',' . $value->lon_awal . ']';
                $arr[$inc]['plotAkhir'] =  '[' . $value->lat_akhir . ',' . $value->lon_akhir . ']';
                $arr[$inc]['afdeling'] = $value->afdeling;

                $arr[$inc]['lokasi_kerja'] = $value->lokasi_kerja;
                $arr[$inc]['blok'] = $value->blok;

                $inc++;
            }
        }


        // dd($arr);

        echo json_encode($arr);
    }

    public function plotUserTaksasi(Request $request)
    {

        $estate_input = $request->get('est');
        $tgl = $request->get('tgl');

        // $tglData = Carbon::parse($tgl);

        // $kemarin = $tglData->subDay()->format('Y-m-d') . ' 00:00:00';

        // $newConvert = new Carbon($kemarin);

        // $hariIni = $newConvert->addDays(2);

        // $hariIni = ($hariIni->format('Y-m-d')) . ' 00:00:00';

        $queryData = DB::connection('mysql2')->table('taksasi')
            ->select('taksasi.*')
            ->whereDate('taksasi.waktu_upload', $tgl)
            ->where('lokasi_kerja', $estate_input)
            ->orderBy('taksasi.waktu_upload', 'desc')
            ->get();


        $arr = array();
        $inc = 0;
        foreach ($queryData as $key => $value) {
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
                    $arr[$inc]['lokasi_kerja'] = $value->lokasi_kerja;
                    $arr[$inc]['blok'] = $value->blok;
                    $arr[$inc]['plot'] =  '[' . $lon_awal_exp[$i] . ',' . $lat_awal_exp[$i] . '],[' . $lon_akhir_exp[$i] . ',' . $lat_akhir_exp[$i] . ']';
                    $arr[$inc]['plotAwal'] =  '[' . $lat_awal_exp[$i] . ',' . $lon_awal_exp[$i] . ']';
                    $arr[$inc]['plotAkhir'] =  '[' . $lat_akhir_exp[$i] . ',' . $lon_akhir_exp[$i] . ']';
                    $inc++;
                }
            } else {

                $arr[$inc]['name'] = $value->name;
                $arr[$inc]['plot'] =  '[' . $value->lon_awal . ',' . $value->lat_awal . '],[' . $value->lon_akhir . ',' . $value->lat_akhir . ']';
                $arr[$inc]['plotAwal'] =  '[' . $value->lat_awal . ',' . $value->lon_awal . ']';
                $arr[$inc]['plotAkhir'] =  '[' . $value->lat_akhir . ',' . $value->lon_akhir . ']';
                $arr[$inc]['afdeling'] = $value->afdeling;

                $arr[$inc]['lokasi_kerja'] = $value->lokasi_kerja;
                $arr[$inc]['blok'] = $value->blok;

                $inc++;
            }
        }

        $list_afd = array();
        foreach ($arr as $key => $value) {
            if (!in_array($value['afdeling'], $list_afd)) {
                $list_afd[] = $value['afdeling'];
            }
        }


        $user = array();
        foreach ($list_afd as $key2 => $data) {
            foreach ($arr as $key => $value) {
                if ($data == $value['afdeling']) {
                    $user[$data][] = $value['name'];
                }
            }
        }

        $userTaksasi = array();

        foreach ($user as $key => $value) {
            $userTaksasi[$key] = array_values(array_unique($value));
        }


        echo json_encode($userTaksasi);
    }

    public function history_taksasi(Request $request)
    {
        $tgl = $request->get('tgl');

        $tglData = Carbon::parse();

        $kemarin = $tglData->subDay()->format('Y-m-d') . ' 00:00:00';

        $newConvert = new Carbon($kemarin);

        $hariIni = $newConvert->addDays(2);

        $hariIni = ($hariIni->format('Y-m-d')) . ' 00:00:00';

        $queryData = DB::connection('mysql2')->table('taksasi')
            ->select('taksasi.*')
            ->whereBetween('taksasi.waktu_upload', [$kemarin, $hariIni])
            ->orderBy('taksasi.waktu_upload', 'desc')
            ->get()
            ->groupBy('lokasi_kerja');

        $queryData = json_decode($queryData, true);

        $list_estate = array();
        foreach ($queryData as $key => $value) {
            foreach ($value as $key2 => $data) {
                if (!in_array($data['lokasi_kerja'], $list_estate)) {
                    $list_estate[] = $data['lokasi_kerja'];
                }
            }
        }

        if ($list_estate) {
            return view('taksasi.history', ['list_estate' => $list_estate]);
        } else {
            return view('taksasi.history');
        }
    }

    public function getListEstate(Request $request)
    {
        $tgl = $request->get('tgl');

        // $tglData = Carbon::parse($tgl);

        // $kemarin = $tglData->subDay()->format('Y-m-d') . ' 00:00:00';

        // $newConvert = new Carbon($kemarin);

        // $hariIni = $newConvert->addDays(2);

        // $hariIni = ($hariIni->format('Y-m-d')) . ' 00:00:00';

        $queryData = DB::connection('mysql2')->table('taksasi')
            ->select('taksasi.*')
            ->whereDate('taksasi.waktu_upload', $tgl)
            ->orderBy('taksasi.waktu_upload', 'desc')
            ->get()
            ->groupBy('lokasi_kerja');

        $queryData = json_decode($queryData, true);

        $list_estate = array();
        foreach ($queryData as $key => $value) {
            foreach ($value as $key2 => $data) {
                if (!in_array($data['lokasi_kerja'], $list_estate)) {
                    $list_estate[] = $data['lokasi_kerja'];
                }
            }
        }

        echo json_encode($list_estate);
    }

    public function getDataRegional(Request $request)
    {
        $pil_reg = $request->get('id_reg');
        $takReq = $request->get('tak');

        $reg_all = Regional::all()->pluck('nama');
        $reg_all = json_decode($reg_all);

        $reg = Regional::with("wilayah")->get();
        $reg_arr = array();

        foreach ($reg as $key => $value) {
            foreach ($value->wilayah as $key2 => $data) {
                $reg_arr[$key][$data->nama] =  Wilayah::with("estate")->find($data->id)->estate->pluck('nama', 'est');
            }
        }

        $est_wil_reg = array();
        foreach ($reg_arr as $key => $value) {
            foreach ($value as $key2 => $data) {
                foreach ($data as $key3 => $datas) {
                    $est_wil_reg[$key][$key3] = $datas;
                }
            }
        }

        $est_array = array();
        $keb_pem_array = array();
        $log_tak_est = '';
        $log_keb_pemanen_est = '';
        $est = '';
        $dateToday = Carbon::now()->format('Y-m-d');
        $tglData = $request->get('tgl');

        $hariIni = $tglData . ' 00:00:00';

        $newConvert = new Carbon($hariIni);

        $besok = $newConvert->addDays();
        $besok = ($besok->format('Y-m-d')) . ' 00:00:00';

        $afdeling = array('OA', 'OB', 'OC', 'OD', 'OF', 'OA');

        $query = DB::connection('mysql2')->table('taksasi')
            ->select('taksasi.*')
            ->whereBetween('taksasi.waktu_upload', [$hariIni, $besok])
            ->orderBy('taksasi.afdeling', 'asc')
            ->get();

        if ($query->first() != null) {
            $query = json_decode($query);
            $inc = 0;
            foreach ($est_wil_reg[$pil_reg] as $key => $value) {
                $sum_tak_est = 0;
                $sum_keb_pemanen_est = 0;
                foreach ($query as $data) {
                    if ($data->lokasi_kerja == $key) {
                        $sum_tak_est += $data->taksasi;
                        $sum_keb_pemanen_est += $data->pemanen;
                    }
                }

                $est_array[$key] = $sum_tak_est;
                $keb_pem_array[$key] = $sum_keb_pemanen_est;
                $inc++;
            }
        }

        if ($takReq == 1) {
            echo json_encode($est_array);
        } else {
            echo json_encode($keb_pem_array);
        }
        exit;
    }

    function getDataAfd(Request $request)
    {
        $pil_reg = $request->get('id_reg');
        $pil_est = $request->get('id_est');
        $takReq = $request->get('tak');
        $reg_all = Regional::all()->pluck('nama');
        $reg_all = json_decode($reg_all);

        // dd($pil_est);
        $reg = Regional::with("wilayah")->get();
        $reg_arr = array();
        foreach ($reg as $key => $value) {
            foreach ($value->wilayah as $key2 => $data) {
                $reg_arr[$key][$data->nama] =  Wilayah::with("estate")->find($data->id)->estate->pluck('nama', 'est');
            }
        }

        $est_wil_reg = array();
        foreach ($reg_arr as $key => $value) {
            foreach ($value as $key2 => $data) {
                foreach ($data as $key3 => $datas) {
                    $est_wil_reg[$key][$key3] = $datas;
                }
            }
        }

        $id_est = Estate::where('est', $pil_est)->first()->id;
        $queryEst = Estate::with("afdeling")->find($id_est);

        $list_afd_est = $queryEst->afdeling->pluck('nama');

        $afd_array = array();
        $keb_pem_array = array();
        $dateToday = Carbon::now()->format('Y-m-d');
        $tglData = $request->has('tgl') ? $request->input('tgl') : $defaultHari = $dateToday;

        $hariIni = $tglData . ' 00:00:00';

        $newConvert = new Carbon($hariIni);

        $besok = $newConvert->addDays();
        $besok = ($besok->format('Y-m-d')) . ' 00:00:00';

        $color_chart = array(
            '#001E3C',
            '#AB221D',
            '#5CAF50',
            '#7CAF50',
            '#8CAF50',
            '#AB221D',
            '#AB221D',
            '#AB221D',
            '#AB221D',
            '#AB221D',
            '#AB221D',
            '#AB221D',
            '#AB221D',
            '#AB221D',
            '#282C34'
        );

        $query = DB::connection('mysql2')->table('taksasi')
            ->select('taksasi.*')
            ->whereBetween('taksasi.waktu_upload', [$hariIni, $besok])
            ->where('lokasi_kerja', $queryEst->est)
            ->orderBy('taksasi.afdeling', 'asc')
            ->get();

        $list_afd_est = json_decode($list_afd_est);
        if ($query->first() != null) {
            $query = json_decode($query);
            foreach ($list_afd_est as $key => $value) {
                $sum_tak_afd = 0;
                $sum_keb_pemanen_afd = 0;
                foreach ($query as $key2 => $data) {
                    if ($data->afdeling == $value) {
                        $sum_tak_afd += $data->taksasi;
                        $sum_keb_pemanen_afd += $data->pemanen;
                    }
                }
                $afd_array[$value] = round($sum_tak_afd, 2);
                $keb_pem_array[$value] = $sum_keb_pemanen_afd;
            }
        }

        if ($takReq == 1) {
            echo json_encode($afd_array);
        } else {
            echo json_encode($keb_pem_array);
        }
        exit;
    }

    public function ds_pemupukan(Request $request)
    {

        // if ($request->ajax()) {
        $query = DB::connection('mysql2')->table('monitoring_pemupukan')
            ->select('monitoring_pemupukan.*', 'pupuk.nama as nama_pupuk')
            ->join('pupuk', 'monitoring_pemupukan.jenis_pupuk_id', '=', 'pupuk.id')
            ->orderBy('monitoring_pemupukan.waktu_upload', 'DESC')
            ->get();

        // dd($query);
        foreach ($query as $item) {
            $hari = Carbon::parse($item->waktu_upload)->locale('id');
            $hari->settings(['formatFunction' => 'translatedFormat']);
            $item->tanggal = $hari->format('j F Y');
        }

        $listEst = array();
        foreach ($query as $key => $value) {
            if (!in_array($value->estate, $listEst)) {
                $listEst[] = $value->estate;
            }
        }

        $allEst = Estate::with("afdeling")->get();

        $listAfdEst = array();
        foreach ($allEst as $key => $value) {
            foreach ($listEst as $key2 => $value2) {
                if ($value->est == $value2) {
                    foreach ($value->afdeling as $key => $value3) {
                        $listAfdEst[$value2][] = $value3->nama;
                    }
                }
            }
        }
        // dd($listAfdEst);

        // dd($query[0]->estate);

        $arrValPerEstAfd = array();
        foreach ($listAfdEst as $key => $value) {
            foreach ($value as $key2 => $val) {
                foreach ($query as $key3 => $value2) {
                    if ($val == $value2->afdeling && $key == $value2->estate) {
                        $arrValPerEstAfd[$key][$val][] = $value2;
                    }
                }
            }
        }

        // dd($arrValPerEstAfd);
        $raw = array();
        $inc = 0;
        foreach ($arrValPerEstAfd as $key => $est) {
            foreach ($est as $key2 => $afd) {
                // dd($afd);
                foreach ($afd as $key3 => $data) {
                    $raw[$inc][$key2]['tanggal'] = $data->tanggal;
                    $raw[$inc][$key2]['estate'] = $key;
                    $raw[$inc][$key2]['afdeling'] = $key2;
                    $raw[$inc][$key2]['jenis_pupuk'] = $data->nama_pupuk;
                }
            }
            $inc++;
        }

        $arrView = array();
        $inc = 0;
        foreach ($raw as $key => $value) {
            foreach ($value as $key2 => $val) {
                $arrView[$inc] = $val;
                $inc++;
            }
        }

        //     return DataTables::of($arrView)
        //         ->editColumn('afdeling', function ($model) {
        //             $newFormatDate = Carbon::parse($model['tanggal']);
        //             return '<a href="' . route('detail_pemupukan', ['est' => $model['estate'], 'afd' => $model['afdeling'], 'tanggal' => $newFormatDate->format('d-m-Y')]) . '">  ' . $model['afdeling'] . '    </a>';
        //         })
        //         ->rawColumns(['afdeling'])
        //         ->editColumn('biSm1Rekom', function ($model) {
        //             return '-';
        //         })
        //         ->editColumn('sbiSm1Rekom', function ($model) {
        //             return '-';
        //         })
        //         ->editColumn('biSm2Rekom', function ($model) {
        //             return '-';
        //         })
        //         ->editColumn('sbiSm2Rekom', function ($model) {
        //             return '-';
        //         })
        //         ->editColumn('biSm1Apl', function ($model) {
        //             return '-';
        //         })
        //         ->editColumn('sbiSm1Apl', function ($model) {
        //             return '-';
        //         })
        //         ->editColumn('biSm2Apl', function ($model) {
        //             return '-';
        //         })
        //         ->editColumn('sbiSm2Apl', function ($model) {
        //             return '-';
        //         })
        //         ->editColumn('achieve', function ($model) {
        //             return '-';
        //         })
        //         ->editColumn('varian', function ($model) {
        //             return '-';
        //         })
        //         ->editColumn('annual', function ($model) {
        //             return '-';
        //         })
        //         ->editColumn('kgpokok', function ($model) {
        //             return '-';
        //         })
        //         ->addColumn('action', function ($model) {
        //             return '<a href="" class="" >  <i class="nav-icon fa fa-eye" style="color:#1E6E42"></i>    </a>';
        //         })
        //         ->make(true);
        // }

        return view('mon_pemupukan.dashboard');
    }




    public function getDataTable(Request $request)
    {
        $estate_input = $request->get('est');
        $tgl = $request->get('tgl');



        // $tglData = Carbon::parse($tgl);

        // $kemarin = $tglData->subDay()->format('Y-m-d') . ' 00:00:00';

        // $newConvert = new Carbon($kemarin);

        // $hariIni = $newConvert->addDays(2);

        // $hariIni = ($hariIni->format('Y-m-d')) . ' 00:00:00';

        $queryData = DB::connection('mysql2')->table('taksasi')
            ->select('taksasi.*', DB::raw("DATE_FORMAT(taksasi.waktu_upload, '%d %M %y') as tanggal_formatted"))
            ->whereDate('taksasi.waktu_upload', $tgl)
            ->where('lokasi_kerja', $estate_input)
            ->orderBy('taksasi.waktu_upload', 'desc')
            ->get();

        $inc = 0;
        foreach ($queryData as $key => $value) {
            $path_arr = explode(';', $value->br_kanan);
            $value->jumlah_path = count($path_arr);
            $value->tanggal_upload = Carbon::parse($value->waktu_upload)->format('d M Y');
            $value->ritase = ceil($value->taksasi / 6500);
            $value->akp_round = round($value->akp, 2);
            $value->tak_round = number_format($value->taksasi, 2, ",", ".");
            $inc++;
        }

        echo json_encode($queryData);
    }

    public function detail_pemupukan(Request $request)
    {
        $estate_input = request()->route('est');
        $afdeling_input = request()->route('afd');
        $tanggal = request()->route('tanggal');

        // dd($estate_input);
        $newDate = Carbon::parse($tanggal);

        $newDate = $newDate->format('Y-m-d');

        //menentukan plot estate
        $queryData = DB::connection('mysql2')->table('monitoring_pemupukan')
            ->select('monitoring_pemupukan.*', 'pupuk.nama as nama_pupuk')
            ->join('pupuk', 'monitoring_pemupukan.jenis_pupuk_id', '=', 'pupuk.id')
            ->whereDate('monitoring_pemupukan.waktu_upload', $newDate)
            ->where('monitoring_pemupukan.estate', $estate_input)
            ->where('monitoring_pemupukan.afdeling', $afdeling_input)
            ->groupBy('monitoring_pemupukan.waktu_upload')
            ->orderBy('monitoring_pemupukan.waktu_upload', 'DESC')
            ->get();



        $plotLine = array();
        foreach ($queryData as $key => $value) {
            if (str_contains($value->lat_awal, ';')) {
                $splitted_lat_awal = explode(';', $value->lat_awal);
                $splitted_lon_awal = explode(';', $value->lon_awal);
                $splitted_lat_akhir = explode(';', $value->lat_akhir);
                $splitted_lon_akhir = explode(';', $value->lon_akhir);
                for ($i = 0; $i < count($splitted_lat_awal); $i++) {
                    $plotLine[] =  '[' . $splitted_lon_awal[$i] . ',' . $splitted_lat_awal[$i] . '],[' . $splitted_lon_akhir[$i] . ',' . $splitted_lat_akhir[$i] . ']';
                }
            } else {
                $plotLine[] =  '[' . $value->lon_awal . ',' . $value->lat_awal . '],[' . $value->lon_akhir . ',' . $value->lat_akhir . ']';
            }
        }

        // dd($queryData);

        $plotMarker = array();
        foreach ($queryData as $key => $value) {
            if (str_contains($value->lat_awal, ';')) {
                $splitted_lat_awal = explode(';', $value->lat_awal);
                $splitted_lon_awal = explode(';', $value->lon_awal);
                for ($i = 0; $i < count($splitted_lat_awal); $i++) {
                    $plotMarker[] =  '[' . $splitted_lat_awal[$i] . ',' . $splitted_lon_awal[$i] . ']';
                }
            } else {
                $plotMarker[] =  '[' . $value->lat_awal . ',' . $value->lon_awal . ']';
            }
        }

        $line_pemupukan = array();
        $plot = '';
        foreach ($queryData as $item) {
            $hari = Carbon::parse($item->waktu_upload)->locale('id');
            $hari->settings(['formatFunction' => 'translatedFormat']);
            $item->tanggal = $hari->format('j F Y H:i:s');

            //terpupuk
            $sub = substr($item->dipupuk, 1, -1);
            $formatted = explode(", ", $sub);
            $countDipupuk = 0;
            foreach ($formatted as $key => $value) {
                if ($value == 1) {
                    $countDipupuk++;
                }
            }

            //jenis pupuk
            $sub = substr($item->jenis_pupuk, 1, -1);
            $formatted = explode(", ", $sub);
            $countJenisPupuk = 0;
            foreach ($formatted as $key => $value) {
                if ($value == 1) {
                    $countJenisPupuk++;
                }
            }

            //lokasi pupuk
            $sub = substr($item->lokasi_pupuk, 1, -1);
            $formatted = explode(", ", $sub);
            $countLokasiPupuk = 0;
            foreach ($formatted as $key => $value) {
                if ($value == 1) {
                    $countLokasiPupuk++;
                }
            }

            //sebaran pupuk
            $sub = substr($item->sebar_pupuk, 1, -1);
            $formatted = explode(", ", $sub);
            $countSebarPupuk = 0;
            foreach ($formatted as $key => $value) {
                if ($value == 1) {
                    $countSebarPupuk++;
                }
            }

            if ($item->foto != '') {
                $splitted = explode(";", $item->foto);
                for ($i = 0; $i < count($splitted); $i++) {
                    $key = 'foto_' . $i;
                    $item->$key = $splitted[$i];
                }
            }

            $item->terpupuk = $countDipupuk;
            $item->tersebar = $countSebarPupuk;
            $item->terlokasi = $countLokasiPupuk;
            $item->kesesuaian_jenis = $countJenisPupuk;

            $plot = '[' . $item->lon_awal . ',' . $item->lat_awal . '],[' . $item->lon_akhir . ',' . $item->lat_akhir . ']';
            $line_pemupukan['plot'][] = rtrim($plot, ',');
        }

        $plotEstate = DB::connection('mysql2')->table('estate_plot')
            ->select('*')
            ->join('estate', 'estate_plot.est', '=', 'estate.est')
            ->where('estate.est', $estate_input)
            ->get();

        $estate_plot = array();
        $plot = '';
        $estate = '';

        foreach ($plotEstate as $key2 => $val) {
            $plot .= '[' . $val->lon . ',' . $val->lat . '],';
            $estate = $val->nama;
        }
        $estate_plot['est'] = $estate . ' Estate';
        $estate_plot['plot'] =  rtrim($plot, ',');

        $queryData = json_decode($queryData, true);

        $list_blok = array();
        foreach ($queryData as $key => $value) {
            $list_blok[$estate_input][] = $value['blok'];
        }

        $blokPerEstate = array();
        $estateQuery = Estate::with("afdeling")->where('est', $estate_input)->get();
        foreach ($estateQuery as $key => $value) {
            $i = 0;
            foreach ($value->afdeling as $key2 => $data) {
                $blokPerEstate[$estate_input][$data->nama] =  Afdeling::with("blok")->find($data->id)->blok->pluck('nama', 'id');
                $i++;
            }
        }

        $result_list_blok = array();
        foreach ($list_blok as $key => $value) {
            foreach ($value as $key2 => $data) {
                if (strlen($data) == 5) {
                    $result_list_blok[$key][$data] = substr($data, 0, -2);
                }
                if (strlen($data) == 6) {
                    $sliced = substr_replace($data, '', 1, 1);
                    $result_list_blok[$key][$data] = substr($sliced, 0, -2);
                }
            }
        }
        // dd($result_list_blok);
        // dd(substr('G12a', 1, 1));
        // if (preg_match("/^[a-z]+$/", 'G12a'))

        //     print "Yes\n";
        // else
        //     print "No\n";

        $result_list_all_blok = array();
        foreach ($blokPerEstate as $key2 => $value) {
            foreach ($value as $key3 => $afd) {
                foreach ($afd as $key4 => $data) {
                    if (strlen($data) == 4) {
                        if (substr($data, 1, 1) != '0') {
                            $result_list_all_blok[$key2][] = $data;
                        } else {
                            $result_list_all_blok[$key2][] = substr_replace($data, '', 1, 1);
                        }
                    } else {
                        $result_list_all_blok[$key2][] = $data;
                    }
                }
            }
        }

        // dd($result_list_all_blok);

        // //bandingkan list blok query dan list all blok dan get hanya blok yang cocok
        $result_blok = array();
        if (array_key_exists($estate_input, $result_list_all_blok)) {
            $query = array_unique($result_list_all_blok[$estate_input]);
            $result_blok[$estate_input] = array_intersect($result_list_blok[$estate_input], $query);
        }

        // dd($result_blok);

        // // //get lat lang dan key $result_blok atau semua list_blok


        $blokLatLn = array();

        foreach ($result_blok as $key => $value) {
            $inc = 0;
            foreach ($value as $key2 => $data) {

                $estate = DB::connection('mysql2')->table('estate')
                    ->select('estate.*')
                    ->where('estate.est', $estate_input)
                    ->leftJoin('afdeling', 'afdeling.id', 'afdeling.estate')
                    // ->where('')
                    ->first();
                $nama_estate = $estate->nama;
                $id_estate = $estate->id;
                // dd($estate);

                // // dd($estate);
                $queryTest = DB::connection('mysql2')->table('afdeling')
                    ->select('afdeling.*')
                    ->leftJoin('estate', 'estate.wil', 'estate.id', 'estate.wil')

                    ->where('afdeling.estate', '=', $id_estate)
                    ->where('afdeling.nama', '=', $afdeling_input)
                    ->get();

                $blok_afd = [];
                foreach ($queryTest as $key => $value) {
                    // dd($value);
                    $blok_afd = $value->id;
                }
                // dd($blok_afd);

                $newData = substr_replace($data, '0', 1, 0);
                // dd($newData);
                $query = '';
                $query = DB::connection('mysql2')->table('blok')
                    ->select('blok.*')
                    ->where('blok.nama', $newData)
                    ->where('blok.afdeling', $blok_afd)
                    ->orWhere('blok.nama', $data)
                    // ->where('afdeling.')
                    ->get();

                // dd($query);
                $latln = '';
                foreach ($query as $key3 => $val) {

                    $latln .= '[' . $val->lon . ',' . $val->lat . '],';
                }

                // dd($latln);



                $blokLatLn[$inc]['blok'] = $key2;
                $blokLatLn[$inc]['estate'] = $nama_estate;
                $blokLatLn[$inc]['latln'] = rtrim($latln, ',');
                $inc++;
            }
        }


        $blokLatLn = json_encode($blokLatLn);
        $plotLine = json_encode($plotLine);
        $plotMarker = json_encode($plotMarker);

        $plotEstateJson = json_encode($estate_plot);

        // dd($queryData);
        return view('mon_pemupukan.detail', ['plotMarker' => $plotMarker, 'plotLine' => $plotLine, 'blokLatLn' => $blokLatLn, 'plotEstateJson' => $plotEstateJson, 'queryData' => $queryData, 'est' => $estate_input, 'afd' => $afdeling_input]);
    }

    public function rekom_aplikasi(Request $request)
    {
        $estate_input = request()->route('est');
        $afdeling_input = request()->route('afd');
        $rot = request()->route('rot');
        $sm = request()->route('sm');
        $tanggal = request()->route('tanggal');

        $getYear = Carbon::parse($tanggal)->format('Y');
        $getDate = Carbon::parse($tanggal)->format('Y-m-d');

        if ($rot == 'R1') {
            $pupuk_rekom = array('NPK 13/6/27/4/0.65', 'NPK 7/6/34', 'Urea', 'RP', 'MOP', 'Kies', 'Dol', 'HGFB', 'Zincop Chelated', 'Zincop Fe Chelated', 'Fe Chelated', 'Boron Cair');
            $sm = 'sm1';
            $semester = 'Semester 1';
            $rot = 'r1';
            $rotasi = 'Rotasi 1';
            $from = $getYear . '-01-01';
            $to = $getYear . '-03-31';
        } else if ($rot == 'R2') {
            $pupuk_rekom = array('NPK 13/6/27/4/0.65', 'NPK 7/6/34', 'Urea', 'MOP');
            $sm = 'sm1';
            $semester = 'Semester 1';
            $rot = 'r2';
            $rotasi = 'Rotasi 2';
            $from = $getYear . '-04-01';
            $to = $getYear . '-06-31';
        } else if ($rot == 'R3') {
            $pupuk_rekom = array('NPK 13/6/27/4/0.65', 'NPK 7/6/34', 'Urea', 'MOP');
            $sm = 'sm2';
            $semester = 'Semester 2';
            $rot = 'r3';
            $rotasi = 'Rotasi 3';
            $from = $getYear . '-07-01';
            $to = $getYear . '-09-31';
        } else {
            $pupuk_rekom = array('NPK 13/6/27/4/0.65', 'NPK 7/6/34', 'Dol', 'HGFB', 'Zincop Chelated', 'Zincop Fe Chelated', 'Fe Chelated', 'Boron Cair');
            $sm = 'sm2';
            $semester = 'Semester 2';
            $rot = 'r4';
            $rotasi = 'Rotasi 4';
            $from = $getYear . '-10-01';
            $to = $getYear . '-12-31';
        }

        $queryData = DB::connection('mysql2')->table('monitoring_pemupukan')
            ->select('monitoring_pemupukan.*', 'pupuk.nama as nama_pupuk')
            ->join('pupuk', 'monitoring_pemupukan.jenis_pupuk_id', '=', 'pupuk.id')
            // ->whereBetween('monitoring_pemupukan.waktu_upload', [$from, $to])
            ->where('monitoring_pemupukan.waktu_upload', 'like', '%' . $getDate . '%')
            ->where('monitoring_pemupukan.estate', $estate_input)
            ->where('monitoring_pemupukan.afdeling', $afdeling_input)
            ->groupBy('monitoring_pemupukan.blok')
            ->orderBy('monitoring_pemupukan.waktu_upload', 'ASC')
            ->get();

        $arrResult = array();
        foreach ($pupuk_rekom as $key3 => $value3) {
            foreach ($queryData as $key4 => $value4) {
                $spl = substr($value4->blok, 0, 3);
                $spl1 = str_split($spl);
                $blok = $spl1[0] . 0 . $spl1[1] . $spl1[2];

                $queryRekom = DB::connection('mysql2')->table('rekom_pemupukan')
                    ->select('*')
                    ->where('tahun', $getYear)
                    ->where('est', $estate_input)
                    ->where('afd', $afdeling_input)
                    ->where('blok', $blok)
                    ->get();

                if (empty($queryRekom->toArray())) {
                    $arrResult[$blok][$key4]['nama'] = $value4->nama_pupuk;
                    $arrResult[$blok][$key4]['rekom'] = '-';
                    $arrResult[$blok][$key4]['apl'] = $value4->dosis_pupuk;
                    if (empty($arrResult[$blok][$key4]['apl'])) {
                        $arrResult[$blok][$key4]['apl'] = '-';
                    }
                } else {
                    foreach ($queryRekom as $key1 => $value1) {
                        foreach ($value1 as $key2 => $value2) {
                            $arrResult[$blok][$key3]['nama'] = $value3;
                            if ($rot == 'r1') {
                                if ($arrResult[$blok][$key3]['nama'] == 'NPK 13/6/27/4/0.65') {
                                    $rotResult = $sm . '_' . $rot . '_npk1';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'NPK 7/6/34') {
                                    $rotResult = $sm . '_' . $rot . '_npk2';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'Urea') {
                                    $rotResult = $sm . '_' . $rot . '_urea';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'RP') {
                                    $rotResult = $sm . '_' . $rot . '_rp';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'MOP') {
                                    $rotResult = $sm . '_' . $rot . '_mop';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'Kies') {
                                    $rotResult = $sm . '_' . $rot . '_kies';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'Dol') {
                                    $rotResult = $sm . '_' . $rot . '_dol';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'HGFB') {
                                    $rotResult = $sm . '_' . $rot . '_hgfb';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'Zincop Chelated') {
                                    $rotResult = $sm . '_' . $rot . '_zincop_chelated';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'Zincop Fe Chelated') {
                                    $rotResult = $sm . '_' . $rot . '_zincop_fe_chelated';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'Fe Chelated') {
                                    $rotResult = $sm . '_' . $rot . '_fe_chelated';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'Boron Cair') {
                                    $rotResult = $sm . '_' . $rot . '_boron_cair';
                                }
                            } else if ($rot == 'r2') {
                                if ($arrResult[$blok][$key3]['nama'] == 'NPK 13/6/27/4/0.65') {
                                    $rotResult = $sm . '_' . $rot . '_npk1';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'NPK 7/6/34') {
                                    $rotResult = $sm . '_' . $rot . '_npk2';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'Urea') {
                                    $rotResult = $sm . '_' . $rot . '_urea';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'MOP') {
                                    $rotResult = $sm . '_' . $rot . '_mop';
                                }
                            } else if ($rot == 'r3') {
                                if ($arrResult[$blok][$key3]['nama'] == 'NPK 13/6/27/4/0.65') {
                                    $rotResult = $sm . '_' . $rot . '_npk1';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'NPK 7/6/34') {
                                    $rotResult = $sm . '_' . $rot . '_npk2';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'Urea') {
                                    $rotResult = $sm . '_' . $rot . '_urea';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'MOP') {
                                    $rotResult = $sm . '_' . $rot . '_mop';
                                }
                            } else {
                                if ($arrResult[$blok][$key3]['nama'] == 'NPK 13/6/27/4/0.65') {
                                    $rotResult = $sm . '_' . $rot . '_npk1';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'NPK 7/6/34') {
                                    $rotResult = $sm . '_' . $rot . '_npk2';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'Dol') {
                                    $rotResult = $sm . '_' . $rot . '_dol';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'HGFB') {
                                    $rotResult = $sm . '_' . $rot . '_hgfb';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'Zincop Chelated') {
                                    $rotResult = $sm . '_' . $rot . '_zincop_chelated';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'Zincop Fe Chelated') {
                                    $rotResult = $sm . '_' . $rot . '_zincop_fe_chelated';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'Fe Chelated') {
                                    $rotResult = $sm . '_' . $rot . '_fe_chelated';
                                } else if ($arrResult[$blok][$key3]['nama'] == 'Boron Cair') {
                                    $rotResult = $sm . '_' . $rot . '_boron_cair';
                                }
                            }
                            $arrResult[$blok][$key3]['rekom'] = $value1->$rotResult;
                            if (empty($arrResult[$blok][$key3]['rekom'])) {
                                $arrResult[$blok][$key3]['rekom'] = '-';
                            }

                            if ($key3 == 0 && $value4->jenis_pupuk_id == 25) {
                                $arrResult[$blok][$key3]['apl'] = $value4->dosis_pupuk;
                            } else if ($key3 == 1 && $value4->jenis_pupuk_id == 53) {
                                $arrResult[$blok][$key3]['apl'] = $value4->dosis_pupuk;
                            } else if ($key3 == 2 && $value4->jenis_pupuk_id == 28) {
                                $arrResult[$blok][$key3]['apl'] = $value4->dosis_pupuk;
                            } else if ($key3 == 3 && $value4->jenis_pupuk_id == 12) {
                                $arrResult[$blok][$key3]['apl'] = $value4->dosis_pupuk;
                            } else if ($key3 == 3 && $value4->jenis_pupuk_id == 13) {
                                $arrResult[$blok][$key3]['apl'] = $value4->dosis_pupuk;
                            } else if ($key3 == 3 && $value4->jenis_pupuk_id == 14) {
                                $arrResult[$blok][$key3]['apl'] = $value4->dosis_pupuk;
                            } else if ($key3 == 4 && $value4->jenis_pupuk_id == 15) {
                                $arrResult[$blok][$key3]['apl'] = $value4->dosis_pupuk;
                            } else if ($key3 == 5 && $value4->jenis_pupuk_id == 16) {
                                $arrResult[$blok][$key3]['apl'] = $value4->dosis_pupuk;
                            } else if ($key3 == 5 && $value4->jenis_pupuk_id == 17) {
                                $arrResult[$blok][$key3]['apl'] = $value4->dosis_pupuk;
                            } else if ($key3 == 6 && $value4->jenis_pupuk_id == 18) {
                                $arrResult[$blok][$key3]['apl'] = $value4->dosis_pupuk;
                            } else if ($key3 == 7 && $value4->jenis_pupuk_id == 20) {
                                $arrResult[$blok][$key3]['apl'] = $value4->dosis_pupuk;
                            } else if ($key3 == 7 && $value4->jenis_pupuk_id == 21) {
                                $arrResult[$blok][$key3]['apl'] = $value4->dosis_pupuk;
                            } else if ($key3 == 7 && $value4->jenis_pupuk_id == 49) {
                                $arrResult[$blok][$key3]['apl'] = $value4->dosis_pupuk;
                            } else if ($key3 == 8 && $value4->jenis_pupuk_id == 23) {
                                $arrResult[$blok][$key3]['apl'] = $value4->dosis_pupuk;
                            } else if ($key3 == 9 && $value4->jenis_pupuk_id == 22) {
                                $arrResult[$blok][$key3]['apl'] = $value4->dosis_pupuk;
                            } else if ($key3 == 10 && $value4->jenis_pupuk_id == 51) {
                                $arrResult[$blok][$key3]['apl'] = $value4->dosis_pupuk;
                            } else if ($key3 == 11 && $value4->jenis_pupuk_id == 34) {
                                $arrResult[$blok][$key3]['apl'] = $value4->dosis_pupuk;
                            }

                            if (empty($arrResult[$blok][$key3]['apl'])) {
                                $arrResult[$blok][$key3]['apl'] = '-';
                            }
                        }
                    }
                }
            }
        }

        // dd($arrResult);
        return view('mon_pemupukan.rekom_aplikasi', ['arrResult' => $arrResult, 'est' => $estate_input, 'afd' => $afdeling_input, 'tgl' => $tanggal, 'rot' => $rotasi, 'sm' => $semester]);
    }


    public function getNameWilayah(Request $request)
    {

        $id_reg = $request->get('regional');

        $reg_all = Regional::where('nama', '!=', 'Regional V')->get()->toArray();

        $id_reg = $reg_all[$id_reg]['id'];
        $specific_wil = 'Wilayah 3';
        $wil_all = Wilayah::
            // where('regional', $id_reg)
            where('nama', $specific_wil)
            ->pluck('nama')->toArray();

        $output = '';
        $inc_est = 1;
        foreach ($wil_all as $key => $val) {
            $output .= '<option value="' . $key . '">' . $val . '</option>';
            $inc_est++;
        }

        echo $output;
    }

    public function getPdfqc($est, $date)
    {
        // $tgl = $request->get('est');

        // $estate_input = $request->get('date');

        $estate_input = $est;
        $tgl = $date;


        // dd($tgl, $estate_input);
        $queryEstate = DB::connection('mysql2')->table('estate')
            ->select('estate.*')
            ->where('est', $estate_input)
            ->first();

        // dd($queryEstate);

        $queryData = DB::connection('mysql2')->table('taksasi')
            ->select('taksasi.*')
            ->whereDate('taksasi.waktu_upload', $tgl)
            ->where('lokasi_kerja', $estate_input)
            ->orderBy('taksasi.afdeling', 'asc')
            ->get();

        $newData = $queryData->groupBy(['afdeling']);
        $newData = json_decode($newData, true);


        $Taksasi = array();
        foreach ($newData as $key => $value) {
            $Taksasi[$key] = array(); // Initialize the index in $Taksasi

            foreach ($value as $key1 => $value1) {
                $brkiri = explode(';', $value1['br_kiri']);
                $brkanan = explode(';', $value1['br_kanan']);
                $ffbkiri = explode(';', $value1['ffb_kiri']);
                $ffb_kanan = explode(';', $value1['ffb_kanan']);

                $maxCount = max(count($brkiri), count($brkanan));


                for ($i = 0; $i < $maxCount; $i++) {
                    $newIndex = count($Taksasi[$key]);
                    $Taksasi[$key]["baris{$newIndex}"]["br_kiri"] = isset($brkiri[$i]) ? $brkiri[$i] : null;
                    $Taksasi[$key]["baris{$newIndex}"]["br_kanan"] = isset($brkanan[$i]) ? $brkanan[$i] : null;
                    $Taksasi[$key]["baris{$newIndex}"]["ffbkiri"] = isset($ffbkiri[$i]) ? $ffbkiri[$i] : null;
                    $Taksasi[$key]["baris{$newIndex}"]["ffb_kanan"] = isset($ffb_kanan[$i]) ? $ffb_kanan[$i] : null;
                    $Taksasi[$key]["baris{$newIndex}"]["sph"] = $value1['sph'];
                    $Taksasi[$key]["baris{$newIndex}"]["blok"] = $value1['blok'];
                    $Taksasi[$key]["baris{$newIndex}"]["afdeling"] = $value1['afdeling'];
                    $Taksasi[$key]["baris{$newIndex}"]["lokasi_kerja"] = $value1['lokasi_kerja'];
                    $Taksasi[$key]["baris{$newIndex}"]["name"] = $value1['name'];
                    $Taksasi[$key]["baris{$newIndex}"]["luas"] = $value1['luas'];
                    $Taksasi[$key]["baris{$newIndex}"]["sph"] = $value1['sph'];
                    $Taksasi[$key]["baris{$newIndex}"]["bjr"] = $value1['bjr'];
                    $Taksasi[$key]["baris{$newIndex}"]["jumlah_pokok"] = $value1['jumlah_pokok'];
                    $Taksasi[$key]["baris{$newIndex}"]["jumlah_janjang"] = $value1['jumlah_janjang'];
                }
            }
        }



        foreach ($queryData as $key => $value) {
            $path_arr = explode(';', $value->br_kanan);
            $value->jumlah_path = count($path_arr);
            $value->ritase = ceil($value->taksasi / 6500);
        }

        $queryCount = DB::connection('mysql2')->table('taksasi')
            ->select('taksasi.afdeling', DB::raw('count(taksasi.afdeling) as countData'))
            ->whereDate('taksasi.waktu_upload', $tgl)
            ->where('lokasi_kerja', $estate_input)
            ->orderBy('taksasi.afdeling', 'asc')
            ->groupBy('taksasi.afdeling')
            ->pluck('countData', 'afdeling');

        $arr = array();

        $dataAfd = array();

        foreach ($queryCount as $key => $value) {
            $dataAfd[$key]['afdeling'] = $key;
            $dataAfd[$key]['blok'] = '-';
            $dataAfd[$key]['luas'] = '-';
            $dataAfd[$key]['sph'] = '-';
            $dataAfd[$key]['bjr'] = '-';
            $dataAfd[$key]['jumlah_path'] = '-';
            $dataAfd[$key]['jumlah_pokok'] = '-';
            $dataAfd[$key]['jumlah_janjang'] = '-';
            $dataAfd[$key]['akp'] = '-';
            $dataAfd[$key]['taksasi'] = '-';
            $dataAfd[$key]['pemanen'] = '-';
            $dataAfd[$key]['ritase'] = '-';
        }

        $total_luas = 0;
        $total_sph = 0;
        $total_bjr = 0;
        $total_path = 0;
        $total_pokok = 0;
        $total_janjang = 0;
        $total_akp = 0;
        $total_taksasi = 0;
        $total_pemanen = 0;
        $increment = 0;
        foreach ($queryCount as $key => $value) {
            for ($i = 0; $i < $value; $i++) {
                $tak = 0;
                $akp = 0;
                $sum_luas = 0;
                $sum_sph = 0;
                $sum_bjr = 0;
                $sum_path = 0;
                $sum_pokok = 0;
                $sum_janjang = 0;
                $sum_pemanen = 0;
                $inc = 0;
                foreach ($queryData as $key2 => $val) {
                    if ($key == $val->afdeling) {
                        $arr[$key][] = $val;
                        $sum_luas += $val->luas;
                        $sum_sph += $val->sph;
                        $sum_bjr += $val->bjr;
                        $sum_path += $val->jumlah_path;
                        $sum_pokok += $val->jumlah_pokok;
                        $sum_janjang += $val->jumlah_janjang;
                        $sum_pemanen += $val->pemanen;
                        $inc++;
                    }
                }

                $arr[$key]['total']['luas'] = $sum_luas;
                $sum_sph = round($sum_sph / $inc, 2);
                $arr[$key]['total']['sph'] = $sum_sph;
                $sum_bjr = round($sum_bjr / $inc, 2);
                $arr[$key]['total']['bjr'] = $sum_bjr;
                $arr[$key]['total']['jumlah_path'] = $sum_path;
                $arr[$key]['total']['jumlah_pokok'] = $sum_pokok;
                $arr[$key]['total']['jumlah_janjang'] = $sum_janjang;
                $akp = round($sum_janjang / $sum_pokok, 2) * 100;
                $arr[$key]['total']['akp'] = $akp;
                $tak = round(($akp * $sum_luas * $sum_bjr * $sum_sph) / 100, 2);
                $arr[$key]['total']['taksasi'] = $tak;
                $arr[$key]['total']['ritase'] = ceil($tak / 6500);
                $arr[$key]['total']['pemanen'] = $sum_pemanen;
                break;
            }


            $total_luas += $sum_luas;
            $total_sph += $sum_sph;
            $total_bjr += $sum_bjr;
            $total_path += $sum_path;
            $total_pokok += $sum_pokok;
            $total_janjang += $sum_janjang;
            $total_pemanen += $sum_pemanen;
            $increment++;
        }


        $arrEstate = array();
        $arrEstate['luas'] = $total_luas;
        $total_sph =  round($total_sph / $increment, 2);
        $arrEstate['sph'] = $total_sph;
        $total_bjr = round($total_bjr / $increment, 2);
        $arrEstate['bjr'] = $total_bjr;
        $arrEstate['total_path'] = $total_path;
        $arrEstate['total_pokok'] = $total_pokok;
        $arrEstate['total_janjang'] = $total_janjang;
        $total_akp = round($total_janjang / $total_pokok, 2) * 100;
        $arrEstate['total_akp'] = $total_akp;
        $total_taksasi = round(($total_akp * $total_luas * $total_sph * $total_bjr) / 100, 2);
        $arrEstate['total_taksasi'] = $total_taksasi;
        $arrEstate['total_pemanen'] = $total_pemanen;
        $arrEstate['total_ritase'] = ceil($total_taksasi / 6500);

        function tanggal_indo2($tanggal, $cetak_hari = false)
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

        $besok = Carbon::parse($tgl)->addDays()->format('Y-m-d');

        $besokFormatted = strtoupper(tanggal_indo2($besok, true));

        $todayFormatted = strtoupper(tanggal_indo2($tgl, true));

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
        $queryDataNew = DB::connection('mysql2')->table('taksasi')
            ->select('taksasi.*')
            ->whereDate('taksasi.waktu_upload', $tgl)
            ->where('lokasi_kerja', $estate_input)
            ->orderBy('taksasi.afdeling', 'asc')
            // ->groupBy('taksasi.afdeling')
            ->get();

        $queryDataNew = $queryDataNew->groupBy(['afdeling', 'blok']);
        $queryDataNew = json_decode($queryDataNew, true);


        $Taksasix = array();
        foreach ($queryDataNew as $key => $value) {
            foreach ($value as $key1 => $value1) {
                $jumlahpk = 0;
                $sum_janjang = 0;
                $sum_pokok = 0;
                $pemanen = 0;

                foreach ($value1 as $key2 => $value2) {
                    // dd($value2);

                    $sum_janjang += $value2['jumlah_janjang'];
                    $sum_pokok += $value2['jumlah_pokok'];
                    $pemanen += $value2['pemanen'];



                    // dd($value2);
                }
                $akp = round(($sum_janjang / $sum_pokok) * 100, 2);
                $tak = round(($akp * $value2['luas'] * $value2['bjr'] * $value2['sph']) / 100, 1);
                // $sum_sph = round($sum_sph / $inc, 2);
                $Taksasix[$key][$key1]['luas'] = $value2['luas'];
                $Taksasix[$key][$key1]['bjr'] = $value2['bjr'];
                $Taksasix[$key][$key1]['sph'] = $value2['sph'];
                $Taksasix[$key][$key1]['jumlah_path'] = count($value1);
                $Taksasix[$key][$key1]['jumlah_pokok'] = $sum_pokok;
                $Taksasix[$key][$key1]['jumlah_janjang'] = $sum_janjang;
                $Taksasix[$key][$key1]['pemanen'] = $pemanen;
                $Taksasix[$key][$key1]['akp'] = $akp;
                $Taksasix[$key][$key1]['taksasix'] = $tak;
                $Taksasix[$key][$key1]['ritase'] = ceil($tak / 6500);
            }
        }

        $takafd = array();

        foreach ($Taksasix as $key => $value) {
            $luas = 0;
            $sum_sph = 0;
            $jumlah_path = 0;
            $sum_bjr = 0;
            $jumlah_janjang = 0;
            $jumlah_pokok = 0;
            $pemanen = 0;
            $tak = 0;
            foreach ($value as $key1 => $value1) {
                // dd($value1);
                $luas += $value1['luas'];
                $sum_sph += $value1['sph'];
                $jumlah_path += $value1['jumlah_path'];
                $sum_bjr += $value1['bjr'];
                $jumlah_pokok += $value1['jumlah_pokok'];
                $jumlah_janjang += $value1['jumlah_janjang'];
                $pemanen += $value1['pemanen'];
            } # code...
            $akp = round(($jumlah_janjang / $jumlah_pokok) * 100, 2);
            $sum_sph = round($sum_sph / count($value), 2);
            $sum_bjr = round($sum_bjr / count($value), 2);
            $tak = round(($akp * $luas * $sum_bjr * $sum_sph) / 100, 1);
            $takafd[$key]['luas'] = $luas;
            $takafd[$key]['jumlah_path'] = $jumlah_path;
            $takafd[$key]['sph'] = $sum_sph;
            $takafd[$key]['bjr'] = $sum_bjr;
            $takafd[$key]['jumlah_pokok'] = $jumlah_pokok;
            $takafd[$key]['jumlah_janjang'] = $jumlah_janjang;
            $takafd[$key]['akp'] = $akp;
            $takafd[$key]['taksasi'] = $tak;
            $takafd[$key]['ritase'] = ceil($tak / 6500);
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
        $tak = 0;
        foreach ($takafd as $key => $value) {
            $luas += $value['luas'];
            $sum_sph += $value['sph'];
            $jumlah_path += $value['jumlah_path'];
            $sum_bjr += $value['bjr'];
            $jumlah_pokok += $value['jumlah_pokok'];
            $jumlah_janjang += $value['jumlah_janjang'];
            $pemanen += $value['pemanenx'];
        }
        $akp = round(($jumlah_janjang / $jumlah_pokok) * 100, 2);
        $sum_sph = round($sum_sph / count($takafd), 2);
        $sum_bjr = round($sum_bjr / count($takafd), 2);
        $tak = round(($akp * $luas * $sum_bjr * $sum_sph) / 100, 1);
        $takest['luas'] = $luas;
        $takest['jumlah_path'] = $jumlah_path;
        $takest['path'] = count($takafd);
        $takest['sph'] = $sum_sph;
        $takest['bjr'] = $sum_bjr;
        $takest['jumlah_pokok'] = $jumlah_pokok;
        $takest['jumlah_janjang'] = $jumlah_janjang;
        $takest['akp'] = $akp;
        $takest['taksasi'] = $tak;
        $takest['ritase'] = ceil($tak / 6500);
        $takest['pemanenx'] = $pemanen;
        // dd($takafd, $takest);
        $hitungTak = array();

        foreach ($Taksasi as $key => $value) {
            $luasha = 0;
            $blokCheck = ''; // Variable to store the previous 'blok' value
            $pkok_sample = 0;
            $jumlah_janjang = 0;
            foreach ($value as $key1 => $value1) {
                $ffbkiri = $value1['ffbkiri'];
                $ffbkanan = $value1['ffb_kanan'];

                // Remove non-numeric characters and '-' from ffbkiri
                $ffbkiriCleaned = preg_replace('/[^0-9]/', '', $ffbkiri);
                // dd($ffbkiri, $ffbkiriCleaned);
                // Remove non-numeric characters and '-' from ffb_kanan
                $ffbkananCleaned = preg_replace('/[^0-9]/', '', $ffbkanan);

                $pk_kiri  = strlen($ffbkiriCleaned);
                $pk_kanan  = strlen($ffbkananCleaned);

                $jm_pokok = $pk_kiri + $pk_kanan;
                // Sum the remaining numeric values in ffbkiri
                $sumKiri = array_sum(str_split($ffbkiriCleaned));

                // Sum the remaining numeric values in ffb_kanan
                $sumKanan = array_sum(str_split($ffbkananCleaned));


                $pkok_sample += $jm_pokok;
                $jumlah_janjang += $value1['jumlah_janjang'];

                // Update the $blokCheck variable for the next iteration
                $blokCheck = $value1['blok'];

                $hitungTak[$key][$key1]['total_kiri'] = $sumKiri;
                $hitungTak[$key][$key1]['total_kanan'] = $sumKanan;
                $hitungTak[$key][$key1]['kiri'] = $value1['br_kiri'];
                $hitungTak[$key][$key1]['kanan'] = $value1['br_kanan'];
                $hitungTak[$key][$key1]['sph'] = $value1['sph'];
                $hitungTak[$key][$key1]['blok'] = $value1['blok'];
                $hitungTak[$key][$key1]['afdeling'] = $value1['afdeling'];
                $hitungTak[$key][$key1]['lokasi_kerja'] = $value1['lokasi_kerja'];
                $hitungTak[$key][$key1]['name'] = $value1['name'];
                $hitungTak[$key][$key1]['luas'] = $value1['luas'];
                $hitungTak[$key][$key1]['bjr'] = $value1['bjr'];

                $hitungTak[$key][$key1]['jumlah_pokok'] = $jm_pokok;
                $hitungTak[$key][$key1]['jumlah_janjang'] = $value1['jumlah_janjang'];
                $hitungTak[$key][$key1]['jumlah_path'] = 1;
                $hitungTak[$key][$key1]['pkok_kiri'] = $pk_kiri;
                $hitungTak[$key][$key1]['pkok_kanan'] = $pk_kanan;
            }
            foreach ($takafd as $keyx => $valuex) if ($key == $keyx) {
                // dd($valuex);
                $sph = $valuex['sph'];
                $bjr = $valuex['bjr'];
                $luasha = $valuex['luas'];
                $jumlah_janjangx = $valuex['jumlah_janjang'];
            }
            $hitungTak[$key]['luas_ha'] = $luasha;
            $hitungTak[$key]['bjr'] = $bjr;
            $hitungTak[$key]['sph'] = $sph;
            $hitungTak[$key]['jumlah_pokok'] = $pkok_sample;
            $hitungTak[$key]['jumlah_janjang'] = $jumlah_janjangx;
            $hitungTak[$key]['jumlah_path'] = count($value);
        }

        // dd($hitungTak);

        $rom = DB::connection('mysql2')->table('estate')
            ->select('estate.*')
            ->where('est', $estate_input)
            ->pluck('wil');

        function convertToRoman($number)
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
        $romanNumeral = convertToRoman($rom[0]);

        // dd($hitungTak, $takafd);
        $pdf = pdf::loadview('taksasi.pdfqctaksasi', ['est' => $estate_input, 'tgl' => $tgl, 'data' => $arr, 'dataAfd' => $dataAfd, 'rekap' => $arrEstate, 'namaEstate' => strtoupper($queryEstate->nama), 'wil' => $romanNumeral, 'today' => $todayFormatted, 'besok' => $besokFormatted, 'taksasi' => $hitungTak]);
        $customPaper = array(360, 360, 360, 360);
        $pdf->set_paper('A3', 'potrait');

        $filename = 'QC-Taksasi.pdf';
        return $pdf->stream($filename);
    }


    public function verifikasiDataTaksasi(Request $request)
    {

        $query = DB::connection('mysql2')->table('taksasi')->find($request->id);

        if ($query) {

            $verifikasiValue = $request->action === 'verifikasi' ? 1 : 99;

            // Update the record
            $updateSuccessful = DB::connection('mysql2')->table('taksasi')
                ->where('id', $request->id)
                ->update([
                    'status_verifikasi' => $verifikasiValue,
                ]);

            if ($updateSuccessful) {
                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false, 'message' => 'Update failed.']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
        }
    }

    public function editDataTaksasi(Request $request)
    {
        // Validate the request data if necessary
        $validatedData = $request->validate([
            'id' => 'required|integer',
            'lokasi_kerja' => 'required|string|max:255',
            'afdeling' => 'required|string|max:255',
            'blok' => 'required|string|max:255',
            'luas' => 'required|numeric',
            'sph' => 'required|numeric',
            'bjr_sensus' => 'required|numeric',
            'jumlah_pokok' => 'required|numeric',
            'jumlah_janjang' => 'required|numeric',
            'akp' => 'required|numeric',
            'taksasi' => 'required|numeric',
            'output' => 'required|numeric',
            'pokok_produktif' => 'required|numeric',
        ]);

        // Find the existing record by ID
        // Update the record directly using the DB facade
        $updateSuccessful = DB::connection('mysql2')->table('taksasi')
            ->where('id', $validatedData['id'])
            ->update([
                'lokasi_kerja' => $validatedData['lokasi_kerja'],
                'afdeling' => $validatedData['afdeling'],
                'blok' => $validatedData['blok'],
                'luas' => $validatedData['luas'],
                'sph' => $validatedData['sph'],
                'bjr_sensus' => $validatedData['bjr_sensus'],
                'jumlah_pokok' => $validatedData['jumlah_pokok'],
                'jumlah_janjang' => $validatedData['jumlah_janjang'],
                'akp' => $validatedData['akp'],
                'taksasi' => $validatedData['taksasi'],
                'output' => $validatedData['output'],
                'pokok_produktif' => $validatedData['pokok_produktif'],
            ]);

        if ($updateSuccessful) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'message' => 'Update failed.']);
        }
    }
}
