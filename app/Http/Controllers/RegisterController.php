<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function index()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nip' => 'required',
            'nama_lengkap' => 'required',
            'jabatan' => 'required',
            'no_hp' => 'required',
            'password' => 'required|min:8',
        ], [
            'nip.required' => 'NIP wajib diisi.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'no_hp.required' => 'Nomor handphone wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password harus terdiri dari minimal 8 karakter.',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }        

        DB::beginTransaction();

        try {
            $user = User::create([
                'nip' => $request->nip,
                'password' => Hash::make($request->password),
                'role' => 'dosen'
            ]);

            $userID = $user->id_user;

            $dosen = Dosen::create([
                'id_user' => $userID,
                'nip' => $request->nip,
                'nama_lengkap' => $request->nama_lengkap,
                'jabatan' => $request->jabatan,
                'no_hp' => $request->no_hp,
                'password' => Hash::make($request->password),
            ]);

            DB::commit();

            toastr()->success('Pendaftaran Berhasil!');
            return redirect()->route('login');
        } catch (\Exception $e) {
            toastr()->error('Pendaftaran gagal: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }
}
