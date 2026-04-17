<?php

namespace App\Services;

use App\Models\Informasi;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Class InformasiService
 * 
 * Service untuk mengelola logika bisnis dan operasi penyimpanan file
 * yang berkaitan dengan entitas Informasi.
 *
 * @package App\Services
 */
class InformasiService
{
    /**
     * Menerapkan filter pencarian pada Query Builder
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $search
     * @return void
     */
    public function applySearchFilter($query, ?string $search): void
    {
        if (!$search) return;

        $query->where(function ($subQuery) use ($search) {
            $subQuery->where('title', 'like', "%{$search}%")
                     ->orWhere('desc', 'like', "%{$search}%")
                     ->orWhere('loc', 'like', "%{$search}%")
                     ->orWhere('organizer', 'like', "%{$search}%");
        });
    }

    /**
     * Menangani logika pembaruan atau penghapusan gambar saat proses update
     *
     * @param Request $request
     * @param Informasi $informasi
     * @param array $validatedData
     * @return array
     */
    public function processImageUpdate(Request $request, Informasi $informasi, array $validatedData): array
    {
        if ($request->hasFile('image')) {
            $this->deleteImageIfExists($informasi->path_image);
            
            $imageData = $this->handleImageUpload($request->file('image'));
            $validatedData = array_merge($validatedData, $imageData);
        } elseif ($request->boolean('remove_image')) {
            $this->deleteImageIfExists($informasi->path_image);
            
            $validatedData['nama_image'] = null;
            $validatedData['path_image'] = null;
        }

        unset($validatedData['remove_image']);
        
        return $validatedData;
    }

    /**
     * Mengunggah file gambar dan mengembalikan path serta nama filenya
     *
     * @param UploadedFile $file
     * @return array
     */
    public function handleImageUpload(UploadedFile $file): array
    {
        $originalFilename = $file->getClientOriginalName();
        $filenameWithoutExt = pathinfo($originalFilename, PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        
        $sanitizedFilename = preg_replace('/[^A-Za-z0-9\._-]/', '', $filenameWithoutExt);
        $newFilename = Str::random(8) . '-' . $sanitizedFilename . '.' . $extension;

        $path = $file->storeAs('informasi', $newFilename, 'public');

        return [
            'nama_image' => $originalFilename,
            'path_image' => $path,
        ];
    }

    /**
     * Menghapus file gambar fisik dari Storage jika file tersebut ada
     *
     * @param string|null $path
     * @return void
     */
    public function deleteImageIfExists(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
