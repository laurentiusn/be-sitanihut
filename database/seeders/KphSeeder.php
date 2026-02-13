<?php

namespace Database\Seeders;

use App\Models\Kph;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KphSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kphs = [
            [
                'nama_kph' => 'Tahura WAR',
                'wilayah'  => 'Bandar Lampung, Pesawaran',
                'alamat'   => 'Jl. Raden Imba Kusuma Ratu No.21, Sukadana, Kec. Tj. Karang Barat, Kota Bandar Lampung',
                'nomor_wa' => '081367898876',
                'jenis'    => 'KPHK'
            ],
            [
                'nama_kph' => 'Pesisir Barat',
                'wilayah'  => 'Pesisir Barat',
                'alamat'   => 'Jl. Merdeka No.1, Pasar Krui, Kec. Pesisir Tengah, Kabupaten Pesisir Barat',
                'nomor_wa' => '081367898877',
                'jenis'    => 'KPHP'
            ],
            [
                'nama_kph' => 'Liwa',
                'wilayah'  => 'Lampung Barat',
                'alamat'   => 'Jl. Jend. Sudirman No.1, Pasar Liwa, Kec. Balik Bukit, Kabupaten Lampung Barat',
                'nomor_wa' => '086677887365',
                'jenis'    => 'KPHL'
            ],
            [
                'nama_kph' => 'Bukit Punggur',
                'wilayah'  => 'Way Kanan',
                'alamat'   => 'Jl. Lintas Sumatera, Kampung Bukit Punggur, Kec. Blambangan Umpu, Kabupaten Way Kanan',
                'nomor_wa' => '086677887366',
                'jenis'    => 'KPHP'
            ],
            [
                'nama_kph' => 'Muara Dua',
                'wilayah'  => 'Tulang Bawang, Way Kanan, Lampung Utara',
                'alamat'   => 'Jl. Raya Ethanol No.2, Kampung Muara Dua, Kec. Banjar Agung, Kabupaten Tulang Bawang',
                'nomor_wa' => '086677887367',
                'jenis'    => 'KPHL'
            ],
            [
                'nama_kph' => 'Sungai Buaya',
                'wilayah'  => 'Mesuji',
                'alamat'   => 'Jl. Lintas Timur Sumatera, Kampung Sungai Buaya, Kec. Rawajitu Utara, Kabupaten Mesuji',
                'nomor_wa' => '086677887368',
                'jenis'    => 'KPHP'
            ],
            [
                'nama_kph' => 'Way Terusan',
                'wilayah'  => 'Lampung Tengah',
                'alamat'   => 'Jl. Raya Terusan, Kampung Way Terusan, Kec. Seputih Banyak, Kabupaten Lampung Tengah',
                'nomor_wa' => '089376478928',
                'jenis'    => 'KPHL'
            ],
            [
                'nama_kph' => 'Way Waya',
                'wilayah'  => 'Lampung Tengah, Lampung Utara',
                'alamat'   => 'Jl. Lintas Sumatera, Kampung Way Waya, Kec. Abung Timur, Kabupaten Lampung Utara',
                'nomor_wa' => '082674856789',
                'jenis'    => 'KPHP'
            ],
            [
                'nama_kph' => 'Tangkit Teba',
                'wilayah'  => 'Lampung Tengah',
                'alamat'   => 'Jl. Raya Tangkit Teba, Kampung Tangkit Teba, Kec. Terbanggi Besar, Kabupaten Lampung Tengah',
                'nomor_wa' => '082674856790',
                'jenis'    => 'KPHL'
            ],
            [
                'nama_kph' => 'Batu Tegi',
                'wilayah'  => 'Tanggamus, Lampung Tengah, Lampung Barat, Pringsewu',
                'alamat'   => 'Jl. Waduk Batu Tegi, Kampung Batu Tegi, Kec. Air Naningan, Kabupaten Tanggamus',
                'nomor_wa' => '082674856791',
                'jenis'    => 'KPHP'
            ],
            [
                'nama_kph' => 'Kota Agung Utara',
                'wilayah'  => 'Tanggamus',
                'alamat'   => 'Jl. Raya Kota Agung, Kelurahan Kuripan, Kec. Kota Agung, Kabupaten Tanggamus',
                'nomor_wa' => '089376478929',
                'jenis'    => 'KPHL'
            ],
            [
                'nama_kph' => 'Pematang Neba',
                'wilayah'  => 'Tanggamus',
                'alamat'   => 'Jl. Raya Pematang Neba, Kampung Pematang Neba, Kec. Talang Padang, Kabupaten Tanggamus',
                'nomor_wa' => '089376478930',
                'jenis'    => 'KPHL'
            ],
            [
                'nama_kph' => 'Pesawaran',
                'wilayah'  => 'Pesawaran',
                'alamat'   => 'Jl. Raya Kedondong, Kampung Kedondong, Kec. Kedondong, Kabupaten Pesawaran',
                'nomor_wa' => '089376478931',
                'jenis'    => 'KPHP'
            ],
            [
                'nama_kph' => 'Gunung Balak',
                'wilayah'  => 'Lampung Timur',
                'alamat'   => 'Jl. Raya Gunung Balak, Kampung Gunung Balak, Kec. Sekampung Udik, Kabupaten Lampung Timur',
                'nomor_wa' => '083674892874',
                'jenis'    => 'KPHL'
            ],
            [
                'nama_kph' => 'Way Pisang',
                'wilayah'  => 'Lampung Selatan',
                'alamat'   => 'Jl. Raya Way Pisang, Kampung Way Pisang, Kec. Kalianda, Kabupaten Lampung Selatan',
                'nomor_wa' => '083674892875',
                'jenis'    => 'KPHP'
            ],
            [
                'nama_kph' => 'Batu Serampok',
                'wilayah'  => 'Bandar Lampung, Lampung Selatan',
                'alamat'   => 'Jl. Raya Lintas Sumatera, Kampung Batu Serampok, Kec. Natar, Kabupaten Lampung Selatan',
                'nomor_wa' => '083674892876',
                'jenis'    => 'KPHL'
            ],
            [
                'nama_kph' => 'Gedong Wani',
                'wilayah'  => 'Lampung Selatan, Lampung Timur',
                'alamat'   => 'Jl. Raya Gedong Wani, Kampung Gedong Wani, Kec. Jati Agung, Kabupaten Lampung Selatan',
                'nomor_wa' => '085627839825',
                'jenis'    => 'KPHL'
            ]
        ];

        foreach ($kphs as $kph) {
            Kph::create($kph);
        }
    }
}
