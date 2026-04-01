<?php

namespace App\Services;

use App\Repository\UserRepository;
use Carbon\Carbon;

/**
 * Class StatistikService
 * 
 * Service yang menangani logika pengolahan data statistik,
 * contohnya untuk ekstraksi umur petani berdasarkan Nomor Induk Kependudukan (NIK).
 *
 * @package App\Services
 */
class StatistikService
{
    /**
     * @var UserRepository Instansiasi UserRepository
     */
    protected UserRepository $userRepository;

    /**
     * StatistikService constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Mengambil daftar NIK petani lalu mengelompokkannya ke dalam rentang umur tertentu.
     *
     * @param string $rolePetaniId ID role untuk Petani
     * @return array Array berisi rentang umur beserta jumlah petani di rentang tersebut
     */
    public function getRangeUmurPetani(string $rolePetaniId): array
    {
        $niks = $this->userRepository->getNikByRoleId($rolePetaniId);

        $currentYear2Digits = (int) Carbon::now()->format('y');

        $rangeUmurPetani = [
            '<11'   => 0,
            '11-20' => 0,
            '21-30' => 0,
            '31-40' => 0,
            '41-50' => 0,
            '51-60' => 0,
            '>60'   => 0,
        ];

        foreach ($niks as $nik) {
            $age = $this->calculateAgeFromNik($nik, $currentYear2Digits);
            
            if ($age !== null) {
                $category = $this->getAgeCategory($age);
                $rangeUmurPetani[$category]++;
            }
        }

        return $rangeUmurPetani;
    }

    /**
     * Menghitung umur saat ini berdasarkan data NIK (16 digit) sesuai format KTP Indonesia.
     * Termasuk penyesuaian untuk tanggal lahir wanita (+40).
     *
     * @param string|null $nik NIK petani (contoh: 1871... untuk daerah tertentu)
     * @param int $currentYear2Digits 2 digit terakhir dari tahun sekarang (contoh: 24 untuk 2024)
     * @return int|null Mengembalikan umur dalam bentuk angka, atau null jika NIK tidak valid
     */
    private function calculateAgeFromNik(?string $nik, int $currentYear2Digits): ?int
    {
        if (!$nik || strlen($nik) < 12) return null;
        
        $dateStr = substr($nik, 6, 2);
        $monthStr = substr($nik, 8, 2);
        $yearStr = substr($nik, 10, 2);
        
        if (!is_numeric($dateStr) || !is_numeric($monthStr) || !is_numeric($yearStr)) {
            return null;
        }
        
        $date = (int)$dateStr;
        $month = (int)$monthStr;
        $year = (int)$yearStr;
        
        if ($date > 40) {
            $date -= 40;
        }
        
        if ($date < 1 || $date > 31 || $month < 1 || $month > 12) {
            return null;
        }
        
        $birthYear = ($year > $currentYear2Digits) ? 1900 + $year : 2000 + $year;
        $birthDateString = sprintf('%04d-%02d-%02d', $birthYear, $month, $date);
        
        try {
            return Carbon::parse($birthDateString)->age;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Mengonversi angka umur ke dalam format string kategori/rentang umur.
     *
     * @param int $age Umur petani
     * @return string Kategori rentang umur (contoh: '21-30')
     */
    private function getAgeCategory(int $age): string
    {
        return match (true) {
            $age < 11  => '<11',
            $age <= 20 => '11-20',
            $age <= 30 => '21-30',
            $age <= 40 => '31-40',
            $age <= 50 => '41-50',
            $age <= 60 => '51-60',
            default    => '>60',
        };
    }
}