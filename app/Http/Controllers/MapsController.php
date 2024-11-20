<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use App\Models\data_map;

class MapsController extends Controller
{
    public function dashboard()
    {
        $tahun = DB::connection('mysql2')
            ->table('dt_pokok_kuning')
            ->select('tahun_tanam')
            ->distinct()
            ->pluck('tahun_tanam')
            ->toArray();

        $estate = DB::connection('mysql2')
            ->table('dt_pokok_kuning')
            ->select('est')
            ->distinct()
            ->pluck('est')
            ->toArray();


        // Pass the data as an associative array
        return view('maps.index', [
            'tahun' => $tahun,
            'estate' => $estate
        ]);
    }



    public function getPlotBlok(Request $request)
    {

        $est = $request->get('estData');
        $tahun = $request->get('tahunData');
        $afd_s = $request->get('afd');
        // dd($afd);
        $estateQuery = DB::connection('mysql2')->Table('estate')
            ->join('afdeling', 'afdeling.estate', 'estate.id')
            ->where('est', $est)
            ->get();

        $listIdAfd = array();
        foreach ($estateQuery as $key => $value) {
            $listIdAfd[] = $value->id;
        }
        $blokEstate =  DB::connection('mysql2')->Table('blok')->whereIn('afdeling', $listIdAfd)->groupBy('nama')->pluck('nama', 'id');
        $blokEstateFix[$est] = json_decode($blokEstate, true);

        // dd($blokEstateFix);
        $blokLatLn = array();
        foreach ($blokEstateFix as $key => $value) {
            $inc = 0;
            foreach ($value as $key2 => $data) {
                $nilai = 0;
                $kategori = 'x';



                $query = DB::connection('mysql2')->table('blok')
                    ->select('blok.*')
                    ->whereIn('blok.afdeling', $listIdAfd)
                    ->get();

                $latln = '';
                $queryAfd  = '';
                foreach ($query as $key3 => $val) {
                    if ($val->nama == $data) {
                        $latln .= '[' . $val->lon . ',' . $val->lat . '],';
                        $afd =  $val->afdeling;
                        $queryAfd = DB::connection('mysql2')->table('afdeling')
                            ->select('afdeling.*')
                            ->where('id', $afd)
                            ->first();

                        // $queryEst = DB::connection('mysql2')->table('afdeling')
                        // ->select('afdeling.*')
                        // ->whereIn('afdeling.id', $afd)
                        // ->pluck('nama');


                    }
                }

                $blokLatLn[$inc]['blok'] = $data;
                $blokLatLn[$inc]['estate'] = $est;
                $blokLatLn[$inc]['latln'] = rtrim($latln, ',');
                $blokLatLn[$inc]['nilai'] = $nilai;
                $blokLatLn[$inc]['afdeling'] = $queryAfd->nama;
                $blokLatLn[$inc]['kategori'] = $kategori;

                $inc++;
            }
        }
        // dd($blokLatLn);

        $queryAfd = DB::connection('mysql2')->table('afdeling')
            ->select(
                'afdeling.id',
                'afdeling.nama',
                'estate.est'
            ) //buat mengambil data di estate db dan willayah db
            ->join('estate', 'estate.id', '=', 'afdeling.estate') //kemudian di join untuk mengambil est perwilayah
            ->where('est', '=', $est)
            ->get();
        $queryAfd = json_decode($queryAfd, true);


        $id = [];
        foreach ($queryAfd as $key => $value) {
            $id[] = $value['id'];
        }


        // dd($id);


        $querryBlok = DB::connection('mysql2')->table('blok')
            ->select('blok.*') // Use 'blok.*' to select all columns from the 'blok' table
            // ->whereIn('afdeling', $id)
            ->where('afdeling', '=', 51)

            ->get()
            ->groupBy(['afdeling', 'nama']);
        $querryBlok = json_decode($querryBlok, true);

        // dd($querryBlok);
        // Now $jsonResult contains the JSON-encoded data
        // dd($querryBlok);
        $plot_estate = array();
        foreach ($querryBlok as $key => $value) {
            foreach ($value as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {


                    $plot_estate[$key][$key1][$key2]['lat'] = $value2['lat'];
                    $plot_estate[$key][$key1][$key2]['lon'] = $value2['lon'];
                } # code...
            }  # code...
        }
        $latLonArray = [];

        foreach ($querryBlok as $afdeling => $namaGroup) {
            foreach ($namaGroup as $nama => $items) {
                foreach ($items as $item) {
                    // Extract lat and lon from each item and add them to the $latLonArray
                    $latLonArray[] = ['lat' => $item['lat'], 'lon' => $item['lon']];
                }
            }
        }
        $queryPlotEst = DB::connection('mysql2')->table('estate_plot')
            ->select("estate_plot.*")
            ->where('est', $est)
            ->get();
        // $queryPlotEst = $queryPlotEst->groupBy(['estate', 'afdeling']);
        $queryPlotEst = json_decode($queryPlotEst, true);

        // dd($queryPlotEst, $latLonArray);
        $convertedCoords = [];
        foreach ($latLonArray as $coord) {
            $convertedCoords[] = [$coord['lat'], $coord['lon']];
        }


        // dd($afd);
        $queryTrans = DB::connection('mysql2')->table("dt_pokok_kuning")
            ->select("dt_pokok_kuning.*", "estate.wil")
            ->join('estate', 'estate.est', '=', 'dt_pokok_kuning.est')
            ->where('dt_pokok_kuning.est', $est)
            ->where('dt_pokok_kuning.afd', $afd_s)
            ->where('tahun_tanam', 'like', '%' . $tahun . '%')
            ->get();
        $queryTrans = json_decode($queryTrans, true);

        $groupedTrans = array_reduce($queryTrans, function ($carry, $item) {
            $carry[$item['blok']][] = $item;
            return $carry;
        }, []);

        // dd($groupedTrans);


        $trans_plot = [];
        foreach ($groupedTrans as $blok => $coords) {
            foreach ($coords as $coord) {
                $afd = ($coord['est'] == 'MRE' && $coord['afd'] == 'OC') ? 'OD' : $coord['afd'];

                $trans_plot[$blok][] = [
                    'blok' => $blok,
                    'lat' => $coord['lat'],
                    'lon' => $coord['lon'],
                    'tahun_tanam' => $coord['tahun_tanam'],
                    'keterangan' => $coord['keterangan'],
                    'est' => $coord['est'],
                    'afd' => $afd
                ];
            }
        }

        // dd($trans_plot);







        $plot['blok'] = $blokLatLn;
        $plot['coords'] = $convertedCoords;
        $plot['pokok_data'] = $trans_plot;


        // dd($plotBlokAll);
        echo json_encode($plot);
    }

