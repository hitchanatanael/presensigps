<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log; // Tambahkan logging

class ProfilController extends Controller
{
    public function editprofil()
    {
        $nip = Auth::guard('dosen')->user()->nip;
        $dosen = DB::table('dosen')->where('nip', $nip)->first();

        return view('presensi.editprofil', compact('dosen'));
    }

    public function updateprofil(Request $request)
    {
        try {
            $nip = Auth::guard('dosen')->user()->nip;
            $nama_lengkap = $request->nama_lengkap;
            $no_hp = $request->no_hp;
            $password = Hash::make($request->password);
            $dosen = DB::table('dosen')->where('nip', $nip)->first();

            if ($request->hasFile('foto')) {
                Log::info("User $nip is uploading a new photo.");
                $foto = $nip . "." . $request->file('foto')->getClientOriginalExtension();
                $folderPath = "public/uploads/dosen/";
                $request->file('foto')->storeAs($folderPath, $foto);
                Log::info("Photo saved successfully at $folderPath$foto.");
            } else {
                Log::info("No photo uploaded for user $nip, using existing photo.");
                $foto = $dosen->foto;
            }

            if (empty($request->password)) {
                $data = [
                    'nama_lengkap' => $nama_lengkap,
                    'no_hp' => $no_hp,
                    'foto' => $foto,
                ];
            } else {
                $data = [
                    'nama_lengkap' => $nama_lengkap,
                    'no_hp' => $no_hp,
                    'password' => $password,
                    'foto' => $foto
                ];
            }

            $update = DB::table('dosen')->where('nip', $nip)->update($data);
            if ($update) {
                Log::info("User $nip profile updated successfully.");
                return Redirect::back()->with(['success' => 'Data Berhasil Di Update']);
            } else {
                Log::error("User $nip profile failed to update.");
                return Redirect::back()->with(['error' => 'Data Gagal Di Update']);
            }
        } catch (\Exception $e) {
            Log::error("An error occurred while updating profile for user $nip: " . $e->getMessage());
            return Redirect::back()->with(['error' => 'Terjadi kesalahan saat mengupdate data.']);
        }
    }
}
