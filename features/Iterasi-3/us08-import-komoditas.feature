Feature: Import Data Komoditas
  Sebagai sekretariat, saya ingin mengimpor data dari file Excel yang berisi data Komoditas,
  sehingga saya dapat membuat data Komoditas dalam jumlah yang banyak

  Scenario: Berhasil import file excel untuk membuat banyak Komoditas
    Given Pengguna dengan peran sekretariat login untuk import komoditas
    When Pengguna mengirim file excel data Komoditas
    Then Sistem memberikan pesan sukses import komoditas
    And Data Komoditas sudah berada di database

  Scenario: Berhasil import file csv untuk membuat banyak Komoditas
    Given Pengguna dengan peran sekretariat login untuk import komoditas
    When Pengguna mengirim file csv data Komoditas
    Then Sistem memberikan pesan sukses import komoditas
    And Data Komoditas sudah berada di database

  Scenario: Ekstensi file Komoditas tidak sesuai
    Given Pengguna dengan peran sekretariat login untuk import komoditas
    When Pengguna mengirim file txt data Komoditas
    Then Sistem memberikan pesan error terkait ekstensi file komoditas

  Scenario: Ukuran File Komoditas terlalu besar
    Given Pengguna dengan peran sekretariat login untuk import komoditas
    When Pengguna mengirim file csv data Komoditas yang ukuran lebih besar dari 5MB
    Then Sistem memberikan pesan error terkait ukuran file komoditas

  Scenario: Struktur Header duplikat pada File Komoditas
    Given Pengguna dengan peran sekretariat login untuk import komoditas
    When Pengguna mengirim file csv data Komoditas dengan struktur header yang duplikat
    Then Sistem memberikan pesan error terkait header duplikat file komoditas

  Scenario: Struktur Header tidak lengkap pada File Komoditas
    Given Pengguna dengan peran sekretariat login untuk import komoditas
    When Pengguna mengirim file csv data Komoditas dengan struktur header tidak lengkap
    Then Sistem memberikan pesan error terkait struktur header tidak sesuai file komoditas

  Scenario: Terdapat data Komoditas yang tidak valid
    Given Pengguna dengan peran sekretariat login untuk import komoditas
    When Pengguna mengirim file csv data Komoditas dan terdapat data yang tidak valid
    Then Sistem memberikan pesan error terkait data pada file komoditas yang tidak valid