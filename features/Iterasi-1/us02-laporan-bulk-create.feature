Feature: Laporan Bulk Create
  Sebagai petani, saya ingin membuat banyak laporan dalam satu halaman, sehingga saya bisa membuat banyak laporan sekaligus

  Scenario: Berhasil membuat banyak laporan dalam satu request
    Given Pengguna dengan peran petani login ke dalam sistem
    And Pengguna telah menyiapkan data-data laporan
    When Pengguna ingin membuat banyak laporan secara bersamaan
    Then Sistem memberikan pesan sukses bahwa beberapa laporan berhasil ditambahkan

  Scenario: Gagal mengirimkan laporan karena formulir kosong
    Given Pengguna dengan peran petani login ke dalam sistem
    And Pengguna tidak memiliki data-data laporan
    When Pengguna ingin membuat banyak laporan secara bersamaan
    Then Sistem menolak permintaan tersebut
    And Sistem memberikan pesan error terkait minimal laporan untuk dibuat

  Scenario: Gagal membuat banyak laporan karena salah satu data tidak valid
    Given Pengguna dengan peran petani login ke dalam sistem
    And Pengguna memiliki data-data laporan namun ada yang tidak valid
    When Pengguna ingin membuat banyak laporan secara bersamaan
    Then Sistem menolak permintaan tersebut
    And Sistem memberikan pesan error terkait data yang tidak valid

  Scenario: Gagal membuat laporan karena melanggar aturan pembuatan laporan
    Given Pengguna dengan peran petani login ke dalam sistem
    And Pengguna memiliki data-data laporan dan menyertakan lebih dari 10 lampiran
    When Pengguna ingin membuat banyak laporan secara bersamaan
    Then Sistem menolak permintaan tersebut
    And Sistem memberikan pesan error terkait batas maksimal lampiran

  Scenario: Gagal membuat banyak laporan karena pengguna tidak memiliki izin
    Given Pengguna dengan peran bukan petani login ke dalam sistem
    And Pengguna telah menyiapkan data-data laporan
    When Pengguna ingin membuat banyak laporan secara bersamaan
    Then Sistem memberikan pesan error terkait hak akses pembuatan laporan