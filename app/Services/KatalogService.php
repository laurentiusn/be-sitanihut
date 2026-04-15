<?php

namespace App\Services;

use App\Models\Katalog;
use App\Helpers\RoleHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Class KatalogService
 * 
 * Service untuk mengelola logika bisnis yang berkaitan dengan entitas Katalog,
 * terutama pemrosesan file gambar (upload & sinkronisasi) serta validasi otorisasi.
 *
 * @package App\Services
 */
class KatalogService
{
    protected string $rolePenyuluh;

    /**
     * Konstruktor KatalogService.
     * Menginisialisasi RoleHelper untuk men-cache ID Role penyuluh.
     */
    public function __construct()
    {
        $this->rolePenyuluh = RoleHelper::id('penyuluh');
    }

    /**
     * Fungsi *helper* untuk memeriksa apakah *User* yang sedang login
     * diizinkan mengakses data Katalog ini (Pembatasan akses horisontal).
     *
     * @param Katalog $katalog
     * @return bool True jika diizinkan, false jika ditolak
     */
    public function isAuthorized(Katalog $katalog): bool
    {
        if (!Auth::check()) return true; 

        $user = Auth::user();
        return !($user->id_role === $this->rolePenyuluh && $katalog->id_user !== $user->id);
    }

    /**
     * Membersihkan *file* fisik dan record DB gambar yang sengaja dibuang oleh pengguna
     * pada proses *update* (menyelaraskan *ID arrays to keep* dengan DB).
     *
     * @param Katalog $katalog
     * @param array $idsToKeep Array of ID yang tidak boleh dihapus
     * @return void
     */
    public function syncPictures(Katalog $katalog, array $idsToKeep): void
    {
        foreach ($katalog->pictures as $picture) {
            if (!in_array($picture->id, $idsToKeep)) {
                if (Storage::disk('public')->exists($picture->path)) {
                    Storage::disk('public')->delete($picture->path);
                }
                $picture->delete();
            }
        }
    }

    /**
     * Mengunggah koleksi *file* gambar ke Storage dan membuat rekor relasionalnya ke DB.
     * Fungsi ini menyanitasi nama file untuk menghindari celah keamanan (XSS / Directory Traversal).
     *
     * @param Katalog $katalog
     * @param array $files Koleksi file dari HTTP Upload
     * @return void
     */
    public function uploadAndSavePictures(Katalog $katalog, array $files): void
    {
        foreach ($files as $file) {
            $originalFilename = $file->getClientOriginalName();
            
            $filenameWithoutExt = pathinfo($originalFilename, PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $sanitizedFilename = preg_replace('/[^A-Za-z0-9\._-]/', '', $filenameWithoutExt);
            
            $newFilename = Str::random(8) . '-' . $sanitizedFilename . '.' . $extension;
            $path = $file->storeAs('catalog_images', $newFilename, 'public');
            
            $katalog->pictures()->create([
                'nama_file' => $originalFilename,
                'path'      => $path,
            ]);
        }
    }
}
