<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Kategori;
use App\Models\Barang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Validation\ValidatesRequests;

class KategoriController extends Controller
{
    use ValidatesRequests;

    public function index()
    {
        $rsetKategori = Kategori::all();

        foreach ($rsetKategori as $kategori) {
            $kategori->kategori_deskripsi = DB::selectOne('SELECT ketKategori(?) as kategori_deskripsi', [$kategori->kategori])->kategori_deskripsi;
        }

        return view('kategori.index', compact('rsetKategori'));
    }

    public function create()
    {
        $aKategori = array(
            'blank' => 'Pilih Kategori',
            'M' => 'Barang Modal',
            'A' => 'Alat',
            'BHP' => 'Bahan Habis Pakai',
            'BTHP' => 'Bahan Tidak Habis Pakai'
        );

        return view('kategori.create', compact('aKategori'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'deskripsi' => 'required',
            'kategori' => 'required|in:M,A,BHP,BTHP',
        ]);

        Kategori::create([
            'deskripsi' => $request->deskripsi,
            'kategori' => $request->kategori,
        ]);

        return redirect()->route('kategori.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    public function show(string $id)
    {
        if (DB::table('barang')->where('kategori_id', $id)->exists()) {
            $rsetKategori = Kategori::find($id);
        } else {
            $rsetKategori = Kategori::find($id);
        }

        $rsetKategori->kategori_deskripsi = DB::selectOne('SELECT ketKategori(?) as kategori_deskripsi', [$rsetKategori->kategori])->kategori_deskripsi;

        return view('kategori.show', compact('rsetKategori'));
    }

    public function edit(string $id)
    {
        $aKategori = array(
            'blank' => 'Pilih Kategori',
            'M' => 'Barang Modal',
            'A' => 'Alat',
            'BHP' => 'Bahan Habis Pakai',
            'BTHP' => 'Bahan Tidak Habis Pakai'
        );

        $rsetKategori = Kategori::find($id);
        return view('kategori.edit', compact('rsetKategori', 'aKategori'));
    }

    public function update(Request $request, string $id)
    {
        $this->validate($request, [
            'deskripsi' => 'required',
            'kategori' => 'required|in:M,A,BHP,BTHP',
        ]);

        $rsetKategori = Kategori::find($id);

        $rsetKategori->update([
            'deskripsi' => $request->deskripsi,
            'kategori' => $request->kategori
        ]);

        return redirect()->route('kategori.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    public function destroy(string $id)
    {
        if (DB::table('barang')->where('kategori_id', $id)->exists()) {
            return redirect()->route('kategori.index')->with(['Gagal' => 'Data Gagal Dihapus!']);
        } else {
            $rsetKategori = Kategori::find($id);
            $rsetKategori->delete();
            return redirect()->route('kategori.index')->with(['success' => 'Data Berhasil Dihapus!']);
        }
    }

    // Metode API
    public function getAPIKategori()
    {
        $rsetKategori = Kategori::all();
        $data = array("data" => $rsetKategori);
        return response()->json($data);
    }

    public function getAPIKategorisatu($id)
    {
        $rsetKategori = Kategori::find($id);
        $data = array("data" => $rsetKategori);
        return response()->json($data);
    }

    public function updateAPIKategori(Request $request, $kategori_id)
    {
        $kategori = Kategori::find($kategori_id);

        if (null == $kategori) {
            return response()->json(['status' => "kategori tidak ditemukan"]);
        }

        $kategori->deskripsi = $request->deskripsi;
        $kategori->kategori = $request->kategori;
        $kategori->save();

        return response()->json(["status" => "kategori berhasil diubah"]);
    }

    public function showAPIKategori(Request $request)
    {
        $kategori = Kategori::all();
        return response()->json($kategori);
    }

    public function createAPIKategori(Request $request)
    {
        $request->validate([
            'deskripsi' => 'required|string|max:100',
            'kategori' => 'required|in:M,A,BHP,BTHP',
        ]);

        $kat = Kategori::create([
            'deskripsi' => $request->deskripsi,
            'kategori' => $request->kategori,
        ]);

        return response()->json(["status" => "data berhasil dibuat"]);
    }

    public function deleteAPIKategori($kategori_id)
    {
        $del_kategori = Kategori::findOrFail($kategori_id);
        $del_kategori->delete();

        return response()->json(["status" => "data berhasil dihapus"]);
    }
}
