<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Kph\CreateKphRequest;
use App\Http\Requests\Api\Kph\UpdateKphRequest;
use App\Http\Resources\PostResource;
use App\Models\Kph;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KphController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            // 'start_date'    => 'nullable|date_format:Y-m-d',
            // 'end_date'      => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'search'        => 'nullable|string|max:100',
            'page'          => 'nullable|integer|min:1',
            'limit'         => 'nullable|integer|min:10|max:50',
            'jenis'         => ['nullable', 'string', Rule::in(['KPHL', 'KPHP', 'KPHK'])],
            'sort_by'       => ['nullable', 'string', Rule::in(['nama_kph', 'created_at'])],
            'sort_direction'=> ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ]);

        //Nama - Wilayah - Jenis - Alamat
        $query = Kph::select(['id', 'nama_kph', 'wilayah', 'jenis', 'alamat']);

        //Search
        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->query('search');
            return $q->where(function ($subQuery) use ($search) {
                $subQuery->where('nama_kph', 'like', "%{$search}%")
                      ->orWhere('wilayah', 'like', "%{$search}%")
                      ->orWhere('alamat', 'like', "%{$search}%");
            });
        });

        //Filter by jenis
        $query->when($request->filled('jenis'), function ($q) use ($request) {
            return $q->where('jenis', $request->query('jenis'));
        });

        //Filter by start_date and end_date
        // $query->when($request->filled('start_date') && $request->filled('end_date'), function ($q) use ($request) {
        //     return $q->whereBetween('created_at', [$request->query('start_date')." 00:00:00", $request->query('end_date')." 23:59:59"]);
        // });

        //Sort
        $sortBy = $request->query('sort_by', 'nama_kph');
        $sortDirection = $request->query('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        //Pagination
        $limit = $request->query('limit', 10);
        $paginator = $query->paginate($limit);

        // $field = ['id','nama_kph', 'wilayah', 'jenis', 'alamat'];
        // $kph = Kph::select($field)->orderBy('nama_kph', 'asc')->get();

        return response()->json([
            'statusCode' => 200,
            'message' => 'Data KPH berhasil diambil',
            'data' => [
                'data'       => $paginator->items(),
                'totalPages' => $paginator->lastPage(),
                'count'      => $paginator->total(),
            ]
        ], 200);

        // return (new PostResource(
        //     'Data KPH berhasil diambil', 
        //     200, 
        //     $kph
        // ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateKphRequest $request)
    {
        $validatedData = $request->validated();

        $kph = Kph::create($validatedData);

        return (new PostResource(
            'Data KPH berhasil ditambahkan', 
            201, 
            $kph
        ));

        // try{
        //     $data = $request->validated();
        //     $kph = Kph::create($data);
        //     return (new PostResource(
        //         'Data KPH berhasil ditambahkan', 
        //         201, 
        //         $kph
        //     ));
        // } catch (\Exception $e) {
        //     return (new PostResource(
        //         'Gagal menambahkan data KPH: '.$e->getMessage(), 
        //         500, 
        //         []
        //     ));
        // }

    }

    /**
     * Display the specified resource.
     */
    public function show(Kph $kph)
    {
        return (new PostResource(
            'Data KPH berhasil diambil', 
            200, 
            $kph
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKphRequest $request, Kph $kph)
    {
        $validatedData = $request->validated();

        $kph->update($validatedData);

        return (new PostResource(
            'Data KPH berhasil diperbarui', 
            200, 
            $kph
        ));

        // try {
        //     $data = $request->validated();
        //     $kph->update($data);

        //     return (new PostResource(
        //         'Data KPH berhasil diperbarui', 
        //         200, 
        //         $kph));

        // } catch (\Exception $e) {
        //     return (new PostResource(
        //         'Terjadi kesalahan pada server: '.$e->getMessage(), 
        //         500, 
        //         []
        //     ));
        // }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kph $kph)
    {
        $kph->delete();

        return new PostResource(
            'Data KPH berhasil dihapus', 
            200, 
            []
        );


        // try{
        //     $kph->delete();
        //     return (new PostResource(
        //         'Data KPH berhasil dihapus', 
        //         200, 
        //         []
        //     ));

        // } catch (Exception $e){
        //     return (new PostResource(
        //         'Terjadi kesalahan pada server: '.$e->getMessage(), 
        //         500, 
        //         []
        //     ));

        // }
    }
}