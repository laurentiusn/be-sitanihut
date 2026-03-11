<?php

namespace App\Services;

use App\Models\User;
use App\Models\Kth;
use App\Helpers\RoleHelper;
use Illuminate\Http\Exceptions\HttpResponseException;

/** Class KthService
 * @package App\Services
 */
class KthService
{
    /** @var User */
    protected User $userModel;

    /** KthService constructor
     * @param User $userModel Dependency injection untuk model User
     */
    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }
    
    /** Memvalidasi penyuluh dengan KPH
     * @param string $idPenyuluh ID dari penyuluh
     * @param string $idKph ID dari KPH
     * @return void
     * @throws HttpResponseException
     */
    private function validatePenyuluhKphMatch(string $idPenyuluh, string $idKph): void
    {
        $rolePenyuluh = RoleHelper::id('penyuluh');
        $penyuluh = $this->userModel->where('id', $idPenyuluh)
                        ->where('id_role', $rolePenyuluh)
                        ->first();

        if (!$penyuluh) {
            $this->throwValidationError('Penyuluh tidak ada');
        }

        if ($penyuluh->id_kph !== $idKph) {
            $this->throwValidationError('Penyuluh tidak terdaftar di KPH yang dipilih');
        }
    }

    /** Melempar error validasi
     * @param string $message Pesan error yang akan dikembalikan
     * @return void
     * @throws HttpResponseException
     */
    private function throwValidationError(string $message): void
    {
        throw new HttpResponseException(response()->json([
            'statusCode' => 422,
            'message' => $message,
        ], 422));
    }
    
    /** Validasi dan manipulasi data sebelum insert
     * @param array $validatedData Data KTH yang sudah lolos validasi request
     * @param User $currentUser Objek user yang sedang mengakses (melakukan aksi)
     * @return array Data yang telah disesuaikan
     */
    public function createCheck(array $validatedData, User $currentUser): array
    {
        if ($currentUser->hasRole('penyuluh')) {
            $validatedData['id_kph'] = $currentUser->id_kph;
            $validatedData['id_penyuluh'] = $currentUser->id;
        } elseif (!empty($validatedData['id_penyuluh'])) {
            $this->validatePenyuluhKphMatch($validatedData['id_penyuluh'], $validatedData['id_kph']);
        }

        return $validatedData;
    }

    /** Validasi dan manipulasi data sebelum update
     * @param array $validatedData Data KTH yang sudah lolos validasi request
     * @param User $currentUser Objek user yang sedang mengakses
     * @param Kth $kth Objek KTH asli yang akan diubah
     * @return array Data yang telah disesuaikan
     */
    public function updateCheck(array $validatedData, User $currentUser, Kth $kth): array
    {
        if ($currentUser->hasRole('penyuluh') && (!empty($validatedData['id_kph']) || !empty($validatedData['id_penyuluh']))) {
            $this->throwValidationError('KPH atau Penyuluh tidak boleh diubah');
        }

        $finalIdKph = $validatedData['id_kph'] ?? $kth->id_kph;
        $finalIdPenyuluh = array_key_exists('id_penyuluh', $validatedData) 
                            ? $validatedData['id_penyuluh'] 
                            : $kth->id_penyuluh;

        if (!empty($finalIdPenyuluh)) {
            $this->validatePenyuluhKphMatch($finalIdPenyuluh, $finalIdKph);
        }

        return $validatedData;
    }
}