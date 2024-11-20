<?php

namespace App\Http\Controllers;

use App\Models\Pupuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Yajra\Datatables\Datatables;

class PupukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Pupuk::all();

        return view('pupuk.index', ['data' => $data]);
        // return Datatables::of($data)
        //     ->addIndexColumn()
        //     ->addColumn('action', function ($row) {
        //         $actionBtn = '<a href="javascript:void(0)" class="edit btn btn-success btn-sm">Edit</a> <a href="javascript:void(0)" class="delete btn btn-danger btn-sm">Delete</a>';
        //         return $actionBtn;
        //     })
        //     ->rawColumns(['action'])
        //     ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pupuk.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
        ]);
        $newData = Pupuk::create($request->all());

        $response = Http::asForm()->post('http://localhost/pupukVersion.php', [
            'id_pupuk' => $newData->id,
            'update' => 1,
        ]);

        return redirect()->route('pupuk.index')->with('success', 'Pupuk Berhasil di Input');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Pupuk $pupuk)
    {
        //
        return view('pupuk.edit', compact('pupuk'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Pupuk $pupuk)
    {
        $request->validate([
            'nama' => 'required',
        ]);

        $pupuk->update($request->all());

        // dd($pupuk);
        $response = Http::asForm()->post('http://localhost/pupukVersion.php', [
            'update' => 1,
        ]);

        return redirect()->route('pupuk.index')->with('success', 'Pupuk Berhasil di Update');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Pupuk $pupuk)
    {

        $response = Http::asForm()->post('http://localhost/pupukVersion.php', [
            'update' => 1,
        ]);

        $pupuk->delete();

        return redirect()->route('pupuk.index')->with('succes', 'Pupuk ' . $pupuk->nama . ' Berhasil di Hapus');
    }
}
