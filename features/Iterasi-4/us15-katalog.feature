Feature: Katalog
  Sebagai sekretariat dan penyuluh, saya ingin mengelola data katalog
  untuk mempromosikan produk dari petani, sehingga data katalog dapat dilihat secara publik.

  Scenario: Sekretariat berhasil membuat katalog
    Given Pengguna dengan peran sekretariat login untuk mengelola katalog
    When Pengguna ingin membuat data katalog
    Then Sistem memberikan pesan sukses katalog

  Scenario: Penyuluh berhasil membuat katalog
    Given Pengguna dengan peran penyuluh login untuk mengelola katalog
    When Pengguna ingin membuat data katalog
    Then Sistem memberikan pesan sukses katalog

  Scenario: Penyuluh memperbarui katalog
    Given Pengguna dengan peran penyuluh login untuk mengelola katalog
    When Pengguna ingin memperbarui beberapa data katalog
    Then Sistem memberikan pesan sukses katalog
    And Data Katalog diperbarui

  Scenario: Sekretariat berhasil menghapus katalog
    Given Pengguna dengan peran sekretariat login untuk mengelola katalog
    When Pengguna ingin menghapus data katalog yang sudah ada
    Then Sistem memberikan pesan sukses katalog

  Scenario: Penyuluh melihat list katalog
    Given Pengguna dengan peran penyuluh login untuk mengelola katalog
    When Pengguna ingin melihat list katalog
    Then Sistem memberikan pesan sukses katalog
    And Terdapat list data katalog milik penyuluh

  Scenario: Pengguna yang tidak login melihat List Katalog
    Given Pengguna yang tidak login untuk melihat katalog
    When Pengguna ingin melihat list katalog
    Then Sistem memberikan pesan sukses katalog
    And Terdapat list data katalog

  Scenario: Pengguna yang tidak login melihat Detail Katalog
    Given Pengguna yang tidak login untuk melihat katalog
    When Pengguna ingin melihat detail katalog
    Then Sistem memberikan pesan sukses katalog
    And Terdapat detail data katalog

  Scenario: Penyuluh gagal melihat detail katalog bukan miliknya
    Given Pengguna dengan peran penyuluh login untuk mengelola katalog
    When Pengguna ingin melihat detail katalog yang bukan miliknya
    Then Sistem memberikan pesan error terkait hak akses katalog

  Scenario: Penyuluh gagal memperbarui katalog yang bukan miliknya
    Given Pengguna dengan peran penyuluh login untuk mengelola katalog
    When Pengguna ingin memperbarui data katalog yang bukan miliknya
    Then Sistem memberikan pesan error terkait hak akses katalog

  Scenario: Penyuluh gagal menghapus katalog yang bukan miliknya
    Given Pengguna dengan peran penyuluh login untuk mengelola katalog
    When Pengguna ingin menghapus data katalog yang bukan miliknya
    Then Sistem memberikan pesan error terkait hak akses katalog

  Scenario: Gagal membuat katalog karena data tidak diisi
    Given Pengguna dengan peran sekretariat login untuk mengelola katalog
    When Pengguna ingin membuat data katalog dengan data yang tidak diisi
    Then Sistem memberikan pesan error terkait gagal pembuatan katalog

  Scenario: Gagal membuat katalog karena data tidak sesuai aturan
    Given Pengguna dengan peran sekretariat login untuk mengelola katalog
    When Pengguna ingin membuat data katalog dengan data yang tidak sesuai aturan
    Then Sistem memberikan pesan error terkait gagal pembuatan katalog

  Scenario: Gagal memperbarui katalog karena data tidak valid
    Given Pengguna dengan peran penyuluh login untuk mengelola katalog
    When Pengguna ingin memperbarui data katalog dengan data yang tidak valid
    Then Sistem memberikan pesan error terkait gagal pembaruan katalog

  Scenario: Gagal mengelola katalog karena tidak memiliki hak akses
    Given Pengguna dengan peran auditor login untuk mengelola katalog
    When Pengguna ingin membuat data katalog
    Then Sistem memberikan pesan error terkait hak akses membuat katalog