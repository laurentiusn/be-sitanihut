Feature: Manajemen Data User Penyuluh
  Sebagai sekretariat, auditor, dan penanggung jawab,
  saya ingin melihat daftar penyuluh dan data lengkap penyuluh,
  sehingga saya dapat mengetahui data penyuluh

  Scenario: Sekretariat berhasil melihat list penyuluh
    Given Pengguna dengan peran sekretariat login untuk melihat akun penyuluh
    When Pengguna ingin melihat list penyuluh
    Then Sistem memberikan pesan sukses list penyuluh
    And Terdapat data list penyuluh

  Scenario: Auditor berhasil melihat list penyuluh dengan filter
    Given Pengguna dengan peran auditor login untuk melihat akun penyuluh
    And Pengguna memasukkan kata kunci untuk list penyuluh
    And Pengguna ingin melihat penyuluh yang berjenis kelamin pria
    And Pengguna ingin list penyuluh diurutkan berdasarkan nama penyuluh
    And Pengguna ingin list penyuluh diurutkan secara descending
    When Pengguna ingin melihat list penyuluh
    Then Sistem memberikan pesan sukses list penyuluh
    And Terdapat data list penyuluh

  Scenario: Penanggung jawab berhasil melihat list penyuluh
    Given Pengguna dengan peran penanggung jawab login untuk melihat akun penyuluh
    When Pengguna ingin melihat list penyuluh
    Then Sistem memberikan pesan sukses list penyuluh
    And Terdapat data list penyuluh
    And Penyuluh yang berada di KPH yang sama dengan penanggung jawab

  Scenario: Sekretariat berhasil melihat detail penyuluh
    Given Pengguna dengan peran sekretariat login untuk melihat akun penyuluh
    When Pengguna ingin melihat detail akun penyuluh
    Then Sistem memberikan pesan sukses detail penyuluh
    And Terdapat data detail penyuluh

  Scenario: Auditor berhasil melihat detail penyuluh
    Given Pengguna dengan peran auditor login untuk melihat akun penyuluh
    When Pengguna ingin melihat detail akun penyuluh
    Then Sistem memberikan pesan sukses detail penyuluh
    And Terdapat data detail penyuluh

  Scenario: Penanggung jawab berhasil melihat detail penyuluh
    Given Pengguna dengan peran penanggung jawab login untuk melihat akun penyuluh
    When Pengguna ingin melihat detail akun penyuluh
    Then Sistem memberikan pesan sukses detail penyuluh
    And Terdapat data detail penyuluh

  Scenario: Gagal melihat list penyuluh karena filter tidak valid
    Given Pengguna dengan peran auditor login untuk melihat akun penyuluh
    And Pengguna memasukkan kata kunci untuk list penyuluh yang sangat panjang
    And Pengguna ingin melihat penyuluh yang berjenis kelamin selain pria dan wanita
    And Pengguna ingin list penyuluh diurutkan berdasarkan nomor induk
    And Pengguna ingin list penyuluh diurutkan selain ascending dan descending
    When Pengguna ingin melihat list penyuluh
    Then Sistem memberikan pesan error terkait validasi request list penyuluh yang tidak valid

  Scenario: Gagal melihat list penyuluh karena tidak ada hak akses
    Given Pengguna dengan peran selain sekretariat, auditor, dan penanggung jawab login untuk melihat akun penyuluh
    When Pengguna ingin melihat list penyuluh
    Then Sistem memberikan pesan error terkait hak akses melihat list penyuluh

  Scenario: Gagal melihat detail penyuluh karena tidak ada hak akses
    Given Pengguna dengan peran selain sekretariat, auditor, dan penanggung jawab login untuk melihat akun penyuluh
    When Pengguna ingin melihat detail akun penyuluh
    Then Sistem memberikan pesan error terkait hak akses melihat detail penyuluh