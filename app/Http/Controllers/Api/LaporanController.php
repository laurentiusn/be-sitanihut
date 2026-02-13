<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Laporan\CreateLaporanRequest;
use App\Http\Requests\Api\Laporan\UpdateLaporanRequest;
use App\Http\Resources\LaporanDetailResource;
use App\Http\Resources\LaporanResource;
use App\Models\Laporan;
use App\Models\LogLaporan;
use App\Models\Komoditas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class LaporanController extends Controller
{
    /**
     * Display a listing of the resource with full searching and filtering capabilities.
     */
    public function index(Request $request): JsonResponse
    {
        // 1. VALIDATION BLOCK
        $request->validate([
            'start_date'    => 'nullable|date_format:Y-m-d',
            'end_date'      => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'search'        => 'nullable|string|max:100',
            'page'          => 'nullable|integer|min:1',
            'limit'         => 'nullable|integer|min:10|max:50',
            'periode'       => 'nullable|integer|digits:4',
            'bulan'         => ['nullable', 'string', Rule::in(['januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember'])],
            'status'        => ['nullable', 'string', Rule::in(['belum diajukan', 'menunggu', 'diverifikasi', 'disetujui', 'ditolak'])],
            'sort_by'       => ['nullable', 'string', Rule::in(['tanggal', 'periode', 'status'])],
            'sort_direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ]);

        // 2. INITIAL QUERY & AUTHORIZATION
        $user = Auth::user();
        $query = Laporan::with(['user.userDetail', 'user.kph', 'masaTanam', 'masaPanen']);

        if ($user->role->slug === 'petani') {
            $query->where('id_user', $user->id);
        } elseif ($user->role->slug === 'penyuluh') {
            if ($user->id_kph) {
                $query->whereHas('user', function ($q) use ($user) {
                    $q->where('id_kph', $user->id_kph);
                })->whereIn('status', ['menunggu', 'diverifikasi', 'disetujui', 'ditolak']);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->role->slug === 'penanggung-jawab') {
            if ($user->id_kph) {
                $query->whereHas('user', function ($q) use ($user) {
                    $q->where('id_kph', $user->id_kph);
                })->whereIn('status', ['diverifikasi', 'disetujui', 'ditolak']);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->role->slug === 'sekretariat') {
            $query->whereIn('status', ['menunggu', 'diverifikasi', 'disetujui', 'ditolak']);
        } elseif ($user->role->slug === 'auditor') {
            $query->whereIn('status', ['disetujui']);
        }

        // 3. APPLYING FILTERS CONDITIONALLY

        // --- General Search ---
        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->query('search');
            return $q->where(function ($subQuery) use ($search) {

                $subQuery->whereHas('user.userDetail', function ($q_detail) use ($search) {
                    $q_detail->where('nama_user', 'like', "%{$search}%");
                })
                    ->orWhereHas('user.kph', function ($q_kph) use ($search) {
                        $q_kph->where('nama_kph', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user.kth', function ($q_kth) use ($search) {
                        $q_kth->where('nama_kth', 'like', "%{$search}%");
                    })
                    ->orWhereHas('masaPanen.komoditas', function ($q_panen) use ($search) {
                        $q_panen->where('nama_komoditas', 'like', "%{$search}%");
                    });
            });
        });


        // --- Start & End Date Filter ---
        $query->when($request->filled('start_date'), function ($q) use ($request) {
            return $q->whereDate('tanggal', '>=', $request->query('start_date'));
        });

        $query->when($request->filled('end_date'), function ($q) use ($request) {
            return $q->whereDate('tanggal', '<=', $request->query('end_date'));
        });

        // --- Period (Year) Filter ---
        $query->when($request->filled('periode'), function ($q) use ($request) {
            return $q->where('periode', $request->query('periode'));
        });

        // --- Bulan Filter ---
        $query->when($request->filled('bulan'), function ($q) use ($request) {
            return $q->where('bulan', $request->query('bulan'));
        });

        // --- Status Filter ---
        $query->when($request->filled('status'), function ($q) use ($request) {
            return $q->where('status', $request->query('status'));
        });

        // 4. SORTING & PAGINATION
        $sortBy = $request->query('sort_by', 'tanggal');
        $sortDirection = $request->query('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $limit = $request->query('limit', 10);
        $paginator = $query->paginate($limit)->withQueryString();
        // 5. CUSTOM JSON RESPONSE
        return response()->json([
            'statusCode' => 200,
            'message'   => 'Data Laporan berhasil diambil',
            'data'      => [
                'data'       => LaporanResource::collection($paginator->items()),
                'totalPages' => $paginator->lastPage(),
                'count'      => $paginator->total(),
            ]
        ], 200);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateLaporanRequest $request): JsonResponse
    {
        $this->authorize('create', Laporan::class);

        $validatedData = $request->validated();
        $validatedData['tanggal'] = now()->toDateString();
        $validatedData['id_user'] = Auth::id();

        $laporanData = Arr::except($validatedData, ['masa_tanam', 'masa_panen', 'lampiran']);
        $masaTanamData = $validatedData['masa_tanam'] ?? [];
        $masaPanenData = $validatedData['masa_panen'] ?? [];
        $lampiranFiles = $request->file('lampiran', []);

        $nteTotal = 0;
        $processedMasaPanen = [];

        if (!empty($masaPanenData)) {
            foreach ($masaPanenData as $panen) {
                $jumlah = (float)($panen['jumlah'] ?? 0);
                $hargaSatuan = (float)($panen['harga_satuan'] ?? 0);
                $hargaTotal = $jumlah * $hargaSatuan;

                $panen['harga_total'] = $hargaTotal;
                $processedMasaPanen[] = $panen;

                $nteTotal += $hargaTotal;
            }
        }

        $laporanData['nte'] = $nteTotal;

        if (isset($validatedData['status']) && $validatedData['status'] === 'menunggu') {
            $laporanData['tanggal_pengajuan'] = now();
        }

        $laporan = DB::transaction(function () use ($laporanData, $masaTanamData, $lampiranFiles, $processedMasaPanen) {
            $laporan = Laporan::create($laporanData);

            // This now works directly with the validated data.
            if (!empty($masaTanamData)) {
                $laporan->masaTanam()->createMany($masaTanamData);
            }
            if (!empty($processedMasaPanen)) {
                $laporan->masaPanen()->createMany($processedMasaPanen);
            }

            // Lampiran logic remains the same
            if (!empty($lampiranFiles)) {
                foreach ($lampiranFiles as $file) {
                    $originalFilename = $file->getClientOriginalName();
                    $filenameWithoutExt = pathinfo($originalFilename, PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $sanitizedFilename = preg_replace('/[^A-Za-z0-9\._-]/', '', $filenameWithoutExt);
                    $newFilename = Str::random(8) . '-' . $sanitizedFilename . '.' . $extension;
                    $path = $file->storeAs('lampiran', $newFilename, 'public');

                    $laporan->lampiran()->create([
                        'path'      => $path,
                        'nama_file' => $originalFilename,
                    ]);
                }
            }
            return $laporan;
        });

        $laporan->load(['masaTanam.komoditas', 'masaPanen.komoditas', 'lampiran']);

        return response()->json([
            'statusCode' => 201,
            'message'    => 'Laporan berhasil dibuat',
            'data'       => new LaporanDetailResource($laporan)
        ], 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(Laporan $laporan): JsonResponse
    {
        $this->authorize('view', $laporan);

        $laporan->load(['user.userDetail', 'user.kph', 'masaTanam', 'masaPanen', 'lampiran']);

        return response()->json([
            'statusCode' => 200,
            'message'   => 'Data Laporan berhasil diambil',
            'data'      => new LaporanDetailResource($laporan)
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLaporanRequest $request, Laporan $laporan): JsonResponse
    {
        $this->authorize('update', $laporan);
        $validatedData = $request->validated();
        $user = Auth::user();

        //CHECK IF LAPORAN IS DISETUJUI
        // if ($laporan->status === 'disetujui') {
        //     // error
        //     return response()->json([
        //         'statusCode' => 403,
        //         'message'    => 'Laporan sudah disetujui dan tidak dapat diubah.',
        //     ], 403);
        // }

        $laporanDataToUpdate = [];

        // ROLE CHECK
        if ($user->role->slug === 'petani') {
            $allowedFields = ['periode', 'bulan', 'tanggal', 'modal', 'catatan_petani', 'status'];
            $laporanDataToUpdate = Arr::only($validatedData, $allowedFields);
        } elseif ($user->role->slug === 'penyuluh') {
            // $this->authorize('periksaLaporan', $laporan); 
            $allowedFields = ['status', 'catatan_penyuluh'];
            $laporanDataToUpdate = Arr::only($validatedData, $allowedFields);
        } elseif ($user->role->slug === 'penanggung-jawab') {
            $allowedFields = ['status', 'catatan_penyuluh'];
            $laporanDataToUpdate = Arr::only($validatedData, $allowedFields);
        } elseif ($user->role->slug === 'sekretariat') {
            $allowedFields = ['status', 'catatan_penyuluh'];
            $laporanDataToUpdate = Arr::only($validatedData, $allowedFields);
        }

        // CALCULATE NTE
        if (array_key_exists('masa_panen', $validatedData)) {
            $nteTotal = 0;
            $masaPanenInput = $validatedData['masa_panen'] ?? []; // Default to empty array if null

            if (!empty($masaPanenInput)) {
                foreach ($masaPanenInput as $panen) {
                    $hargaTotal = (float)($panen['jumlah'] ?? 0) * (float)($panen['harga_satuan'] ?? 0);
                    $nteTotal += $hargaTotal;
                }
            }
            // If the user sent "masa_panen": [], the nteTotal will correctly be 0.
            $laporanDataToUpdate['nte'] = $nteTotal;
        }

        if (isset($validatedData['status'])) {
            $newStatus = $validatedData['status'];
            if ($newStatus === 'menunggu') {
                $laporanDataToUpdate['tanggal_pengajuan'] = now();
            }
            if (in_array($newStatus, ['diverifikasi', 'ditolak'])) {
                $laporanDataToUpdate['tanggal_tindakan'] = now();
            }
            if (in_array($newStatus, ['disetujui'])) {
                $laporanDataToUpdate['tanggal_keputusan'] = now();
            }
        }

        DB::transaction(function () use ($laporan, $laporanDataToUpdate, $validatedData, $user, $request) {

            // A. Update the main Laporan record
            if (!empty($laporanDataToUpdate)) {
                $laporan->update($laporanDataToUpdate);
            }

            // B. Sync Masa Tanam records if present
            if ($user->role->slug === 'petani') {
                if ($request->has('masa_tanam')) {
                    $laporan->masaTanam()->delete();
                    $laporan->masaTanam()->createMany($validatedData['masa_tanam']);
                }

                // C. Sync Masa Panen records if present
                if (array_key_exists('masa_panen', $validatedData)) {
                    $processedMasaPanen = [];
                    $masaPanenInput = $validatedData['masa_panen'] ?? [];
                    if (!empty($masaPanenInput)) {
                        foreach ($masaPanenInput as $panen) {
                            $panen['harga_total'] = (float)($panen['jumlah'] ?? 0) * (float)($panen['harga_satuan'] ?? 0);
                            $processedMasaPanen[] = $panen;
                        }
                    }
                    $laporan->masaPanen()->delete();
                    if (!empty($processedMasaPanen)) {
                        $laporan->masaPanen()->createMany($processedMasaPanen);
                    }
                }

                // D. Sync file attachments
                if (array_key_exists('lampiran_existing', $validatedData)) {
                    $idsToKeep = $validatedData['lampiran_existing'] ?? [];
                    foreach ($laporan->lampiran as $attachment) {
                        if (!in_array($attachment->id, $idsToKeep)) {
                            $attachment->delete();
                        }
                    }
                }
                if ($request->hasFile('lampiran_new')) {
                    foreach ($request->file('lampiran_new') as $file) {
                        $originalFilename = $file->getClientOriginalName();
                        $newFilename = Str::random(8) . '-' . preg_replace('/[^A-Za-z0-9\._-]/', '', pathinfo($originalFilename, PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                        $path = $file->storeAs('lampiran', $newFilename, 'public');
                        $laporan->lampiran()->create([
                            'path'      => $path,
                            'nama_file' => $originalFilename,
                        ]);
                    }
                }
            }

            if (isset($validatedData['status']) && $validatedData['status'] === 'disetujui') {

                // Refresh relasi untuk memastikan kita mendapatkan data masa panen yang terbaru
                // setelah kemungkinan sinkronisasi.
                $laporan->load('masaPanen');

                // Tambahkan harga_total dari setiap masa panen ke komoditas yang sesuai.
                foreach ($laporan->masaPanen as $panen) {
                    Komoditas::where('id', $panen->id_komoditas)->increment('nte', $panen->harga_total);
                }
            }

            // E. Create the audit log if status was finalized
            if (isset($laporanDataToUpdate['status']) && in_array($laporanDataToUpdate['status'], ['disetujui', 'diverifikasi', 'ditolak'])) {
                LogLaporan::create([
                    'id_laporan'   => $laporan->id,
                    'id_pengaju'   => $laporan->id_user,
                    'id_pemeriksa' => $user->id,
                    'status'       => $laporanDataToUpdate['status'],
                    'periode'      => $laporan->periode,
                    'bulan'        => $laporan->bulan,
                ]);
            }
        });

        $updatedLaporan = $laporan->fresh()->load(['user.userDetail', 'masaTanam.komoditas', 'masaPanen.komoditas', 'lampiran']);

        return response()->json([
            'statusCode' => 200,
            'message'   => 'Data Laporan berhasil diupdate',
            'data'      => new LaporanDetailResource($updatedLaporan)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Laporan $laporan): JsonResponse
    {
        $this->authorize('delete', $laporan);

        $laporan->delete();

        return response()->json([
            'statusCode' => 200,
            'message'   => 'Data Laporan berhasil dihapus',
            'data'      => null
        ], 200);
    }
}
