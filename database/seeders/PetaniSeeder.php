<?php

namespace Database\Seeders;

use App\Models\Kth;
use App\Models\Petani;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PetaniSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kth = Kth::where('nama_kth', 'KTH Wana Bakti Pesawaran')->first();
        if ($kth) {
            $petanis = [
                ['nama_petani' => 'Suparjo', 'nik' => '1809011005700001', 'jenis_kelamin' => 'pria', 'alamat' => 'Dusun I, Desa Hurun', 'nomor_wa' => '081211112221', 'pendidikan_terakhir' => 'SMP', 'pekerjaan_sampingan' => 'Buruh Harian', 'luas_lahan' => 1.5, 'id_kth' => $kth->id],
                ['nama_petani' => 'Siti Nurbaya', 'nik' => '1809015008750002', 'jenis_kelamin' => 'wanita', 'alamat' => 'Dusun II, Desa Hurun', 'nomor_wa' => '081211112222', 'pendidikan_terakhir' => 'SMA', 'pekerjaan_sampingan' => 'Ibu Rumah Tangga', 'luas_lahan' => 0.5, 'id_kth' => $kth->id],
                ['nama_petani' => 'Joko Santoso', 'nik' => '1809012003800003', 'jenis_kelamin' => 'pria', 'alamat' => 'Dusun I, Desa Hurun', 'nomor_wa' => '081211112223', 'pendidikan_terakhir' => 'SD', 'pekerjaan_sampingan' => 'Tukang Ojek', 'luas_lahan' => 1, 'id_kth' => $kth->id],
            ];
            foreach ($petanis as $petani) { Petani::create($petani); }
        }

        // 2. KTH Krui Lestari (2 Petani)
        $kth = Kth::where('nama_kth', 'KTH Krui Lestari')->first();
        if ($kth) {
            $petanis = [
                ['nama_petani' => 'Ahmad Dahlan', 'nik' => '1813013001680001', 'jenis_kelamin' => 'pria', 'alamat' => 'Jl. Pantai No. 10, Pekon Walur', 'nomor_wa' => '082122223331', 'pendidikan_terakhir' => 'SMA', 'pekerjaan_sampingan' => 'Nelayan', 'luas_lahan' => 2, 'id_kth' => $kth->id],
                ['nama_petani' => 'Cut Nyak Dien', 'nik' => '1813016012780003', 'jenis_kelamin' => 'wanita', 'alamat' => 'Jl. Pantai No. 12, Pekon Walur', 'nomor_wa' => '082122223333', 'pendidikan_terakhir' => 'SMA', 'pekerjaan_sampingan' => 'Pedagang', 'luas_lahan' => 1, 'id_kth' => $kth->id],
            ];
            foreach ($petanis as $petani) { Petani::create($petani); }
        }

        // 3. KTH Sumber Makmur Liwa (4 Petani)
        $kth = Kth::where('nama_kth', 'KTH Sumber Makmur Liwa')->first();
        if ($kth) {
            $petanis = [
                ['nama_petani' => 'Slamet Widodo', 'nik' => '1804031206650001', 'jenis_kelamin' => 'pria', 'alamat' => 'Dusun Makmur, Pekon Watas', 'nomor_wa' => '085733334441', 'pendidikan_terakhir' => 'SD', 'pekerjaan_sampingan' => 'Peternak Kopi', 'luas_lahan' => 2.5, 'id_kth' => $kth->id],
                ['nama_petani' => 'Sumiyati', 'nik' => '1804035503680002', 'jenis_kelamin' => 'wanita', 'alamat' => 'Dusun Makmur, Pekon Watas', 'nomor_wa' => '085733334442', 'pendidikan_terakhir' => 'SD', 'pekerjaan_sampingan' => 'Ibu Rumah Tangga', 'luas_lahan' => 2.5, 'id_kth' => $kth->id],
                ['nama_petani' => 'Teuku Umar', 'nik' => '1804030509850004', 'jenis_kelamin' => 'pria', 'alamat' => 'Dusun Sejahtera, Pekon Watas', 'nomor_wa' => '085733334443', 'pendidikan_terakhir' => 'SMP', 'pekerjaan_sampingan' => 'Tidak Ada', 'luas_lahan' => 1.5, 'id_kth' => $kth->id],
                ['nama_petani' => 'Fatmawati', 'nik' => '1804034507880005', 'jenis_kelamin' => 'wanita', 'alamat' => 'Dusun Sejahtera, Pekon Watas', 'nomor_wa' => '085733334444', 'pendidikan_terakhir' => 'D3', 'pekerjaan_sampingan' => 'Pegawai Swasta', 'luas_lahan' => 0.75, 'id_kth' => $kth->id],
            ];
            foreach ($petanis as $petani) { Petani::create($petani); }
        }

        // 4. KTH Way Kanan Sejahtera (5 Petani)
        $kph = Kth::where('nama_kth', 'KTH Way Kanan Sejahtera')->first();
        if ($kph) {
            $petanis = [
                ['nama_petani' => 'Gajah Mada', 'nik' => '1808040101800001', 'jenis_kelamin' => 'pria', 'alamat' => 'RT 01/RW 01, Kampung Gistang', 'nomor_wa' => '081344445551', 'pendidikan_terakhir' => 'SMA', 'pekerjaan_sampingan' => 'Wiraswasta', 'luas_lahan' => 1, 'id_kth' => $kph->id],
                ['nama_petani' => 'Hayam Wuruk', 'nik' => '1808040202810002', 'jenis_kelamin' => 'pria', 'alamat' => 'RT 01/RW 01, Kampung Gistang', 'nomor_wa' => '081344445552', 'pendidikan_terakhir' => 'SMP', 'pekerjaan_sampingan' => 'Buruh', 'luas_lahan' => 1, 'id_kth' => $kph->id],
                ['nama_petani' => 'Tribhuwana Wijayatunggadewi', 'nik' => '1808044303820003', 'jenis_kelamin' => 'wanita', 'alamat' => 'RT 02/RW 01, Kampung Gistang', 'nomor_wa' => '081344445553', 'pendidikan_terakhir' => 'SMA', 'pekerjaan_sampingan' => 'Pedagang', 'luas_lahan' => 0.5, 'id_kth' => $kph->id],
                ['nama_petani' => 'Raden Wijaya', 'nik' => '1808040404830004', 'jenis_kelamin' => 'pria', 'alamat' => 'RT 02/RW 01, Kampung Gistang', 'nomor_wa' => '081344445554', 'pendidikan_terakhir' => 'S1', 'pekerjaan_sampingan' => 'Guru Honorer', 'luas_lahan' => 2, 'id_kth' => $kph->id],
                ['nama_petani' => 'Gayatri Rajapatni', 'nik' => '1808044505840005', 'jenis_kelamin' => 'wanita', 'alamat' => 'RT 03/RW 02, Kampung Gistang', 'nomor_wa' => '081344445555', 'pendidikan_terakhir' => 'SMA', 'pekerjaan_sampingan' => 'Tidak Ada', 'luas_lahan' => 1.2, 'id_kth' => $kph->id],
            ];
            foreach ($petanis as $petani) { Petani::create($petani); }
        }

        // 5. KTH Tani Jaya Tulang Bawang (3 Petani)
        $kph = Kth::where('nama_kth', 'KTH Tani Jaya Tulang Bawang')->first();
        if ($kph) {
            $petanis = [
                ['nama_petani' => 'Sultan Hasanuddin', 'nik' => '1805050808870001', 'jenis_kelamin' => 'pria', 'alamat' => 'Dusun Jaya, Kampung Warga Makmur Jaya', 'nomor_wa' => '089855556661', 'pendidikan_terakhir' => 'SMP', 'pekerjaan_sampingan' => 'Wiraswasta', 'luas_lahan' => 2, 'id_kth' => $kph->id],
                ['nama_petani' => 'Pattimura', 'nik' => '1805051010890003', 'jenis_kelamin' => 'pria', 'alamat' => 'Dusun Makmur, Kampung Warga Makmur Jaya', 'nomor_wa' => '089855556663', 'pendidikan_terakhir' => 'SD', 'pekerjaan_sampingan' => 'Buruh', 'luas_lahan' => 1.5, 'id_kth' => $kph->id],
                ['nama_petani' => 'Martha Christina Tiahahu', 'nik' => '1805055111900004', 'jenis_kelamin' => 'wanita', 'alamat' => 'Dusun Makmur, Kampung Warga Makmur Jaya', 'nomor_wa' => '089855556664', 'pendidikan_terakhir' => 'SMP', 'pekerjaan_sampingan' => 'Pedagang Kecil', 'luas_lahan' => 1, 'id_kth' => $kph->id],
            ];
            foreach ($petanis as $petani) { Petani::create($petani); }
        }

        // 6. KTH Rawa Jitu Lestari (2 Petani)
        $kth = Kth::where('nama_kth', 'KTH Rawa Jitu Lestari')->first();
        if ($kth) {
            $petanis = [
                ['nama_petani' => 'Tuanku Imam Bonjol', 'nik' => '1811011212700001', 'jenis_kelamin' => 'pria', 'alamat' => 'Jl. Dipasena No. 1', 'nomor_wa' => '081166667771', 'pendidikan_terakhir' => 'S1', 'pekerjaan_sampingan' => 'Pengusaha Tambak', 'luas_lahan' => 5, 'id_kth' => $kth->id],
                ['nama_petani' => 'Pangeran Diponegoro', 'nik' => '1811011301710002', 'jenis_kelamin' => 'pria', 'alamat' => 'Jl. Dipasena No. 2', 'nomor_wa' => '081166667772', 'pendidikan_terakhir' => 'S3', 'pekerjaan_sampingan' => 'Tokoh Masyarakat', 'luas_lahan' => 2, 'id_kth' => $kth->id],
            ];
            foreach ($petanis as $petani) { Petani::create($petani); }
        }

        // 7. KTH Seputih Banyak Makmur (4 Petani)
        $kth = Kth::where('nama_kth', 'KTH Seputih Banyak Makmur')->first();
        if ($kth) {
            $petanis = [
                ['nama_petani' => 'Ki Hajar Dewantara', 'nik' => '1805010101880001', 'jenis_kelamin' => 'pria', 'alamat' => 'Kampung Tanjung Harapan', 'nomor_wa' => '081177778881', 'pendidikan_terakhir' => 'SMA', 'pekerjaan_sampingan' => 'Guru', 'luas_lahan' => 2, 'id_kth' => $kth->id],
                ['nama_petani' => 'R.A. Kartini', 'nik' => '1805014202890002', 'jenis_kelamin' => 'wanita', 'alamat' => 'Kampung Tanjung Harapan', 'nomor_wa' => '081177778882', 'pendidikan_terakhir' => 'SMP', 'pekerjaan_sampingan' => 'Penulis', 'luas_lahan' => 1, 'id_kth' => $kth->id],
                ['nama_petani' => 'Mohammad Hatta', 'nik' => '1805010303900003', 'jenis_kelamin' => 'pria', 'alamat' => 'Kampung Tanjung Harapan', 'nomor_wa' => '081177778883', 'pendidikan_terakhir' => 'S2', 'pekerjaan_sampingan' => 'Tidak Ada', 'luas_lahan' => 1.5, 'id_kth' => $kth->id],
                ['nama_petani' => 'Soekarno', 'nik' => '1805010404910004', 'jenis_kelamin' => 'pria', 'alamat' => 'Kampung Tanjung Harapan', 'nomor_wa' => '081177778884', 'pendidikan_terakhir' => 'Ir.', 'pekerjaan_sampingan' => 'Proklamator', 'luas_lahan' => 10, 'id_kth' => $kth->id],
            ];
            foreach ($petanis as $petani) { Petani::create($petani); }
        }

        // 8. KTH Abung Timur Bersatu (3 Petani)
        $kth = Kth::where('nama_kth', 'KTH Abung Timur Bersatu')->first();
        if ($kth) {
            $petanis = [
                ['nama_petani' => 'Jenderal Soedirman', 'nik' => '1803010404910001', 'jenis_kelamin' => 'pria', 'alamat' => 'Desa Papan Rejo', 'nomor_wa' => '081188889991', 'pendidikan_terakhir' => 'S1', 'pekerjaan_sampingan' => 'Pensiunan', 'luas_lahan' => 5, 'id_kth' => $kth->id],
                ['nama_petani' => 'Bung Tomo', 'nik' => '1803010606930003', 'jenis_kelamin' => 'pria', 'alamat' => 'Desa Papan Rejo', 'nomor_wa' => '081188889993', 'pendidikan_terakhir' => 'SMP', 'pekerjaan_sampingan' => 'Buruh', 'luas_lahan' => 1, 'id_kth' => $kth->id],
                ['nama_petani' => 'Sutan Sjahrir', 'nik' => '1803010707940004', 'jenis_kelamin' => 'pria', 'alamat' => 'Desa Papan Rejo', 'nomor_wa' => '081188889994', 'pendidikan_terakhir' => 'SD', 'pekerjaan_sampingan' => 'Buruh', 'luas_lahan' => 1, 'id_kth' => $kth->id],
            ];
            foreach ($petanis as $petani) { Petani::create($petani); }
        }

        // 9. KTH Terbanggi Besar Hijau (5 Petani)
        $kth = Kth::where('nama_kth', 'KTH Terbanggi Besar Hijau')->first();
        if ($kth) {
            $petanis = [
                ['nama_petani' => 'Agus Kuncoro', 'nik' => '1805020101800001', 'jenis_kelamin' => 'pria', 'alamat' => 'Bandar Jaya', 'nomor_wa' => '081344445551', 'pendidikan_terakhir' => 'SMA', 'pekerjaan_sampingan' => 'Aktor', 'luas_lahan' => 1, 'id_kth' => $kth->id],
                ['nama_petani' => 'Christine Hakim', 'nik' => '1805024303820003', 'jenis_kelamin' => 'wanita', 'alamat' => 'Bandar Jaya', 'nomor_wa' => '081344445553', 'pendidikan_terakhir' => 'SMA', 'pekerjaan_sampingan' => 'Aktris', 'luas_lahan' => 0.5, 'id_kth' => $kth->id],
                ['nama_petani' => 'Deddy Mizwar', 'nik' => '1805020404830004', 'jenis_kelamin' => 'pria', 'alamat' => 'Bandar Jaya', 'nomor_wa' => '081344445554', 'pendidikan_terakhir' => 'S1', 'pekerjaan_sampingan' => 'Sutradara', 'luas_lahan' => 2, 'id_kth' => $kth->id],
                ['nama_petani' => 'Eva Celia', 'nik' => '1805024505840005', 'jenis_kelamin' => 'wanita', 'alamat' => 'Bandar Jaya', 'nomor_wa' => '081344445555', 'pendidikan_terakhir' => 'SMA', 'pekerjaan_sampingan' => 'Penyanyi', 'luas_lahan' => 1.2, 'id_kth' => $kth->id],
                ['nama_petani' => 'Iko Uwais', 'nik' => '1805020606850006', 'jenis_kelamin' => 'pria', 'alamat' => 'Bandar Jaya', 'nomor_wa' => '081344445556', 'pendidikan_terakhir' => 'SMP', 'pekerjaan_sampingan' => 'Aktor Laga', 'luas_lahan' => 1.8, 'id_kth' => $kth->id],
            ];
            foreach ($petanis as $petani) { Petani::create($petani); }
        }

        // 10. KTH Air Naningan Asri (4 Petani)
        $kth = Kth::where('nama_kth', 'KTH Air Naningan Asri')->first();
        if ($kth) {
            $petanis = [
                ['nama_petani' => 'Jajang C. Noer', 'nik' => '1802050808870001', 'jenis_kelamin' => 'wanita', 'alamat' => 'Pekon Datar Lebuay', 'nomor_wa' => '089855556661', 'pendidikan_terakhir' => 'SMP', 'pekerjaan_sampingan' => 'Aktris Senior', 'luas_lahan' => 2, 'id_kth' => $kth->id],
                ['nama_petani' => 'Lukman Sardi', 'nik' => '1802051010890003', 'jenis_kelamin' => 'pria', 'alamat' => 'Pekon Datar Lebuay', 'nomor_wa' => '089855556663', 'pendidikan_terakhir' => 'SD', 'pekerjaan_sampingan' => 'Aktor', 'luas_lahan' => 1.5, 'id_kth' => $kth->id],
                ['nama_petani' => 'Marsha Timothy', 'nik' => '1802055111900004', 'jenis_kelamin' => 'wanita', 'alamat' => 'Pekon Datar Lebuay', 'nomor_wa' => '089855556664', 'pendidikan_terakhir' => 'SMP', 'pekerjaan_sampingan' => 'Aktris', 'luas_lahan' => 1, 'id_kth' => $kth->id],
                ['nama_petani' => 'Oka Antara', 'nik' => '1802051212910005', 'jenis_kelamin' => 'pria', 'alamat' => 'Pekon Datar Lebuay', 'nomor_wa' => '089855556665', 'pendidikan_terakhir' => 'S1', 'pekerjaan_sampingan' => 'Aktor', 'luas_lahan' => 3, 'id_kth' => $kth->id],
            ];
            foreach ($petanis as $petani) { Petani::create($petani); }
        }

        // 11. KTH Talang Padang Jaya (2 Petani)
        $kth = Kth::where('nama_kth', 'KTH Talang Padang Jaya')->first();
        if ($kth) {
            $petanis = [
                ['nama_petani' => 'Prisia Nasution', 'nik' => '1802061212700001', 'jenis_kelamin' => 'wanita', 'alamat' => 'Pekon Sinar Banten', 'nomor_wa' => '081166667771', 'pendidikan_terakhir' => 'S1', 'pekerjaan_sampingan' => 'Aktris', 'luas_lahan' => 5, 'id_kth' => $kth->id],
                ['nama_petani' => 'Rio Dewanto', 'nik' => '1802061301710002', 'jenis_kelamin' => 'pria', 'alamat' => 'Pekon Sinar Banten', 'nomor_wa' => '081166667772', 'pendidikan_terakhir' => 'S1', 'pekerjaan_sampingan' => 'Aktor', 'luas_lahan' => 2, 'id_kth' => $kth->id],
            ];
            foreach ($petanis as $petani) { Petani::create($petani); }
        }

        // 12. KTH Kedondong Rindang (3 Petani)
        $kth = Kth::where('nama_kth', 'KTH Kedondong Rindang')->first();
        if ($kth) {
            $petanis = [
                ['nama_petani' => 'Slamet Rahardjo', 'nik' => '1809020101880001', 'jenis_kelamin' => 'pria', 'alamat' => 'Desa Pasar Baru', 'nomor_wa' => '081177778881', 'pendidikan_terakhir' => 'SMA', 'pekerjaan_sampingan' => 'Aktor Senior', 'luas_lahan' => 2, 'id_kth' => $kth->id],
                ['nama_petani' => 'Tio Pakusadewo', 'nik' => '1809020303900003', 'jenis_kelamin' => 'pria', 'alamat' => 'Desa Pasar Baru', 'nomor_wa' => '081177778883', 'pendidikan_terakhir' => 'SD', 'pekerjaan_sampingan' => 'Aktor', 'luas_lahan' => 1.5, 'id_kth' => $kth->id],
                ['nama_petani' => 'Widyawati', 'nik' => '1809024202890002', 'jenis_kelamin' => 'wanita', 'alamat' => 'Desa Pasar Baru', 'nomor_wa' => '081177778882', 'pendidikan_terakhir' => 'SMP', 'pekerjaan_sampingan' => 'Aktris Senior', 'luas_lahan' => 1, 'id_kth' => $kth->id],
            ];
            foreach ($petanis as $petani) { Petani::create($petani); }
        }

        // 13. KTH Sekampung Udik Mandiri (4 Petani)
        $kth = Kth::where('nama_kth', 'KTH Sekampung Udik Mandiri')->first();
        if ($kth) {
            $petanis = [
                ['nama_petani' => 'Yayan Ruhian', 'nik' => '1807010404910001', 'jenis_kelamin' => 'pria', 'alamat' => 'Desa Gunung Pasir Jaya', 'nomor_wa' => '081188889991', 'pendidikan_terakhir' => 'S1', 'pekerjaan_sampingan' => 'Aktor Laga', 'luas_lahan' => 5, 'id_kth' => $kth->id],
                ['nama_petani' => 'Adipati Dolken', 'nik' => '1807010606930003', 'jenis_kelamin' => 'pria', 'alamat' => 'Desa Gunung Pasir Jaya', 'nomor_wa' => '081188889993', 'pendidikan_terakhir' => 'SMP', 'pekerjaan_sampingan' => 'Aktor', 'luas_lahan' => 1, 'id_kth' => $kth->id],
                ['nama_petani' => 'Ario Bayu', 'nik' => '1807010707940004', 'jenis_kelamin' => 'pria', 'alamat' => 'Desa Gunung Pasir Jaya', 'nomor_wa' => '081188889994', 'pendidikan_terakhir' => 'SD', 'pekerjaan_sampingan' => 'Aktor', 'luas_lahan' => 1, 'id_kth' => $kth->id],
                ['nama_petani' => 'Chelsea Islan', 'nik' => '1807014505920002', 'jenis_kelamin' => 'wanita', 'alamat' => 'Desa Gunung Pasir Jaya', 'nomor_wa' => '081188889992', 'pendidikan_terakhir' => 'SMA', 'pekerjaan_sampingan' => 'Aktris', 'luas_lahan' => 2, 'id_kth' => $kth->id],
            ];
            foreach ($petanis as $petani) { Petani::create($petani); }
        }

        // 14. KTH Kalianda Hijau Lestari (2 Petani)
        $kth = Kth::where('nama_kth', 'KTH Kalianda Hijau Lestari')->first();
        if ($kth) {
            $petanis = [
                ['nama_petani' => 'Chicco Jerikho', 'nik' => '1801020101800001', 'jenis_kelamin' => 'pria', 'alamat' => 'Desa Maja', 'nomor_wa' => '081344445551', 'pendidikan_terakhir' => 'SMA', 'pekerjaan_sampingan' => 'Aktor', 'luas_lahan' => 1, 'id_kth' => $kth->id],
                ['nama_petani' => 'Dian Pelangi', 'nik' => '1801024303820003', 'jenis_kelamin' => 'wanita', 'alamat' => 'Desa Maja', 'nomor_wa' => '081344445553', 'pendidikan_terakhir' => 'SMA', 'pekerjaan_sampingan' => 'Desainer', 'luas_lahan' => 0.5, 'id_kth' => $kth->id],
            ];
            foreach ($petanis as $petani) { Petani::create($petani); }
        }

        // 15. KTH Natar Sejuk (5 Petani)
        $kth = Kth::where('nama_kth', 'KTH Natar Sejuk')->first();
        if ($kth) {
            $petanis = [
                ['nama_petani' => 'Fachri Albar', 'nik' => '1801030404830004', 'jenis_kelamin' => 'pria', 'alamat' => 'Desa Merak Batin', 'nomor_wa' => '081344445554', 'pendidikan_terakhir' => 'S1', 'pekerjaan_sampingan' => 'Aktor', 'luas_lahan' => 2, 'id_kth' => $kth->id],
                ['nama_petani' => 'Hannah Al Rashid', 'nik' => '1801034505840005', 'jenis_kelamin' => 'wanita', 'alamat' => 'Desa Merak Batin', 'nomor_wa' => '081344445555', 'pendidikan_terakhir' => 'SMA', 'pekerjaan_sampingan' => 'Aktris', 'luas_lahan' => 1.2, 'id_kth' => $kth->id],
                ['nama_petani' => 'Herjunot Ali', 'nik' => '1801030606850006', 'jenis_kelamin' => 'pria', 'alamat' => 'Desa Merak Batin', 'nomor_wa' => '081344445556', 'pendidikan_terakhir' => 'SMP', 'pekerjaan_sampingan' => 'Aktor', 'luas_lahan' => 1.8, 'id_kth' => $kth->id],
                ['nama_petani' => 'Laudya Cynthia Bella', 'nik' => '1801034707860007', 'jenis_kelamin' => 'wanita', 'alamat' => 'Desa Merak Batin', 'nomor_wa' => '081344445557', 'pendidikan_terakhir' => 'S1', 'pekerjaan_sampingan' => 'Aktris', 'luas_lahan' => 4, 'id_kth' => $kth->id],
                ['nama_petani' => 'Revalina S. Temat', 'nik' => '1801034808870008', 'jenis_kelamin' => 'wanita', 'alamat' => 'Desa Merak Batin', 'nomor_wa' => '081344445558', 'pendidikan_terakhir' => 'SMA', 'pekerjaan_sampingan' => 'Aktris', 'luas_lahan' => 1, 'id_kth' => $kth->id],
            ];
            foreach ($petanis as $petani) { Petani::create($petani); }
        }

        // 16. KTH Jati Agung Wana Raya (3 Petani)
        $kth = Kth::where('nama_kth', 'KTH Jati Agung Wana Raya')->first();
        if ($kth) {
            $petanis = [
                ['nama_petani' => 'Vino G. Bastian', 'nik' => '1801040808870001', 'jenis_kelamin' => 'pria', 'alamat' => 'Desa Karang Anyar', 'nomor_wa' => '089855556661', 'pendidikan_terakhir' => 'SMP', 'pekerjaan_sampingan' => 'Aktor', 'luas_lahan' => 2, 'id_kth' => $kth->id],
                ['nama_petani' => 'Wulan Guritno', 'nik' => '1801044909880002', 'jenis_kelamin' => 'wanita', 'alamat' => 'Desa Karang Anyar', 'nomor_wa' => '089855556662', 'pendidikan_terakhir' => 'SMA', 'pekerjaan_sampingan' => 'Aktris', 'luas_lahan' => 1, 'id_kth' => $kth->id],
                ['nama_petani' => 'Yoshi Sudarso', 'nik' => '1801041010890003', 'jenis_kelamin' => 'pria', 'alamat' => 'Desa Karang Anyar', 'nomor_wa' => '089855556663', 'pendidikan_terakhir' => 'SD', 'pekerjaan_sampingan' => 'Aktor', 'luas_lahan' => 1.5, 'id_kth' => $kth->id],
            ];
            foreach ($petanis as $petani) { Petani::create($petani); }
        }

        // 17. KTHs di UPTD KPH Kota Agung Utara
        $kthNames = ['KTH Curah Sejuk', 'KTH Tanggamus Lestari', 'KTH Baker Lestari', 'KTH Rilau Jaya 1', 'KTH Rilau Jaya 2'];
        $petaniCounter = 1;
        foreach ($kthNames as $kthName) {
            $kth = Kth::where('nama_kth', $kthName)->first();
            if ($kth) {
                $jumlahPetani = rand(2, 5); // Jumlah petani bervariasi untuk setiap KTH di sini
                $petanis = [];
                for ($i = 1; $i <= $jumlahPetani; $i++) {
                    $petanis[] = [
                        'nama_petani' => 'Petani ' . $kth->kabupaten . ' ' . $petaniCounter,
                        'nik' => '180299' . str_pad($petaniCounter, 10, '0', STR_PAD_LEFT),
                        'jenis_kelamin' => ($i % 2 == 0) ? 'wanita' : 'pria',
                        'alamat' => 'Dusun ' . $i . ', ' . $kth->desa,
                        'nomor_wa' => '0852' . str_pad($petaniCounter, 8, '0', STR_PAD_LEFT),
                        'pendidikan_terakhir' => 'SMA',
                        'pekerjaan_sampingan' => 'Wiraswasta',
                        'luas_lahan' => 1.0 + ($i * 0.2),
                        'id_kth' => $kth->id,
                    ];
                    $petaniCounter++;
                }
                foreach ($petanis as $petani) { Petani::create($petani); }
            }
        }
    }
}