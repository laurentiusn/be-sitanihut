<?php

namespace App\Imports;

use App\Helpers\RoleHelper;
use App\Models\Kph;
use App\Models\Kth;
use App\Models\Role;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/** Class UserImport Excel Importer
 * @package App\Imports
 */
class UserImport implements ToCollection, WithHeadingRow, WithChunkReading, WithValidation
{
    protected string $rolePetaniId;
    protected array $dbData = [];

    /** Construct UserImport
     */
    public function __construct()
    {
        $this->rolePetaniId = RoleHelper::id('petani');
    }

    /** Validasi Format Dasar
     * @return array
     */
    public function rules(): array
    {
        return [
            'email'               => 'required|email|max:100',
            'password'            => 'required|string|min:8',
            'id_role'             => 'required|string',
            'id_kph'              => 'nullable|string',
            'id_kth'              => 'nullable|string',
            'nama_user'           => 'required|string|max:30',
            'nomor_induk'         => 'required|string|max:20',
            'jenis_kelamin'       => 'required|in:pria,wanita',
            'alamat'              => 'nullable|string',
            'nomor_wa'            => 'nullable|string|max:20',
            'pendidikan_terakhir' => 'nullable|string|max:10',
            'pekerjaan_sampingan' => 'nullable|string|max:20',
            'luas_lahan'          => 'nullable|numeric',
            'jabatan'             => 'nullable|string|max:100',
        ];
    }

    /** Pesan kesalahan validasi dasar
     * @return array
     */
    public function customValidationMessages(): array
    {
        return [
            'email.required'          => 'Email wajib diisi',
            'password.required'       => 'Password wajib diisi',
            'id_role.required'        => 'ID Role wajib diisi',
            'nama_user.required'      => 'Nama user wajib diisi',
            'nomor_induk.required'    => 'Nomor induk wajib diisi',
            'jenis_kelamin.required'  => 'Jenis kelamin wajib diisi',
            'jenis_kelamin.in'        => 'Jenis kelamin harus berupa "pria" atau "wanita"',
            'email.max'               => 'Email tidak boleh lebih dari 100 karakter',
            'password.min'            => 'Password minimal 8 karakter',
            'nama_user.max'           => 'Nama user tidak boleh lebih dari 30 karakter',
            'nomor_induk.max'         => 'Nomor induk tidak boleh lebih dari 20 karakter',
            'nomor_wa.max'            => 'Nomor WA tidak boleh lebih dari 20 karakter',
            'pendidikan_terakhir.max' => 'Pendidikan terakhir tidak boleh lebih dari 10 karakter',
            'pekerjaan_sampingan.max' => 'Pekerjaan sampingan tidak boleh lebih dari 20 karakter',
            'jabatan.max'             => 'Jabatan tidak boleh lebih dari 100 karakter',
        ];
    }

    /** Hook Aturan Bisnis & Validasi Database
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $rows = collect($validator->getData());
            
            // Prefetch data dari DB ke dalam memori agar proses sangat cepat
            $this->prefetchDatabaseData($rows);
            
            $seen = ['emails' => [], 'nomorInduks' => []];
            $customValidator = new UserImportValidator($this->rolePetaniId, $this->dbData);

            foreach ($rows as $index => $row) {
                $customValidator->validateRow($index, $row, $seen, $validator);
            }
        });
    }

    /** Eksekusi Insert Data
     * @param Collection $rows Koleksi baris terverifikasi
     * @return void
     */
    public function collection(Collection $rows): void
    {
        $usersToInsert = [];
        $userDetailsToInsert = [];
        $mapper = new UserImportMapper();

        foreach ($rows as $row) {
            $userId = Str::uuid()->toString();
            
            $rowArray = $row->toArray();
            $usersToInsert[] = $mapper->mapUserInsertData($rowArray, $userId);
            $userDetailsToInsert[] = $mapper->mapUserDetailInsertData($rowArray, $userId);
        }

        if (!empty($usersToInsert)) {
            User::insert($usersToInsert);
            UserDetail::insert($userDetailsToInsert);
        }
    }

    /** Ukuran Chunk
     * @return int
     */
    public function chunkSize(): int
    {
        return 250;
    }

    /** Prefetch Data Referensi Database
     * @param Collection $rows
     * @return void
     */
    private function prefetchDatabaseData(Collection $rows): void
    {
        $this->dbData = [
            'emails'      => User::whereIn('email', $rows->pluck('email')->filter()->unique())->pluck('email')->toArray(),
            'nomorInduks' => UserDetail::whereIn('nomor_induk', $rows->pluck('nomor_induk')->filter()->unique())->pluck('nomor_induk')->toArray(),
            'roles'       => Role::whereIn('id', $rows->pluck('id_role')->filter()->unique())->pluck('id')->toArray(),
            'kphs'        => Kph::whereIn('id', $rows->pluck('id_kph')->filter()->unique())->pluck('id')->toArray(),
            'kths'        => Kth::whereIn('id', $rows->pluck('id_kth')->filter()->unique())->get()->keyBy('id'),
        ];
    }
}