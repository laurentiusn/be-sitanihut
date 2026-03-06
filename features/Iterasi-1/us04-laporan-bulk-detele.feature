Feature: Laporan Bulk Delete
  Sebagai petani, saya ingin memperbarui banyak laporan dalam satu halaman,
  sehingga saya dapat memperbarui banyak laporan sekaligus

  Scenario: Berhasil menghapus banyak laporan dalam satu request
    Given Pengguna dengan peran petani login ke dalam sistem
    And Pengguna telah menentukan laporan-laporan yang ingin dihapus
    When Pengguna ingin menghapus banyak laporan secara bersamaan
    Then Sistem memberikan pesan sukses bahwa laporan berhasil dihapus

  Scenario: Tidak menyertakan laporan pada request
    Given Pengguna dengan peran petani login ke dalam sistem
    And Pengguna tidak menentukan laporan-laporan yang ingin dihapus
    When Pengguna ingin menghapus banyak laporan secara bersamaan
    Then Sistem menolak permintaan tersebut
    And Sistem memberikan pesan error terkait minimal laporan yang ingin dihapus

  Scenario: Menghapus laporan yang tidak ada
    Given Pengguna dengan peran petani login ke dalam sistem
    And Pengguna menyertakan laporan yang tidak ada di database
    When Pengguna ingin menghapus banyak laporan secara bersamaan
    Then Sistem memberikan pesan terkait laporan tidak ditemukan

  Scenario: Petani tidak bisa menghapus laporan petani lain
    Given Pengguna dengan peran petani login ke dalam sistem
    And Pengguna menentukan laporan-laporan yang ingin dihapus namun laporan milik petani lain
    When Pengguna ingin menghapus banyak laporan secara bersamaan
    Then Sistem memberikan pesan bahwa laporan tersebut bukan milik petani

  Scenario: Menghapus laporan yang sebelumnya sudah dihapus
    Given Pengguna dengan peran petani login ke dalam sistem
    And Pengguna menyertakan laporan yang sebelumnya sudah dihapus
    When Pengguna ingin menghapus banyak laporan secara bersamaan
    Then Sistem memberikan pesan terkait laporan sudah dihapus sebelumnya

  Scenario: Petani tidak bisa menghapus laporan yang statusnya tidak 'belum diajukan' atau 'ditolak'
    Given Pengguna dengan peran petani login ke dalam sistem
    And Pengguna menentukan laporan-laporan yang ingin dihapus namun laporan berstatus selain 'belum diajukan' atau 'ditolak'
    When Pengguna ingin menghapus banyak laporan secara bersamaan
    Then Sistem memberitau bahwa laporan tersebut sedang diproses

  Scenario: Terdapat laporan yang berhasil dihapus dan tidak dihapus
    Given Pengguna dengan peran petani login ke dalam sistem
    And Pengguna menentukan laporan-laporan yang ingin dihapus namun hanya beberapa laporan yang valid
    When Pengguna ingin menghapus banyak laporan secara bersamaan
    Then Sistem memberitau bahwa terdapat laporan yang berhasil dihapus dan laporan yang tidak berhasil dihapus