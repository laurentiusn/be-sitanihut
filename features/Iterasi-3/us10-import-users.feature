Feature: Import Data User
  Sebagai sekretariat, saya ingin mengimpor data dari file Excel yang berisi data Akun, 
  sehingga saya dapat membuat data Akun dalam jumlah yang banyak

  Scenario: Berhasil import file excel untuk membuat banyak akun
    Given Pengguna dengan peran sekretariat login untuk import akun
    When Pengguna mengirim file excel data Akun
    Then Sistem memberikan pesan sukses import akun
    And Data User sudah berada di database

  Scenario: Berhasil import file csv untuk membuat banyak akun
    Given Pengguna dengan peran sekretariat login untuk import akun
    When Pengguna mengirim file csv data Akun
    Then Sistem memberikan pesan sukses import akun
    And Data User sudah berada di database

  Scenario: Ekstensi File Akun tidak sesuai
    Given Pengguna dengan peran sekretariat login untuk import akun
    When Pengguna mengirim file txt data Akun
    Then Sistem memberikan pesan error terkait ekstensi file akun

  Scenario: Ukuran File Akun terlalu besar
    Given Pengguna dengan peran sekretariat login untuk import akun
    When Pengguna mengirim file csv data Akun yang ukuran lebih besar dari 5MB
    Then Sistem memberikan pesan error terkait ukuran file akun

  Scenario: Struktur Header duplikat pada File Akun
    Given Pengguna dengan peran sekretariat login untuk import akun
    When Pengguna mengirim file csv data Akun dengan struktur header yang duplikat
    Then Sistem memberikan pesan error terkait header duplikat file akun

  Scenario: Struktur Header tidak lengkap pada File Akun
    Given Pengguna dengan peran sekretariat login untuk import akun
    When Pengguna mengirim file csv data Akun dengan struktur header tidak lengkap
    Then Sistem memberikan pesan error terkait struktur header tidak sesuai file akun

  Scenario: Terdapat data Akun yang tidak valid
    Given Pengguna dengan peran sekretariat login untuk import akun
    When Pengguna mengirim file csv data Akun dan terdapat data yang tidak valid
    Then Sistem memberikan pesan error terkait data pada file akun yang tidak valid