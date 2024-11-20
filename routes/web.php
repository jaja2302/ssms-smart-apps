<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardNewController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PupukController;
use App\Http\Controllers\MapsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', [App\Http\Controllers\HomeController::class, 'index_login'])->name('login');
Route::get('/register', [App\Http\Controllers\HomeController::class, 'index_registration'])->name('register');
// Auth::routes();

Route::post('/auth_login', [App\Http\Controllers\HomeController::class, 'auth_login'])->name('auth_login');
Route::post('/auth_registration', [App\Http\Controllers\HomeController::class, 'auth_registration'])->name('auth_registration');
// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('logout', [HomeController::class, 'logout'])->name('logout');

Route::group(['middleware' => 'auth'], function () {
    Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::post('editDataTaksasi', [DashboardController::class, 'editDataTaksasi'])->name('editDataTaksasi');
    Route::post('verifikasiDataTaksasi', [DashboardController::class, 'verifikasiDataTaksasi'])->name('verifikasiDataTaksasi');
    Route::get('dashboard_taksasi', [DashboardController::class, 'ds_taksasi'])->name('dash_est');
    Route::get('dashboard_taksasi_afdeling', [DashboardController::class, 'ds_taksasi_afdeling'])->name('dash_afd');
    Route::get('history_taksasi', [DashboardController::class, 'history_taksasi'])->name('hish_tak');
    Route::get('tak_history', [DashboardController::class, 'history_taksasi'])->name('tak_history');
    Route::post('getDataTakEst15Days', [DashboardController::class, 'getTakEst15Days'])->name('getDataTakEst15Days');
    Route::post('getNameEstate', [DashboardController::class, 'getNameEstate'])->name('getNameEstate');
    Route::post('getNameAfdeling', [DashboardController::class, 'getNameAfdeling'])->name('getNameAfdeling');
    Route::post('getNameWilayah', [DashboardController::class, 'getNameWilayah'])->name('getNameWilayah');
    Route::post('plotEstate', [DashboardController::class, 'plotEstate'])->name('plotEstate');
    Route::post('plotBlok', [DashboardController::class, 'plotBlok'])->name('plotBlok');
    Route::post('plotLineTaksasi', [DashboardController::class, 'plotLineTaksasi'])->name('plotLineTaksasi');
    Route::post('plotMarkerMan', [DashboardController::class, 'plotMarkerMan'])->name('plotMarkerMan');
    Route::post('plotUserTaksasi', [DashboardController::class, 'plotUserTaksasi'])->name('plotUserTaksasi');
    Route::post('getDataTable', [DashboardController::class, 'getDataTable'])->name('getDataTable');
    Route::post('getListEstate', [DashboardController::class, 'getListEstate'])->name('getListEstate');
    Route::get('tableCoba', [DashboardController::class, 'tableCoba'])->name('tableCoba');
    Route::post('getDataAfdeling', [DashboardController::class, 'getDataAfd'])->name('getDataAfdeling');
    Route::post('getLoadRegional', [DashboardController::class, 'getDataRegional'])->name('getLoadRegional');
    Route::get('dashboard_pemupukan', [DashboardController::class, 'ds_pemupukan'])->name('dash_pemupukan');
    Route::get('detail_pemupukan/{est}/{afd}/{tanggal}', [DashboardController::class, 'detail_pemupukan'])->name('detail_pemupukan');
    Route::get('rekom_aplikasi/{est}/{afd}/{rot}/{sm}/{tanggal}', [DashboardController::class, 'rekom_aplikasi'])->name('rekom_aplikasi');
    Route::post('getListEstateTerpupuk', [DashboardController::class, 'getListEstateTerpupuk'])->name('getListEstateTerpupuk');
    Route::post('getDataPemupukan', [DashboardController::class, 'getDataPemupukan'])->name('getDataPemupukan');
    Route::post('lastDataPemupukan', [DashboardController::class, 'lastDataPemupukan'])->name('lastDataPemupukan');
    Route::get('/data', [DashboardController::class, 'ds_pemupukan'])->name('data');

    Route::resource('pupuk', PupukController::class);

    Route::get('/maps', [MapsController::class, 'dashboard'])->name('dashboard');

    Route::get('/plotBlok', [MapsController::class, 'getPlotBlok'])->name('getPlotBlok');

    Route::get('/getPdfqc/{est}/{date}', [DashboardController::class, 'getPdfqc'])->name('getPdfqc');

    Route::get('/mapsTest', [MapsController::class, 'mapsTest'])->name('mapsTest');
    Route::get('/mapsestatePlot', [MapsController::class, 'mapsestatePlot'])->name('mapsestatePlot');
    Route::post('/inputquery', [MapsController::class, 'getData'])->name('inputquery');

    Route::get('/get-data-regional-wilayah', [DashboardNewController::class, 'getAllDataRegionalWilayah'])->name('get-data-regional-wilayah');
    Route::get('/get-data-estate', [DashboardNewController::class, 'getAllDataEstate'])->name('get-data-estate');
    Route::get('/get-data-realisasi-taksasi-per-regional', [DashboardNewController::class, 'getDataRealisasiTaksasi'])->name('get-data-realisasi-taksasi-per-regional');
    Route::post('/import-realisasi-taksasi', [DashboardNewController::class, 'importExcelRealisasiTaksasi'])->name('import-realisasi-taksasi');
});

Route::get('/dashboard_vehicle_management', function () {
    return view('vehicle-management');
});
Route::get('/dashboard_field_inspection', function () {
    return view('field-inspection');
});
