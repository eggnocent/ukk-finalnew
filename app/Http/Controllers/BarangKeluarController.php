<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BarangKeluar;
use App\Models\Barang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Validation\ValidatesRequests;

class BarangKeluarController extends Controller
{
    use ValidatesRequests;

    public function index()
    {
        $barangkeluars = BarangKeluar::with('barang')->paginate(10);

        return view('barangkeluar.index', compact('barangkeluars'));
    }

    public function create()
    {
        $barangs = Barang::all();

        return view('barangkeluar.create', compact('barangs'));
    }

    public function store(Request $request)
{
    $this->validate($request, [
        'tgl_keluar' => 'required|date',
        'qty_keluar' => 'required|integer|min:1',
        'barang_id' => 'required|exists:barang,id',
    ]);

    $barang = Barang::findOrFail($request->barang_id);

    // Validasi jika tanggal keluar lebih awal dari tanggal barang masuk
    if ($request->tgl_keluar < $barang->created_at) {
        return redirect()->back()->withErrors(['tgl_keluar' => 'Tanggal keluar tidak dapat lebih awal dari tanggal barang masuk.'])->withInput();
    }

    // Periksa ketersediaan stok
    if ($request->qty_keluar > $barang->stok) {
        return redirect()->back()->withErrors(['qty_keluar' => 'Jumlah keluar melebihi stok yang tersedia'])->withInput();
    }

    // Simpan data pengeluaran barang jika validasi berhasil
    BarangKeluar::create($request->all());

    // Kurangi stok barang yang keluar dari stok yang tersedia
    $barang->stok -= $request->qty_keluar;
    $barang->save();

    return redirect()->route('barangkeluar.index')->with(['success' => 'Data Barang Keluar Berhasil Disimpan!']);
}


    public function show($id)
    {
        $barangkeluar = BarangKeluar::findOrFail($id);

        return view('barangkeluar.show', compact('barangkeluar'));
    }

    public function edit($id)
    {
        $barangkeluar = BarangKeluar::findOrFail($id);
        $barangs = Barang::all();

        return view('barangkeluar.edit', compact('barangkeluar', 'barangs'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'tgl_keluar' => 'required|date',
            'qty_keluar' => 'required|integer|min:1',
            'barang_id' => 'required|exists:barang,id',
        ]);

        $barangkeluar = BarangKeluar::findOrFail($id);
        $barang = Barang::findOrFail($request->barang_id);
    
        // Periksa apakah jumlah keluar melebihi stok yang tersedia
        if ($request->qty_keluar > $barang->stok + $barangkeluar->qty_keluar) {
            return redirect()->back()->withErrors(['qty_keluar' => 'Jumlah keluar melebihi stok yang tersedia'])->withInput();
        }
    
        // Perbarui data pengeluaran barang
        $barangkeluar->update($request->all());
    
        // Perbarui stok barang
        $barang->stok += $barangkeluar->qty_keluar; // Kembalikan stok yang sebelumnya dikurangkan
        $barang->stok -= $request->qty_keluar; // Kurangi stok dengan jumlah baru yang keluar
        $barang->save();

        return redirect()->route('barangkeluar.index')->with(['success' => 'Data Barang Keluar Berhasil Disimpan!']);        
    }

    public function destroy($id)
{
    // Temukan data barang keluar
    $barangKeluar = BarangKeluar::findOrFail($id);
    $barangMasuk = $barangKeluar->barang->created_at;

    // Periksa apakah tanggal barang keluar kurang dari tanggal barang masuk
    if ($barangKeluar->tgl_keluar < $barangMasuk) {
        return redirect()->route('barangkeluar.index')->with(['error' => 'Tidak dapat menghapus barang keluar sebelum tanggal barang masuk.']);
    }

    // Hapus data barang keluar jika tanggal keluar lebih besar atau sama dengan tanggal barang masuk
    $barangKeluar->delete();

    return redirect()->route('barangkeluar.index')->with(['success' => 'Data Barang Keluar Berhasil Dihapus!']);
}
    
}