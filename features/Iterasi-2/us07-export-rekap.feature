Feature: Export Rekapitulasi NTE
  Sebagai sekretariat, saya ingin mengekspor data rekapitulasi 
  nilai transaksi ekonomi menjadi PDF atau CSV, 
  sehingga saya dapat membagikan data rekapitulasi nilai transaksi ekonomi tanpa melalui website

  Scenario: Export data Rekap NTE tiap KPH menjadi file pdf
    Given Pengguna login sebagai sekretariat untuk download rekapitulasi
    When Pengguna ingin download rekapitulasi NTE tiap KPH menjadi file pdf
    Then Sistem memberikan pesan sukses export
    And Terdapat file pdf yang terdownload

  Scenario: Export data Rekap NTE tiap KPH menjadi file csv
    Given Pengguna login sebagai sekretariat untuk download rekapitulasi
    When Pengguna ingin download rekapitulasi NTE tiap KPH menjadi file csv
    Then Sistem memberikan pesan sukses export
    And Terdapat file csv yang terdownload

  Scenario: Export data Rekap NTE tiap KTH menjadi file pdf
    Given Pengguna login sebagai sekretariat untuk download rekapitulasi
    When Pengguna ingin download rekapitulasi NTE tiap KTH menjadi file pdf
    Then Sistem memberikan pesan sukses export
    And Terdapat file pdf yang terdownload

  Scenario: Export data Rekap NTE tiap KTH menjadi file csv
    Given Pengguna login sebagai sekretariat untuk download rekapitulasi
    When Pengguna ingin download rekapitulasi NTE tiap KTH menjadi file csv
    Then Sistem memberikan pesan sukses export
    And Terdapat file csv yang terdownload

  Scenario: Export data Rekap NTE tiap Petani menjadi file pdf
    Given Pengguna login sebagai sekretariat untuk download rekapitulasi
    When Pengguna ingin download rekapitulasi NTE tiap Petani menjadi file pdf
    Then Sistem memberikan pesan sukses export
    And Terdapat file pdf yang terdownload

  Scenario: Export data Rekap NTE tiap Petani menjadi file csv
    Given Pengguna login sebagai sekretariat untuk download rekapitulasi
    When Pengguna ingin download rekapitulasi NTE tiap Petani menjadi file csv
    Then Sistem memberikan pesan sukses export
    And Terdapat file csv yang terdownload

  Scenario: Export data Rekap NTE tiap Komoditas menjadi file pdf
    Given Pengguna login sebagai sekretariat untuk download rekapitulasi
    When Pengguna ingin download rekapitulasi NTE tiap Komoditas menjadi file pdf
    Then Sistem memberikan pesan sukses export
    And Terdapat file pdf yang terdownload

  Scenario: Export data Rekap NTE tiap Komoditas menjadi file csv
    Given Pengguna login sebagai sekretariat untuk download rekapitulasi
    When Pengguna ingin download rekapitulasi NTE tiap Komoditas menjadi file csv
    Then Sistem memberikan pesan sukses export
    And Terdapat file csv yang terdownload

  Scenario: Gagal export karena parameter type tidak valid
    Given Pengguna login sebagai sekretariat untuk download rekapitulasi
    When Pengguna ingin download rekapitulasi NTE dengan type yang tidak valid
    Then Sistem memberikan pesan error terkait parameter type yang tidak valid