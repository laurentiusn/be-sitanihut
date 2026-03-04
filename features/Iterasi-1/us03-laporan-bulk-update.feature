Feature: Laporan Bulk Update
  Sebagai pengguna dengan peran petani
  Saya ingin memperbarui banyak laporan sekaligus
  Agar proses perbaikan data laporan lebih efisien

  Scenario: Berhasil mengupdate banyak laporan dalam satu request
    Given Pengguna dengan peran petani login ke dalam sistem
    And Pengguna telah mengisi data-data pada laporan-laporan yang ingin diperbarui
    When Pengguna ingin memperbarui banyak laporan secara bersamaan
    Then Sistem memberikan pesan sukses bahwa semua laporan berhasil diperbarui

  Scenario: Tidak menyertakan laporan pada request
    Given Pengguna dengan peran petani login ke dalam sistem
    And Pengguna tidak menentukan laporan-laporan yang ingin diperbarui
    When Pengguna ingin memperbarui banyak laporan secara bersamaan
    Then Sistem menolak permintaan tersebut
    And Sistem memberikan pesan error terkait minimal laporan untuk melakukan pembaruan

  Scenario: Data laporan tidak valid
    Given Pengguna dengan peran petani login ke dalam sistem
    And Pengguna menyertakan data yang tidak valid pada laporan-laporan yang ingin diperbarui
    When Pengguna ingin memperbarui banyak laporan secara bersamaan
    Then Sistem menolak permintaan tersebut
    And Sistem memberikan pesan error terkait data yang dimasukan tidak valid

  Scenario: Petani tidak bisa memperbarui laporan petani lain
    Given Pengguna dengan peran petani login ke dalam sistem
    And Laporan yang ingin diperbarui milik petani lain
    When Pengguna ingin memperbarui banyak laporan secara bersamaan
    Then Sistem memberitau bahwa laporan tersebut bukan milik si petani

  Scenario: Tidak bisa memperbarui laporan yang tidak ada
    Given Pengguna dengan peran petani login ke dalam sistem
    And Pengguna menyertakan laporan yang tidak ada di sistem
    When Pengguna ingin memperbarui banyak laporan secara bersamaan
    Then Sistem memberikan pesan terkait laporan tidak ada

  Scenario: Petani tidak bisa memperbarui laporan yang status nya tidak 'belum diajukan' atau 'ditolak'
    Given Pengguna dengan peran petani login ke dalam sistem
    And Laporan yang ingin diperbarui berstatus selain 'belum diajukan' atau 'ditolak'
    When Pengguna ingin memperbarui banyak laporan secara bersamaan
    Then Sistem memberitau bahwa laporan tersebut sedang diproses

  Scenario: Terdapat laporan yang berhasil diperbarui dan tidak berhasil diperbarui
    Given Pengguna dengan peran petani login ke dalam sistem
    And Pengguna menentukan laporan-laporan yang ingin diperbarui namun hanya beberapa laporan yang valid
    When Pengguna ingin memperbarui banyak laporan secara bersamaan
    Then Sistem memberitau bahwa terdapat laporan yang berhasil diperbarui dan laporan yang tidak berhasil diperbarui