<?php

namespace App\Repository;

use App\Models\Laporan;
use Illuminate\Support\Facades\DB;

/** Class LaporanRepository
 * @package App\Repository
 */
class LaporanRepository
{
    /** Mengeksekusi query insert massal
     * @param array $data Array asosiatif yang berisi data-data insert. Key yang didukung: 
     *                    'laporan', 'masa_tanam', 'masa_panen', 'lampiran', 'log'
     * @return void
     */
    public function executeInserts(array $data): void
    {
        if (!empty($data['laporan']))    Laporan::insert($data['laporan']);
        if (!empty($data['masa_tanam'])) DB::table('masa_tanam')->insert($data['masa_tanam']);
        if (!empty($data['masa_panen'])) DB::table('masa_panen')->insert($data['masa_panen']);
        if (!empty($data['lampiran']))   DB::table('lampiran_laporan')->insert($data['lampiran']);
        if (!empty($data['log']))        DB::table('log_laporans')->insert($data['log']);
    }
}