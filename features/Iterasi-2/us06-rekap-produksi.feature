Feature: Produksi pada Rekapitulasi NTE
  Sebagai sekretariat dan auditor, 
  saya ingin melihat informasi terkait produksi pada halaman rekapitulasi nilai transaksi ekonomi, 
  sehingga saya dapat mengetahui produksi yang dikelola oleh petani

  Scenario: Melihat data produksi tiap KPH
    Given Pengguna dengan peran sekretariat login untuk melihat rekapitulasi
    When Pengguna ingin melihat data produksi tiap KPH pada Rekapitulasi NTE
    Then Sistem berhasil menampilkan Rekap
    And Terdapat data produksi tiap KPH

  Scenario: Melihat data produksi tiap KTH
    Given Pengguna dengan peran auditor login untuk melihat rekapitulasi
    When Pengguna ingin melihat data produksi tiap KTH pada Rekapitulasi NTE
    Then Sistem berhasil menampilkan Rekap
    And Terdapat data produksi tiap KTH

  Scenario: Melihat data produksi tiap Petani
    Given Pengguna dengan peran sekretariat login untuk melihat rekapitulasi
    When Pengguna ingin melihat data produksi tiap Petani pada Rekapitulasi NTE
    Then Sistem berhasil menampilkan Rekap
    And Terdapat data produksi tiap Petani

  Scenario: Melihat data produksi tiap Komoditas
    Given Pengguna dengan peran sekretariat login untuk melihat rekapitulasi
    When Pengguna ingin melihat data produksi tiap Komoditas pada Rekapitulasi NTE
    Then Sistem berhasil menampilkan Rekap
    And Terdapat data produksi tiap Komoditas