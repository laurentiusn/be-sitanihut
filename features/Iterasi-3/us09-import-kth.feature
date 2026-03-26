Feature: Import Data KTH
  Sebagai sekretariat, saya ingin mengimpor data dari file Excel yang berisi data KTH,
  sehingga saya dapat membuat data KTH dalam jumlah yang banyak

  Scenario: Berhasil import file excel untuk membuat banyak KTH
    Given Pengguna dengan peran sekretariat login untuk import kth
    When Pengguna mengirim file excel data KTH
    Then Sistem memberikan pesan sukses import kth
    And Data KTH sudah berada di database

  Scenario: Berhasil import file csv untuk membuat banyak KTH
    Given Pengguna dengan peran sekretariat login untuk import kth
    When Pengguna mengirim file csv data KTH
    Then Sistem memberikan pesan sukses import kth
    And Data KTH sudah berada di database

  Scenario: Ekstensi File KTH tidak sesuai
    Given Pengguna dengan peran sekretariat login untuk import kth
    When Pengguna mengirim file txt data KTH
    Then Sistem memberikan pesan error terkait ekstensi file kth

  Scenario: Ukuran File KTH terlalu besar
    Given Pengguna dengan peran sekretariat login untuk import kth
    When Pengguna mengirim file csv data KTH yang ukuran lebih besar dari 5MB
    Then Sistem memberikan pesan error terkait ukuran file kth

  Scenario: Struktur Header duplikat pada File KTH
    Given Pengguna dengan peran sekretariat login untuk import kth
    When Pengguna mengirim file csv data KTH dengan struktur header yang duplikat
    Then Sistem memberikan pesan error terkait header duplikat file kth

  Scenario: Struktur Header tidak lengkap pada File KTH
    Given Pengguna dengan peran sekretariat login untuk import kth
    When Pengguna mengirim file csv data KTH dengan struktur header tidak lengkap
    Then Sistem memberikan pesan error terkait struktur header tidak sesuai file kth

  Scenario: Terdapat data KTH yang tidak valid
    Given Pengguna dengan peran sekretariat login untuk import kth
    When Pengguna mengirim file csv data KTH dan terdapat data yang tidak valid
    Then Sistem memberikan pesan error terkait data pada file kth yang tidak valid