    public function mapsTest(Request $request)
    {


        $queryEstate = DB::connection('mysql2')->table('estate')
            ->select('estate.*')
            ->join('wil', 'wil.id', '=', 'estate.wil')
            ->where('wil.regional', 4)
            ->whereNotIn('estate.est', ['CWS1', 'CWS2', 'CWS3'])
            ->where('estate.est', '!=', 'PLASMA')
            ->pluck('est');

        // dd($queryEstate);


        $queryEstate = json_decode($queryEstate, true);


        $afd = DB::connection('mysql2')->table('afdeling')
            ->select('afdeling.*', 'estate.est')
            ->join('estate', 'estate.id', '=', 'afdeling.estate')
            ->get('est');

        $lastAfdPlot = DB::connection('mysql2')
            ->table('afdeling_plot')
            ->orderBy('id', 'desc')
            ->first(); // Use first() to retrieve only one row



        // dd($afd_plot);

        $lastest = $lastAfdPlot->est;
        $lastafd = $lastAfdPlot->afd;

        // dd($lastafd, $lastest);

        $afd = json_decode($afd, true);
        return view('maps.testingestate', [
            'afd' => $afd,
            'lastest' => $lastest,
            'lastafd' => $lastafd,
            'estate' => $queryEstate
        ]);
    }

