Feature: Manajemen Data User Petani
  Sebagai sekretariat, auditor, penanggung jawab, dan penyuluh, 
  saya ingin melihat daftar petani dan data lengkap petani, sehingga saya dapat mengetahui data petani

  Scenario: Sekretariat berhasil melihat list petani
    Given Pengguna dengan peran sekretariat login untuk melihat akun petani
    When Pengguna ingin melihat list petani
    Then Sistem memberikan pesan sukses list petani
    And Terdapat data list petani

  Scenario: Auditor berhasil melihat list petani dengan filter
    Given Pengguna dengan peran auditor login untuk melihat akun petani
    And Pengguna memasukkan kata kunci untuk list petani
    And Pengguna ingin melihat petani yang berjenis kelamin pria
    And Pengguna ingin list petani diurutkan berdasarkan nama petani
    And Pengguna ingin list petani diurutkan secara descending
    When Pengguna ingin melihat list petani
    Then Sistem memberikan pesan sukses list petani
    And Terdapat data list petani

  Scenario: Penanggung jawab berhasil melihat list petani
    Given Pengguna dengan peran penanggung jawab login untuk melihat akun petani
    When Pengguna ingin melihat list petani
    Then Sistem memberikan pesan sukses list petani
    And Terdapat data list petani
    And Petani berada di KPH yang sama dengan penanggung jawab

  Scenario: Penyuluh berhasil melihat list petani
    Given Pengguna dengan peran penyuluh login untuk melihat akun petani
    When Pengguna ingin melihat list petani
    Then Sistem memberikan pesan sukses list petani
    And Terdapat data list petani
    And Petani berada di KTH yang diawasi oleh penyuluh

  Scenario: Sekretariat berhasil melihat detail petani
    Given Pengguna dengan peran sekretariat login untuk melihat akun petani
    When Pengguna ingin melihat detail akun petani
    Then Sistem memberikan pesan sukses detail petani
    And Terdapat data detail petani

  Scenario: Auditor berhasil melihat detail petani
    Given Pengguna dengan peran auditor login untuk melihat akun petani
    When Pengguna ingin melihat detail akun petani
    Then Sistem memberikan pesan sukses detail petani
    And Terdapat data detail petani

  Scenario: Penanggung jawab berhasil melihat detail petani
    Given Pengguna dengan peran penanggung jawab login untuk melihat akun petani
    When Pengguna ingin melihat detail akun petani
    Then Sistem memberikan pesan sukses detail petani
    And Terdapat data detail petani

  Scenario: Penyuluh berhasil melihat detail petani
    Given Pengguna dengan peran penyuluh login untuk melihat akun petani
    When Pengguna ingin melihat detail akun petani
    Then Sistem memberikan pesan sukses detail petani
    And Terdapat data detail petani

  Scenario: Gagal melihat list petani karena filter tidak valid
    Given Pengguna dengan peran auditor login untuk melihat akun petani
    And Pengguna memasukkan kata kunci untuk list petani yang sangat panjang
    And Pengguna ingin melihat petani yang berjenis kelamin selain pria dan wanita
    And Pengguna ingin list petani diurutkan berdasarkan nomor induk
    And Pengguna ingin list petani diurutkan selain ascending dan descending
    When Pengguna ingin melihat list petani
    Then Sistem memberikan pesan error terkait validasi request list petani yang tidak valid

  Scenario: Gagal melihat list petani karena tidak ada hak akses
    Given Pengguna dengan peran selain sekretariat, auditor, penanggung jawab, dan penyuluh login untuk melihat akun petani
    When Pengguna ingin melihat list petani
    Then Sistem memberikan pesan error terkait hak akses melihat akun petani

  Scenario: Gagal melihat detail petani karena tidak ada hak akses
    Given Pengguna dengan peran selain sekretariat, auditor, penanggung jawab, dan penyuluh login untuk melihat akun petani
    When Pengguna ingin melihat detail akun petani
    Then Sistem memberikan pesan error terkait hak akses melihat akun petani