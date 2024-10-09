<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Dosen;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Admin_PresensiController extends Controller
{
    public function index(Request $request)
    {
        $id_user = $request->session()->get('id_user');
        $dosen = Dosen::where('id_user', $id_user)->first();

        $months = Presensi::selectRaw('DATE_FORMAT(tgl_presensi, "%Y-%m") as month')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        $selectedMonth = $request->input('month', $months->first()->month ?? null);

        $presensi = Presensi::join('dosen', 'dosen.nip', '=', 'presensi.nip')
            ->select('presensi.*', 'dosen.nama_lengkap') // Pilih kolom yang ingin ditampilkan
            ->whereRaw('DATE_FORMAT(tgl_presensi, "%Y-%m") = ?', [$selectedMonth])
            ->get();

        return view('admin.presensi', [
            'title' => 'Absensi',
            'active' => 'absensi',
            'role' => $request->session()->get('role'),
            'presensi' => $presensi,
            'dosen' => $dosen,
            'months' => $months,
            'selectedMonth' => $selectedMonth,
        ]);
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $presensi = Presensi::where('id', $id)->firstOrFail();
            $presensi->delete();

            DB::commit();

            return redirect()->back()->with('toast', ['message' => 'Presensi berhasil dihapus!', 'type' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('toast', ['message' => 'Terjadi kesalahan. Silakan coba lagi nanti.', 'type' => 'error']);
        }
    }
}
