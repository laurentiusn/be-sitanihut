Feature: Statistik Umur Petani
  Sebagai sekretariat atau auditor, saya ingin melihat data statistik mengenai umur petani,
  sehingga saya mempunyai catatan informasi terkait umur petani yang terdaftar

  Scenario: Berhasil melihat informasi umur petani
    Given Pengguna dengan peran sekretariat login untuk melihat statistik
    When Pengguna ingin melihat data umur pada statistik
    Then Sistem memberikan pesan sukses statistik
    And Terdapat data rentang umur petani

  Scenario: Petani baru ditambahkan dengan nomor induk yang valid
    Given Pengguna dengan peran sekretariat telah melihat rentang umur petani
    When Pengguna membuat akun petani serta nomor induk yang valid
    Then Data rentang umur petani berubah

  Scenario: Petani baru ditambahkan dengan nomor induk yang tidak lengkap
    Given Pengguna dengan peran sekretariat telah melihat rentang umur petani
    When Pengguna membuat akun petani serta nomor induk yang kurang dari 12 digit
    Then Data rentang umur petani tidak berubah

  Scenario: Petani baru ditambahkan serta nomor induk dengan tanggal lahir yang tidak sesuai
    Given Pengguna dengan peran sekretariat telah melihat rentang umur petani
    When Pengguna membuat akun petani serta nomor induk dengan hari dan bulan lahir yang tidak sesuai
    Then Data rentang umur petani tidak berubah