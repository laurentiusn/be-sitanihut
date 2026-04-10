Feature: Manajemen Data User Penanggung Jawab
  Sebagai sekretariat dan auditor, saya ingin melihat daftar penanggung jawab
  dan data lengkap penanggung jawab, sehingga saya dapat mengetahui data penanggung jawab

  Scenario: Sekretariat berhasil melihat list penanggung jawab
    Given Pengguna dengan peran sekretariat login untuk melihat akun penanggung jawab
    When Pengguna ingin melihat list penanggung jawab
    Then Sistem memberikan pesan sukses list penanggung jawab
    And Terdapat data list penanggung jawab

  Scenario: Auditor berhasil melihat list penanggung jawab dengan filter
    Given Pengguna dengan peran auditor login untuk melihat akun penanggung jawab
    And Pengguna memasukkan kata kunci
    And Pengguna ingin melihat penanggung jawab yang berjenis kelamin pria
    And Pengguna ingin list diurutkan berdasarkan nama penanggung jawab
    And Pengguna ingin list diurutkan secara descending
    When Pengguna ingin melihat list penanggung jawab
    Then Sistem memberikan pesan sukses list penanggung jawab
    And Terdapat data list penanggung jawab

  Scenario: Sekretariat berhasil melihat detail penanggung jawab
    Given Pengguna dengan peran sekretariat login untuk melihat akun penanggung jawab
    When Pengguna ingin melihat detail akun penanggung jawab
    Then Sistem memberikan pesan sukses detail penanggung jawab
    And Terdapat data detail penanggung jawab

  Scenario: Auditor berhasil melihat detail penanggung jawab
    Given Pengguna dengan peran auditor login untuk melihat akun penanggung jawab
    When Pengguna ingin melihat detail akun penanggung jawab
    Then Sistem memberikan pesan sukses detail penanggung jawab
    And Terdapat data detail penanggung jawab

  Scenario: Gagal melihat list penanggung jawab karena filter tidak valid
    Given Pengguna dengan peran auditor login untuk melihat akun penanggung jawab
    And Pengguna memasukkan kata kunci untuk list penanggung jawab yang sangat panjang
    And Pengguna ingin melihat penanggung jawab yang berjenis kelamin selain pria dan wanita
    And Pengguna ingin list penanggung jawab diurutkan berdasarkan nomor induk
    And Pengguna ingin list penanggung jawab diurutkan selain ascending dan descending
    When Pengguna ingin melihat list penanggung jawab
    Then Sistem memberikan pesan error terkait validasi request list penanggung jawab yang tidak valid