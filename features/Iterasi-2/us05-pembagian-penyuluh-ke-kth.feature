Feature: Pembagian Penyuluh ke KTH
  Sebagai sekretariat, saya ingin menugaskan penyuluh ke salah satu KTH, 
  sehingga setiap KTH memiliki penyuluh sebagai pengawas

  Scenario: Sekretariat membuat KTH sekaligus menentukan pengawas
    Given Pengguna dengan peran sekretariat login untuk manajemen KTH
    When Pengguna ingin membuat data KTH
    And Menentukan pengawas yang berada di KPH yang ditentukan
    Then Sistem memberikan pesan sukses KTH
    And KTH sudah memiliki pengawas

  Scenario: Sekretariat menentukan penyuluh sebagai pengawas pada suatu KTH
    Given Pengguna dengan peran sekretariat login untuk manajemen KTH
    When Pengguna ingin memperbarui data KTH
    And Menentukan pengawas yang berada di KPH yang ditentukan
    Then Sistem memberikan pesan sukses KTH
    And KTH sudah memiliki pengawas

  Scenario: Gagal membuat KTH karena Penyuluh tidak ada
    Given Pengguna dengan peran sekretariat login untuk manajemen KTH
    When Pengguna ingin membuat data KTH
    And Pengawas yang ditentukan tidak ditemukan
    Then Sistem memberikan pesan error terkait penyuluh tidak ditemukan

  Scenario: Gagal memperbarui KTH karena Penyuluh tidak ada
    Given Pengguna dengan peran sekretariat login untuk manajemen KTH
    When Pengguna ingin memperbarui data KTH
    And Pengawas yang ditentukan tidak ditemukan
    Then Sistem memberikan pesan error terkait penyuluh tidak ditemukan

  Scenario: Gagal membuat KTH karena Penyuluh dan KPH tidak sesuai
    Given Pengguna dengan peran sekretariat login untuk manajemen KTH
    When Pengguna ingin membuat data KTH
    And Pengawas yang ditentukan tidak berada di KPH yang telah ditentukan
    Then Sistem memberikan pesan error terkait ketidaksesuaian Penyuluh dan KPH

  Scenario: Gagal memperbarui KTH karena Penyuluh dan KPH tidak sesuai
    Given Pengguna dengan peran sekretariat login untuk manajemen KTH
    When Pengguna ingin memperbarui data KTH
    And Pengawas yang ditentukan tidak berada di KPH yang telah ditentukan
    Then Sistem memberikan pesan error terkait ketidaksesuaian Penyuluh dan KPH

  Scenario: Penyuluh membuat KTH
    Given Pengguna dengan peran penyuluh login untuk manajemen KTH
    When Pengguna ingin membuat data KTH
    Then Sistem memberikan pesan sukses KTH
    And KTH diawasi oleh penyuluh yang membuat

  Scenario: Penyuluh memperbarui KTH yang diawasi
    Given Pengguna dengan peran penyuluh login untuk manajemen KTH
    When Pengguna ingin memperbarui data KTH yang diawasi olehnya
    Then Sistem memberikan pesan sukses KTH

  Scenario: Penyuluh melihat list KTH yang diawasi
    Given Pengguna dengan peran penyuluh login untuk manajemen KTH
    When Pengguna ingin melihat list KTH yang diawasi olehnya
    Then Sistem memberikan pesan sukses KTH

  Scenario: Penyuluh gagal memperbarui KTH karena mengubah pengawas dan KPH
    Given Pengguna dengan peran penyuluh login untuk manajemen KTH
    When Pengguna ingin memperbarui data KTH yang diawasi olehnya
    And Membarui penyuluh yang mengawasi dan KPH yang menaungi KTH
    Then Sistem memberikan pesan error terkait data tidak boleh diubah

  Scenario: Penyuluh gagal menghapus KTH yang tidak diawasi olehnya
    Given Pengguna dengan peran penyuluh login untuk manajemen KTH
    When Pengguna ingin menghapus KTH yang tidak diawasi olehnya
    Then Sistem memberikan pesan error terkait hak akses