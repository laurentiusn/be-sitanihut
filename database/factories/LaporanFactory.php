<?php

namespace Database\Factories;

use App\Models\Laporan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Laporan>
 */
class LaporanFactory extends Factory
{
    protected $model = Laporan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bulanList = ['januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember'];

        return [
            'id' => Str::uuid()->toString(),
            'periode' => fake()->year(),
            'bulan' => fake()->randomElement($bulanList),
            'tanggal' => fake()->date(),
            'modal' => fake()->randomFloat(2, 500000, 5000000),
            'nte' => 0,
            'status' => 'belum diajukan',
            'id_user' => User::factory()->petani(),
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
            'tanggal_pengajuan' => in_array($status, ['menunggu', 'diverifikasi', 'disetujui', 'ditolak']) ? now() : null,
            'tanggal_keputusan' => in_array($status, ['disetujui', 'ditolak']) ? now() : null,
        ]);
    }
    public function withMasaTanamPanen(int $jumlahData = 1): static
    {
        return $this->afterCreating(function (Laporan $laporan) use ($jumlahData) {
            // Ambil komoditas acak, pastikan KomoditasSeeder sudah jalan
            $komoditas = DB::table('komoditas')->inRandomOrder()->first();
            
            $akumulasiNte = 0; // Variabel untuk menyimpan total NTE
            
            for ($i = 0; $i < $jumlahData; $i++) {
                DB::table('masa_tanam')->insert([
                    'id' => Str::uuid()->toString(),
                    'id_laporan' => $laporan->id,
                    'tanggal' => fake()->date(),
                    'id_komoditas' => $komoditas->id,
                    'usia_tanam' => fake()->randomFloat(2, 1, 6), // bulan
                    'jumlah' => fake()->randomFloat(2, 50, 200),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Hitung Masa Panen
                $hargaSatuan = fake()->randomFloat(2, 10000, 50000);
                $jumlahPanen = fake()->randomFloat(2, 100, 1000);
                $hargaTotal = $hargaSatuan * $jumlahPanen;
                
                // Tambahkan harga_total panen ini ke akumulasi NTE laporan
                $akumulasiNte += $hargaTotal;
                
                // Insert Masa Panen
                DB::table('masa_panen')->insert([
                    'id' => Str::uuid()->toString(),
                    'id_laporan' => $laporan->id,
                    'tanggal' => fake()->date(),
                    'id_komoditas' => $komoditas->id,
                    'harga_satuan' => $hargaSatuan,
                    'jumlah' => $jumlahPanen,
                    'harga_total' => $hargaTotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Setelah semua masa panen terbuat, update nilai NTE di Laporan
            $laporan->update([
                'nte' => $akumulasiNte
            ]);
        });
    }

    public function withLampiran(int $jumlahData = 1): static
    {
        return $this->afterCreating(function (Laporan $laporan) use ($jumlahData) {
            for ($i = 0; $i < $jumlahData; $i++) {
                $path = 'lampiran/dummy_file_' . Str::random(8) . '.pdf';
                Storage::disk('public')->put($path, 'dummy content');

                DB::table('lampiran_laporan')->insert([
                    'id' => Str::uuid()->toString(),
                    'id_laporan' => $laporan->id,
                    'nama_file' => 'dummy_file_' . ($i + 1) . '.pdf',
                    'path' => $path,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
