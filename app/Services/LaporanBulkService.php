<?php

namespace App\Services;

use App\Repository\LaporanRepository;
use App\Models\Laporan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LaporanBulkService
{
    protected LaporanRepository $repository;

    public function __construct(LaporanRepository $repository)
    {
        $this->repository = $repository;
    }

    private function storeFile($file): string
    {
        $originalFilename = $file->getClientOriginalName();
        $filenameWithoutExt = pathinfo($originalFilename, PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();

        // Bersihkan nama file dari karakter aneh/spasi
        $sanitizedFilename = preg_replace('/[^A-Za-z0-9\._-]/', '', $filenameWithoutExt);

        // Format: 8 Karakter Acak - Nama Bersih . Ekstensi
        $newFilename = Str::random(8) . '-' . $sanitizedFilename . '.' . $extension;

        return $file->storeAs('lampiran', $newFilename, 'public');
    }

    private function cleanupFailedUploads(array $lampiranData)
    {
        foreach ($lampiranData as $lampiran) Storage::disk('public')->delete($lampiran['path']);
    }

    public function createBulk(array $baruRaw, User $user, array $lampiranFiles): array
    {
        Gate::authorize('create', Laporan::class);

        $hasil = ['sukses' => [], 'gagal' => []];
        $insertData = ['laporan' => [], 'masa_tanam' => [], 'masa_panen' => [], 'lampiran' => [], 'log' => []];
        $now = now();

        DB::beginTransaction();
        try {
            foreach ($baruRaw as $index => $item) {
                $laporanId = $item['id'];
                $nteTotal = 0;
                $clientCreated = isset($item['created_at']) ? Carbon::parse($item['created_at']) : clone $now;
                $clientUpdated = isset($item['updated_at']) ? Carbon::parse($item['updated_at']) : clone $now;

                foreach ($item['masa_panen'] ?? [] as $panen) {
                    $hargaTotal = (float)($panen['jumlah'] ?? 0) * (float)($panen['harga_satuan'] ?? 0);
                    $nteTotal += $hargaTotal;
                    $insertData['masa_panen'][] = [
                        'id' => Str::uuid()->toString(),
                        'id_laporan' => $laporanId,
                        'tanggal' => $panen['tanggal'] ?? $clientCreated->toDateString(),
                        'id_komoditas' => $panen['id_komoditas'],
                        'jumlah' => (float)($panen['jumlah'] ?? 0),
                        'harga_satuan' => (float)($panen['harga_satuan'] ?? 0),
                        'harga_total' => $hargaTotal,
                        'created_at' => $clientCreated,
                        'updated_at' => $clientUpdated,
                    ];
                }

                foreach ($item['masa_tanam'] ?? [] as $tanam) {
                    $insertData['masa_tanam'][] = [
                        'id' => Str::uuid()->toString(),
                        'id_laporan' => $laporanId,
                        'tanggal' => $tanam['tanggal'] ?? $clientCreated->toDateString(),
                        'id_komoditas' => $tanam['id_komoditas'],
                        'jumlah' => (float)($tanam['jumlah'] ?? 0),
                        'usia_tanam' => (float)($tanam['usia_tanam'] ?? 0),
                        'created_at' => $clientCreated,
                        'updated_at' => $clientUpdated,
                    ];
                }

                $files = $lampiranFiles[$index]['lampiran'] ?? [];
                foreach ($files as $file) {
                    $path = $this->storeFile($file);
                    $insertData['lampiran'][] = [
                        'id' => Str::uuid()->toString(),
                        'id_laporan' => $laporanId,
                        'path' => $path,
                        'nama_file' => $file->getClientOriginalName(),
                        'created_at' => $clientCreated,
                        'updated_at' => $clientUpdated,
                    ];
                }

                $insertData['laporan'][] = [
                    'id' => $laporanId,
                    'periode' => $item['periode'],
                    'bulan' => $item['bulan'],
                    'tanggal' => $clientCreated->toDateString(),
                    'modal' => $item['modal'] ?? 0,
                    'nte' => $nteTotal,
                    'status' => $item['status'],
                    'tanggal_pengajuan' => ($item['status'] === 'menunggu') ? $clientCreated->toDateString() : null,
                    'catatan_petani' => $item['catatan_petani'] ?? null,
                    'id_user' => $user->id,
                    'created_at' => $clientCreated,
                    'updated_at' => $clientUpdated,
                ];

                $hasil['sukses'][] = $laporanId;
            }

            $this->repository->executeInserts($insertData);
            DB::commit();
            return $hasil;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->cleanupFailedUploads($insertData['lampiran']);
            throw $e;
        }
    }
}