    public function mapsestatePlot(Request $request)
    {
        // dd($plotAfd);

        $estate = $request->get('estData');
        $afdeling = $request->get('afdling');

        $plot_kuning = DB::connection('mysql2')
            ->table('deficiency_tracker')
            ->select('deficiency_tracker.*')
            ->join('afdeling', 'afdeling.nama', '=', 'deficiency_tracker.afd')
            ->where('deficiency_tracker.est', '=', $estate)
            ->where('afdeling.id', '=', $afdeling)
            // ->whereNotIn('id', [353])
            ->orderBy('blok', 'desc') // Sort by 'id' column in descending order
            ->get();

        $plot_kuning = $plot_kuning->groupBy(['blok']);
        $plot_kuning = json_decode($plot_kuning, true);
        // dd($plot_kuning['G03']);

        // dd($plot_kuning);

        // dd($estate);
        foreach ($plot_kuning as $key => $value) {
            foreach ($value as $key2 => $value3) {
                // dd($value3);
                $afd = $value3['afd'];
                if (strlen($key) === 3 && $estate !== 'NBE' && $estate !== 'MRE') {
                    $newKey = substr($key, 0, 1) . '0' . substr($key, 1);
                    unset($plot_kuning[$key][$key2]);
                    $plot_kuning[$newKey][$key2] = $value3;
                } elseif (strpos($key, 'CBI') !== false && $estate !== 'BKE') {
                    $newKey = str_replace("-CBI", "", $key);
                    $newKey = substr($newKey, 0, 1) . '0' . substr($newKey, 1);
                    unset($plot_kuning[$key][$key2]);
                    $plot_kuning[$newKey][$key2] = $value3;
                } elseif (strpos($key, 'T-') !== false  && $estate !== 'MRE') {
                    $newKey = str_replace("T-", "", $key);
                    unset($plot_kuning[$key][$key2]);
                    $plot_kuning[$newKey][$key2] = $value3;
                } elseif (strpos($key, 'P-') !== false  && $estate !== 'MRE' && $estate !== 'MLE') {
                    $newKey = str_replace("P-", "", $key);
                    unset($plot_kuning[$key][$key2]);
                    $plot_kuning[$newKey][$key2] = $value3;
                } elseif (strpos($key, 'CBI') !== false) {
                    $newKey = str_replace("-CBI", "", $key);
                    // $newKey = substr($newKey, 0, 1) . '0' . substr($newKey, 1);
                    unset($plot_kuning[$key][$key2]);
                    $plot_kuning[$newKey][$key2] = $value3;
                } elseif (strlen($key) === 3 && $estate == 'NBE' && strpos($key, 'D') !== false && $afd !== 'OA' && $afd !== 'OB') {
                    $newKey = substr($key, 0, 1) . '0' . substr($key, 1);
                    unset($plot_kuning[$key][$key2]);
                    $plot_kuning[$newKey][$key2] = $value3;
                } elseif (strlen($key) === 3 && $estate == 'MRE') {
                    $newKey = substr($key, 0, 1) . '0' . substr($key, 1);
                    unset($plot_kuning[$key][$key2]);
                    $plot_kuning[$newKey][$key2] = $value3;
                } elseif (strpos($key, 'P-P') !== false && $estate == 'MRE') {
                    $newKey = str_replace("-P", "0", $key);
                    unset($plot_kuning[$key][$key]);
                    $plot_kuning[$newKey][$key2] = $value3;
                } elseif (strpos($key, 'P-') !== false && $estate == 'MLE') {
                    $keyx = str_replace("P-", "", $key);
                    $newKey = substr($keyx, 0, 1) . '0' . substr($keyx, 1);
                    unset($plot_kuning[$key][$key2]);
                    $plot_kuning[$newKey][$key2] = $value3;
                } else {
                    $plot_kuning[$key][$key2] = $value3;
                }
            }
        }

        // dd($plot_kuning);
        $filteredArray = [];

        foreach ($plot_kuning as $key => $value) {
            if (!empty($value)) {
                // Add non-empty arrays to the filtered array
                $filteredArray[$key] = $value;
            }
        }

        // dd($filteredArray);
        $new_pk = array();
        foreach ($filteredArray as $key => $value) {
            foreach ($value as $key1 => $value1) {

                if ($value1['jenis_pupuk_id'] != null) {
                    $pupukx = explode('$', $value1['jenis_pupuk_id']);
                    // dd($pupukx);
                    $pupuk = DB::connection('mysql2')
                        ->table('pupuk')
                        ->select('pupuk.*')
                        ->whereIn('pupuk.id', $pupukx) // Pass the array directly to whereIn
                        ->orderBy('id', 'desc')
                        ->pluck('nama');

                    $pupuk = json_decode(json_encode($pupuk), true); // Convert the result to an array
                    $new_ppk = implode("$", $pupuk);
                    $komnt_ppk = implode(" - ", $pupuk);
                } else {
                    $new_ppk = null;
                    $komnt_ppk = null;
                }




                // dd($new_ppk);
                $new_pk[$key][$key1]['pupuk'] = $new_ppk;
                $new_pk[$key][$key1]['ppk_kmn'] = $komnt_ppk;
                $new_pk[$key][$key1]['lat'] = $value1['lat'];
                $new_pk[$key][$key1]['lon'] = $value1['lon'];
                $new_pk[$key][$key1]['blok'] = $key;
                $new_pk[$key][$key1]['kondisi'] = $value1['kondisi'];
                $new_pk[$key][$key1]['status'] = $value1['status'];
                $new_pk[$key][$key1]['foto'] = $value1['foto'];
                $new_pk[$key][$key1]['komentar'] = $value1['komentar'];
                $new_pk[$key][$key1]['id'] = $value1['id'];
                $new_pk[$key][$key1]['afd'] = $value1['afd'];
            }
        }

        // dd($new_pk);

        $datatables = DB::connection('mysql2')
            ->table('deficiency_tracker')
            ->select('deficiency_tracker.*')
            ->join('afdeling', 'afdeling.nama', '=', 'deficiency_tracker.afd')
            ->where('deficiency_tracker.est', '=', $estate)
            ->where('afdeling.id', '=', $afdeling)
            // ->whereNotIn('id', [353])
            ->orderBy('id', 'desc') // Sort by 'id' column in descending order
            ->get();


        $datatables = json_decode($datatables, true);



        // dd($datatables);


        $count = array_reduce($filteredArray, function ($carry, $items) {
            return $carry + count(array_filter($items, function ($item) {
                return $item['status'] !== "Sudah";
            }));
        }, 0);

        $count_sudah = array_reduce($filteredArray, function ($carry, $items) {
            return $carry + count(array_filter($items, function ($item) {
                return $item['status'] === "Sudah";
            }));
        }, 0);

        // $count_sudah = 0;

        if (($count + $count_sudah) !== 0) {
            $percentage_sudah = round(($count_sudah / ($count + $count_sudah)) * 100, 2);
        } else {
            $percentage_sudah = 0; // Set a default value (0 or any other suitable value) when the denominator is zero.
        }



        $drawBlok = DB::connection('mysql2')
            ->table('blok')
            ->select('blok.*', 'estate.est', 'afdeling.nama as afd_nama')
            ->join('afdeling', 'afdeling.id', '=', 'blok.afdeling')
            ->join('estate', 'estate.id', '=', 'afdeling.estate')
            // ->where('estate.est', '=', 'KNE')
            // ->where('afdeling.nama', '=', 'OA')
            ->where('estate.est', '=', $estate)
            ->where('afdeling.id', '=', $afdeling)

            ->orderBy('id', 'desc')
            ->get();

        $drawBlok = $drawBlok->groupBy(['nama']);
        $drawBlok = json_decode($drawBlok, true);

        // dd($drawBlok);
        // dd($drawBlok, $outputArray);

        foreach ($drawBlok as $key => $value) {
            foreach ($value as $key2 => $value3) {
                $afd = $value3['afd_nama'];
                if (strlen($key2) === 5 && $estate == 'PDE' && strpos($key2, 'A') !== false) {
                    $newKey = str_replace("A", "", $key2);
                    unset($drawBlok[$key][$key2]);
                    $drawBlok[$key][$newKey] = $value3;
                } elseif (strlen($key2) === 5 && $estate == 'PDE' && strpos($key2, 'B') !== false) {
                    $newKey = str_replace("B", "", $key2);
                    unset($drawBlok[$key][$key2]);
                    $drawBlok[$key][$newKey] = $value3;
                } elseif (strlen($key2) === 6 && $estate == 'PDE' && strpos($key2, 'T-A') !== false) {
                    $newKey = str_replace("T-", "", $key2);
                    unset($drawBlok[$key][$key2]);
                    $drawBlok[$key][$newKey] = $value3;
                } elseif (strlen($key2) === 6 && $estate == 'PDE' && strpos($key2, 'T-A') !== false) {
                    $newKey = str_replace("T-", "", $key2);
                    unset($drawBlok[$key][$key2]);
                    $drawBlok[$key][$newKey] = $value3;
                } elseif (strpos($key2, 'P-N') !== false && $estate == 'SPE'  && $afd !== 'OD') {
                    $newKey = str_replace("P-", "", $key2);
                    unset($drawBlok[$key][$key2]);
                    $drawBlok[$key][$newKey] = $value3;
                } elseif (strpos($key2, 'P-') !== false && strlen($key2) === 6 && $estate !== 'SPE' && $estate !== 'MLE') {
                    $newKey = str_replace("P-", "", $key2);
                    unset($drawBlok[$key][$key2]);
                    $drawBlok[$key][$newKey] = $value3;
                } elseif (strpos($key2, 'P-') !== false && $estate == 'SPE'  && $afd == 'OD') {
                    $newKey = str_replace("P-", "", $key2);
                    $newKey = str_replace("A", "", $newKey);
                    unset($drawBlok[$key][$key2]);
                    $drawBlok[$key][$newKey] = $value3;
                } elseif (strpos($key2, 'P-') !== false && strlen($key2) === 7 && $estate == 'MLE' && $afd == 'OC') {
                    $kexa = str_replace("P-", "", $key2);
                    $newKey = str_replace("B", "", $kexa);
                    unset($drawBlok[$key][$key2]);
                    $drawBlok[$key][$newKey] = $value3;
                } elseif (strpos($key2, 'P-') !== false && strlen($key2) === 6 && $estate == 'MLE' && $estate !== 'SCE') {
                    $kexa = str_replace("P-", "", $key2);
                    // $newKey = str_replace("B", "", $kexa);
                    unset($drawBlok[$key][$key2]);
                    $drawBlok[$key][$newKey] = $value3;
                } elseif (strlen($key2) === 5 && $estate == 'SCE') {
                    $newKey = str_replace("B", "", $key2);
                    unset($drawBlok[$key][$key2]);
                    $drawBlok[$key][$newKey] = $value3;
                } elseif (strlen($key2) === 3 && in_array($estate, ['BDE', 'KTE', 'MKE', 'PKE', 'BHE', 'BSE', 'BWE', 'GDE'])) {
                    $newKey = substr($key2, 0, 1) . '0' . substr($key2, 1);
                    unset($drawBlok[$key][$key2]);
                    $drawBlok[$key][$newKey] = $value3;
                } else {
                    $drawBlok[$key][$key2] = $value3;
                }
            }
        }

        $filteredBlok = [];

        foreach ($drawBlok as $key => $value) {
            if (!empty($value)) {
                // Add non-empty arrays to the filtered array
                $filteredBlok[$key] = $value;
            }
        }
        // dd($filteredBlok, $filteredArray['G003']);

        $new_blok = array();

        foreach ($filteredBlok as $key => $value) {
            $lat_lon = array(); // Initialize lat_lon as an empty array
            $jumblok = 0; // Initialize jumblok to 0
            $kategori = 'Blue'; // Initialize kategori as 'Blue' by default
            $ket = '-'; // Initialize kategori as 'Blue' by default

            foreach ($value as $key2 => $value2) {

                $verif = 0;
                $statusCount = 0;

                foreach ($filteredArray as $key3 => $value3) {
                    if ($key === $key3) {
                        $jumblok = count($value3);
                        foreach ($value3 as $key4 => $value4) {
                            if (isset($value4['status']) && $value4['status'] == 'Sudah') {
                                $statusCount++;
                            }
                            if (isset($value4['status']) && $value4['status'] == 'Terverifikasi') {
                                $verif++;
                            }
                            // Collect lat and lon values

                        }
                        if (isset($value2['lat']) && isset($value2['lon'])) {
                            $lat = $value2['lat'];
                            $lon = $value2['lon'];
                            $lat_lon[] = $lat . ';' . $lon;
                        }
                    }
                }
            }

            if ($jumblok >= 1000 && $jumblok < 10000) {
                if ($statusCount >= 500) {
                    $kategori = 'Hijau';
                    $ket = '1000 : 500';
                }
            } elseif ($jumblok >= 30 && $jumblok < 100) {
                if ($statusCount >= 20) {
                    $kategori = 'Hijau';
                    $ket = '100 : 20';
                }
            } elseif ($jumblok >= 10 && $jumblok <= 30) {
                if ($statusCount >= 10) {
                    $kategori = 'Hijau';
                    $ket = '11 : 10';
                }
            } elseif ($jumblok >= 6 && $jumblok < 10) {
                if ($statusCount == 5) {
                    $kategori = 'Hijau';
                    $ket = '10 : 10';
                }
            } elseif ($jumblok > 1 && $jumblok < 6) {
                if ($statusCount >= 5) {
                    $kategori = 'Hijau';
                    $ket = '6 : 5';
                }
            }
            if (empty($lat_lon)) {
                foreach ($value as $item) {
                    if (isset($item['lat']) && isset($item['lon'])) {
                        $lat = $item['lat'];
                        $lon = $item['lon'];
                        $lat_lon[] = $lat . ';' . $lon;
                    }
                }
            }

            // Rest of your code

            $new_blok[$key]['jum_pokok'] = $jumblok;
            $new_blok[$key]['afd_nama'] = $key;
            $new_blok[$key]['Diverif'] = $verif;
            $new_blok[$key]['kategori'] = $kategori;
            $new_blok[$key]['Ket'] = $ket;
            $new_blok[$key]['Ditangani'] = $statusCount;
            $new_blok[$key]['Belum'] = $jumblok - $statusCount;
            $new_blok[$key]['lat_lon'] = $lat_lon;
        }

        // dd($new_blok);


        $arrView['drawBlok'] = $drawBlok;



        $afdeling = DB::connection('mysql2')->table('blok')
            ->select('blok.*')
            ->join('afdeling', 'afdeling.id', '=', 'blok.afdeling')
            ->join('estate', 'estate.id', '=', 'afdeling.estate')
            ->where('estate.est', '=', 'KNE')
            ->where('afdeling.nama', '=', 'OA')
            ->orderBy('id', 'desc')
            ->get();


        $afdeling = $afdeling->groupBy(['nama']);
        $afdeling = json_decode($afdeling, true);

        // dd($afdeling);



        $newnblok = [];
        foreach ($afdeling as $key => $value) {
            # code...
            foreach ($value as $item) {
                if (isset($item['lat']) && isset($item['lon'])) {
                    $lat = $item['lat'];
                    $lon = $item['lon'];
                    $lat_lon[] = $lat . ';' . $lon;
                }
            }
            $newnblok[$key]['lat_lon'] = $lat_lon;
        }

        // dd($newnblok, $new_blok);

        $plot['blok'] = $new_blok;
        // $plot['blok_Pulau'] = $newkey;
        // $plot['blok_Pulau2'] = $newkey2;


        // dd($plotBlokAll);
        echo json_encode($plot);
    }


    public function getData(Request $request)
    {
        // Get the JSON data from the request
        $data = $request->json()->all();


        // The 'coordinates' key should contain your array of coordinates
        $coordinates = $data['coordinates'];


        $est = $request->get('estData');
        $afd = $request->get('afdling');

        // dd($est, $afd);
        foreach ($coordinates as $index => $coordinateSet) {
            $latitude = $coordinateSet[1]; // Access the latitude (y-coordinate)
            $longitude = $coordinateSet[0]; // Access the longitude (x-coordinate)

            // Insert the coordinates into the database using Eloquent or the query builder
            $data = new data_map();
            $data->est = $est;
            $data->afd = $afd;
            $data->lat = $longitude;
            $data->lon = $latitude;
            $data->save();
        }

        return response()->json(['message' => 'Data inserted successfully']);
    }
}
