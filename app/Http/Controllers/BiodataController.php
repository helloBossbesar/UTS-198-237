<?php

namespace App\Http\Controllers;

use App\Models\Biodata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BiodataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = Biodata::orderBy('id', 'asc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Daftar biodata berhasil diambil.',
                'data' => $data,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data biodata: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'nim' => 'required|string',
            'deskripsi' => 'required|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nim.required' => 'NIM wajib diisi.',
            'deskripsi.required' => 'Deskripsi wajib diisi.',
            'foto.image' => 'File foto harus berupa gambar valid.',
            'foto.mimes' => 'Format foto harus berupa jpg, jpeg, png, webp, atau gif.',
            'foto.max' => 'Ukuran file foto maksimal adalah 5MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data' => null,
            ], 422);
        }

        try {
            $namaFile = null;
            if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
                $file = $request->file('foto');
                $namaFile = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $destination = public_path('uploads');
                if (!File::isDirectory($destination)) {
                    File::makeDirectory($destination, 0755, true, true);
                }
                $file->move($destination, $namaFile);
            }

            $biodata = Biodata::create([
                'nama' => $request->nama,
                'nim' => $request->nim,
                'deskripsi' => $request->deskripsi,
                'foto' => $namaFile,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Biodata berhasil ditambahkan.',
                'data' => $biodata,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan biodata: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $biodata = Biodata::find($id);

            if (!$biodata) {
                return response()->json([
                    'success' => false,
                    'message' => 'Biodata tidak ditemukan.',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Biodata berhasil ditemukan.',
                'data' => $biodata,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data biodata: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'nim' => 'required|string',
            'deskripsi' => 'required|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nim.required' => 'NIM wajib diisi.',
            'deskripsi.required' => 'Deskripsi wajib diisi.',
            'foto.image' => 'File foto harus berupa gambar valid.',
            'foto.mimes' => 'Format foto harus berupa jpg, jpeg, png, webp, atau gif.',
            'foto.max' => 'Ukuran file foto maksimal adalah 5MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data' => null,
            ], 422);
        }

        try {
            $biodata = Biodata::find($id);

            if (!$biodata) {
                return response()->json([
                    'success' => false,
                    'message' => 'Biodata tidak ditemukan.',
                    'data' => null,
                ], 404);
            }

            $namaFile = $biodata->foto;

            if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
                // Hapus foto lama jika ada
                if ($biodata->foto) {
                    $oldPath = public_path('uploads/' . $biodata->foto);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }

                $file = $request->file('foto');
                $namaFile = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $destination = public_path('uploads');
                if (!File::isDirectory($destination)) {
                    File::makeDirectory($destination, 0755, true, true);
                }
                $file->move($destination, $namaFile);
            }

            $biodata->update([
                'nama' => $request->nama,
                'nim' => $request->nim,
                'deskripsi' => $request->deskripsi,
                'foto' => $namaFile,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Biodata berhasil diperbarui.',
                'data' => $biodata->fresh(),
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui biodata: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $biodata = Biodata::find($id);

            if (!$biodata) {
                return response()->json([
                    'success' => false,
                    'message' => 'Biodata tidak ditemukan.',
                    'data' => null,
                ], 404);
            }

            // Hapus file foto jika ada
            if ($biodata->foto) {
                $path = public_path('uploads/' . $biodata->foto);
                if (File::exists($path)) {
                    File::delete($path);
                }
            }

            $biodata->delete();

            return response()->json([
                'success' => true,
                'message' => 'Biodata berhasil dihapus.',
                'data' => null,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus biodata: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
