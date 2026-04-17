Feature: Informasi
  Sebagai sekretariat, 
  saya ingin mengelola data informasi sebagai sumber bantuan material dan immaterial untuk petani,
  sehingga informasi dapat dilihat secara publik.

  Scenario: Sekretariat berhasil membuat informasi
    Given Pengguna dengan peran sekretariat login untuk mengelola informasi
    When Pengguna ingin membuat data informasi
    Then Sistem memberikan pesan sukses informasi

  Scenario: Sekretariat memperbarui informasi
    Given Pengguna dengan peran sekretariat login untuk mengelola informasi
    When Pengguna ingin memperbarui beberapa data informasi
    Then Sistem memberikan pesan sukses informasi
    And Data informasi diperbarui

  Scenario: Sekretariat menghapus gambar pada informasi
    Given Pengguna dengan peran sekretariat login untuk mengelola informasi
    When Pengguna ingin menghapus gambar pada informasi
    Then Sistem memberikan pesan sukses informasi
    And Gambar pada data informasi dihapus

  Scenario: Sekretariat berhasil menghapus informasi
    Given Pengguna dengan peran sekretariat login untuk mengelola informasi
    When Pengguna ingin menghapus informasi yang sudah ada
    Then Sistem memberikan pesan sukses informasi

  Scenario: Pengguna yang tidak login melihat List informasi
    Given Pengguna yang tidak login untuk melihat informasi
    When Pengguna ingin melihat list informasi
    Then Sistem memberikan pesan sukses informasi
    And Terdapat list data informasi

  Scenario: Pengguna yang tidak login melihat Detail informasi
    Given Pengguna yang tidak login untuk melihat informasi
    When Pengguna ingin melihat detail informasi
    Then Sistem memberikan pesan sukses informasi
    And Terdapat detail data informasi

  Scenario: Gagal membuat informasi karena data tidak sesuai aturan
    Given Pengguna dengan peran sekretariat login untuk mengelola informasi
    When Pengguna ingin membuat data informasi dengan data yang tidak valid
    Then Sistem memberikan pesan error terkait gagal pembuatan informasi

  Scenario: Gagal memperbarui informasi karena data tidak valid
    Given Pengguna dengan peran sekretariat login untuk mengelola informasi
    When Pengguna ingin memperbarui data informasi dengan data yang tidak valid
    Then Sistem memberikan pesan error terkait gagal pembaruan informasi