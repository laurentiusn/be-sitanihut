Feature: Refresh Token
  Sebagai pengguna, saya ingin tetap masuk (login) ke dalam akun saya
  secara otomatis saat membuka sistem kembali, sehingga saya tidak perlu
  memasukkan kredensial setiap kali ingin masuk (login) ke sistem

  Scenario: Login dengan kredensial yang valid dan ingin tetap masuk
    Given Pengguna yang sudah memiliki akun ingin login ke sistem
    When Pengguna memasukkan kredensial akun nya
    Then Sistem memberikan access token serta refresh token
    And Refresh token disimpan di database

  Scenario: Generate token baru dengan refresh token
    Given Pengguna telah login ke sistem
    And Access token telah expired
    When Pengguna memperbarui access token dengan refresh token yang valid
    Then Sistem memberikan access token serta refresh token
    And Refresh token yang lama dihapus dari database
    And Refresh token yang baru disimpan di database

  Scenario: Logout dan menghapus refresh token
    Given Pengguna telah login ke sistem
    When Pengguna ingin logout dari sistem
    And Menyertakan refresh token yang valid
    Then Sistem menonaktifkan access token
    And Refresh token dihapus dari database

  Scenario: Login dengan platform yang tidak terdaftar
    Given Pengguna yang sudah memiliki akun ingin login ke sistem
    When Pengguna memasukkan kredensial akun nya dengan platform "desktop"
    Then Sistem memberikan access token
    And Refresh token bernilai null

  Scenario: Refresh token yang tidak valid
    Given Pengguna telah login ke sistem
    And Access token telah expired
    When Pengguna memperbarui access token dengan refresh token yang invalid
    Then Sistem menolak request tersebut
    And Sistem memberikan pesan refresh token tidak valid

  Scenario: Refresh token expired
    Given Pengguna telah login ke sistem
    And Access token telah expired
    When Pengguna memperbarui access token dengan refresh token yang expired
    Then Sistem menolak request tersebut
    And Sistem memberikan pesan bahwa refresh token expired

  Scenario: Logout dengan refresh token yang tidak valid
    Given Pengguna telah login ke sistem
    When Pengguna ingin logout dari sistem
    And Menyertakan refresh token yang tidak valid
    Then Sistem menonaktifkan access token