<?php

namespace App\Imports;

/** Class UserImportValidator
 * @package App\Imports
 */
class UserImportValidator
{
    private string $rolePetaniId;
    private array $dbData;

    /** UserImportValidator constructor
     * @param string $rolePetaniId ID untuk Role Petani
     * @param array $dbData Kumpulan data reference (email, NIK, dll) yang sudah di-*prefetch*
     */
    public function __construct(string $rolePetaniId, array $dbData)
    {
        $this->rolePetaniId = $rolePetaniId;
        $this->dbData = $dbData;
    }

    /** Eksekusi validasi kustom baris
     * @param int $index Nomor baris (indeks iterasi)
     * @param array $row Array payload baris data
     * @param array &$seen Array referensi pencatatan baris ganda
     * @param \Illuminate\Validation\Validator $validator Instance validator laravel
     * @return void
     */
    public function validateRow(int $index, array $row, array &$seen, $validator): void
    {
        $this->validateEmailRules($index, $row['email'] ?? null, $seen, $validator);
        $this->validateNomorIndukRules($index, $row['nomor_induk'] ?? null, $seen, $validator);
        
        $idRole = $row['id_role'] ?? null;
        $idKph  = $row['id_kph'] ?? null;
        $idKth  = $row['id_kth'] ?? null;
        
        $this->validateRelations($index, $idRole, $idKph, $idKth, $validator);
        $this->validatePetaniSpecificRules($index, $idRole, $idKph, $idKth, $validator);
    }

    /** Validasi keunikan email
     * @param int $index Nomor baris (indeks iterasi)
     * @param string|null $email Alamat email yang diinput
     * @param array &$seen Array referensi memori untuk mencatat email yang sudah lewat
     * @param \Illuminate\Validation\Validator $validator Instance validator laravel
     * @return void
     */
    private function validateEmailRules(int $index, ?string $email, array &$seen, $validator): void
    {
        if ($email) {
            if (in_array($email, $seen['emails'])) {
                $validator->errors()->add("{$index}.email", "Email \"{$email}\" terdeteksi ganda/duplikat di dalam file Excel");
            } elseif (in_array($email, $this->dbData['emails'])) {
                $validator->errors()->add("{$index}.email", "Email \"{$email}\" sudah terdaftar di sistem");
            } else {
                $seen['emails'][] = $email;
            }
        }
    }

    /** Validasi keunikan NIK
     * @param int $index Nomor baris (indeks iterasi)
     * @param string|null $nomorInduk NIK yang diinput
     * @param array &$seen Array referensi memori pencatatan NIK
     * @param \Illuminate\Validation\Validator $validator Instance validator laravel
     * @return void
     */
    private function validateNomorIndukRules(int $index, ?string $nomorInduk, array &$seen, $validator): void
    {
        if ($nomorInduk) {
            if (in_array($nomorInduk, $seen['nomorInduks'])) {
                $validator->errors()->add("{$index}.nomor_induk", "Nomor induk \"{$nomorInduk}\" terdeteksi ganda/duplikat di dalam file Excel");
            } elseif (in_array($nomorInduk, $this->dbData['nomorInduks'])) {
                $validator->errors()->add("{$index}.nomor_induk", "Nomor induk \"{$nomorInduk}\" sudah digunakan oleh user lain");
            } else {
                $seen['nomorInduks'][] = $nomorInduk;
            }
        }
    }

    /** Validasi ketersediaan relasi ID
     * @param int $index Nomor baris (indeks iterasi)
     * @param string|null $idRole ID Role
     * @param string|null $idKph ID KPH
     * @param string|null $idKth ID KTH
     * @param \Illuminate\Validation\Validator $validator Instance validator laravel
     * @return void
     */
    private function validateRelations(int $index, ?string $idRole, ?string $idKph, ?string $idKth, $validator): void
    {
        if ($idRole && !in_array($idRole, $this->dbData['roles'])) {
            $validator->errors()->add("{$index}.id_role", "ID Role \"{$idRole}\" tidak ditemukan");
        }
        if ($idKph && !in_array($idKph, $this->dbData['kphs'])) {
            $validator->errors()->add("{$index}.id_kph", "ID KPH \"{$idKph}\" tidak ditemukan");
        }
        if ($idKth && !$this->dbData['kths']->has($idKth)) {
            $validator->errors()->add("{$index}.id_kth", "ID KTH \"{$idKth}\" tidak ditemukan");
        }
    }

    /** Validasi khusus Petani
     * @param int $index Nomor baris (indeks iterasi)
     * @param string|null $idRole ID Role
     * @param string|null $idKph ID KPH
     * @param string|null $idKth ID KTH
     * @param \Illuminate\Validation\Validator $validator Instance validator laravel
     * @return void
     */
    private function validatePetaniSpecificRules(int $index, ?string $idRole, ?string $idKph, ?string $idKth, $validator): void
    {
        if ($idRole === $this->rolePetaniId) {
            if (empty($idKph)) {
                $validator->errors()->add("{$index}.id_kph", 'Untuk role Petani, kolom KPH tidak boleh kosong');
            }
            if (empty($idKth)) {
                $validator->errors()->add("{$index}.id_kth", 'Untuk role Petani, kolom KTH tidak boleh kosong');
            }
            
            if ($idKth && $idKph) {
                $kth = $this->dbData['kths']->get($idKth);
                if ($kth && $kth->id_kph !== $idKph) {
                    $validator->errors()->add("{$index}.id_kth", 'Untuk role Petani, KTH yang dipilih tidak terdaftar di KPH yang bersangkutan');
                }
            }
        }
    }
}
